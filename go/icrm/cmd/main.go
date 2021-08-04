package main

import (
	"database/sql"
	"flag"
	"fmt"
	"strconv"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"

	"redfly.edu/icrm"
)

func main() {
	username, password, host, port, name, margin, minimum := args()
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s", username, password, host, port, name)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		panic(err)
	}
	startTime := time.Now()
	fmt.Println("Start time: ", startTime)
	fmt.Println("Calculating overlaps...")
	crms, err := icrm.CalculateOverlaps(margin, minimum, db)
	if err != nil {
		panic(err)
	}
	fmt.Println("Done!")
	fmt.Println("Calculating inferred CRMs...")
	icrms := icrm.CalculateInferredCRMs(crms, minimum)
	fmt.Println("Done!")
	fmt.Println("Writing all the new data in the database...")
	write(icrms, db)
	fmt.Println("Done!")
	endTime := time.Now()
	fmt.Println("End time: ", endTime)
	executionTimeSpent := endTime.Sub(startTime)
	fmt.Println("Execution time spent: ", executionTimeSpent.Minutes(), " minutes")
}
func write(icrms icrm.InferredCRMCalculation, db *sql.DB) {
	tx, err := db.Begin()
	if err != nil {
		panic(err)
	}
	defer tx.Rollback()
	if _, err := tx.Exec("DELETE FROM icrm_has_rc"); err != nil {
		panic(err)
	}
	if _, err := tx.Exec("DELETE FROM icrm_has_expr_term"); err != nil {
		panic(err)
	}
	if _, err := tx.Exec("DELETE FROM inferred_crm"); err != nil {
		panic(err)
	}
	stmt, err := tx.Prepare("CALL insert_inferred_crm(?, ?, ?, ?, ?, ?, ?, ?, ?)")
	if err != nil {
		panic(err)
	}
	defer stmt.Close()
	for _, icrm := range icrms.InferredCRMs {
		_, err = stmt.Exec(
			1,
			1,
			icrm.CurrentGenomeAssemblyVersion,
			icrm.Coordinates.ChromosomeID,
			icrm.Coordinates.Start,
			icrm.Coordinates.End,
			icrm.Coordinates.End-icrm.Coordinates.Start+1,
			join(icrm.Expression),
			join(icrm.Components))
		if err != nil {
			panic(err)
		}
	}
	_, err = tx.Exec("CALL refresh_inferred_crm_read_model()")
	if err != nil {
		panic(err)
	}
	tx.Commit()
}
func join(is []int) string {
	ss := []string{}
	for _, i := range is {
		ss = append(ss, strconv.Itoa(i))
	}

	return strings.Join(ss, ",")
}
func args() (string, string, string, string, string, int, int) {
	username := flag.String("username", "", "User name to use when connecting to the REDfly database")
	password := flag.String("password", "", "Password to use when connecting to the REDfly database")
	host := flag.String("host", "localhost", "Connect to the REDfly database on the given host")
	port := flag.String("port", "3306", "TCP/IP port number for connecting to the REDfly database")
	name := flag.String("name", "redfly", "The name of the database to use")
	margin := flag.Int("margin", 5, "The margin of error when calculating iCRMs")
	minimum := flag.Int("minimum", 20, "The minimum size of a possible iCRM")
	flag.Parse()

	return *username, *password, *host, *port, *name, *margin, *minimum
}
