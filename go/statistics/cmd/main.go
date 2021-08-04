package main

import (
	"bufio"
	"database/sql"
	"flag"
	"fmt"
	"os"
	"strconv"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"

	"redfly.edu/statistics"
)

func main() {
	username, password, host, port, name := args()
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s", username, password, host, port, name)
	db, err := sql.Open("mysql", dsn)
	if err != nil {
		panic(err)
	}
	sql := `SELECT species_id,
				scientific_name,
				short_name
			FROM Species
			ORDER BY scientific_name;`
	species, err := db.Query(sql)
	if err != nil {
		panic(err)
	}
	var speciesIds []int
	var speciesScientificNames,
		speciesShortNames []string
	speciesIds = append(speciesIds, 0)
	speciesScientificNames = append(speciesScientificNames, "All Species")
	speciesShortNames = append(speciesShortNames, "all")
	for species.Next() {
		var speciesID int
		var speciesScientificName,
			speciesShortName string
		species.Scan(
			&speciesID,
			&speciesScientificName,
			&speciesShortName,
		)
		speciesIds = append(speciesIds, speciesID)
		speciesScientificNames = append(speciesScientificNames, speciesScientificName)
		speciesShortNames = append(speciesShortNames, speciesShortName)
	}
	for index, speciesID := range speciesIds {
		speciesScientificName := speciesScientificNames[index]
		speciesShortName := speciesShortNames[index]
		statisticsFilePath := "docs/statistics_" + speciesShortName + ".txt"
		databaseLastUpdatedFromReport := ""
		var renamedStatisticsFilePath strings.Builder
		renamedStatisticsFilePath.WriteString("docs/statistics_" + speciesShortName + "_")
		// It formats the current datetime as yyyyMMdd
		// according to an "esoteric" Golang format rule
		renamedStatisticsFilePath.WriteString(time.Now().Format("20060102"))
		renamedStatisticsFilePath.WriteString(".txt")
		if _, err := os.Stat(statisticsFilePath); err == nil {
			file, err := os.Open(statisticsFilePath)
			if err != nil {
				panic(err)
			}
			scanner := bufio.NewScanner(file)
			scanner.Split(bufio.ScanLines)
			for scanner.Scan() {
				newLine := scanner.Text()
				if strings.Contains(newLine, "Database last updated") == true {
					lineParts := strings.Fields(newLine)
					databaseLastUpdatedFromReport = lineParts[len(lineParts)-1]
					break
				}
			}
			file.Close()
			if databaseLastUpdatedFromReport == "" {
				panic("No 'Database last updated' in the current " + speciesScientificName + " report")
			}
			os.Rename(statisticsFilePath, renamedStatisticsFilePath.String())
			if err != nil {
				panic(err)
			}
		} else {
			panic(err)
		}
		stats := fillDBStatistics(
			db,
			strconv.Itoa(speciesID),
			speciesScientificName,
			databaseLastUpdatedFromReport)
		newFile, err := os.OpenFile(statisticsFilePath, os.O_CREATE|os.O_RDWR, 0664)
		if err != nil {
			panic(err)
		}
		writer := bufio.NewWriter(newFile)
		_, _ = writer.WriteString(stats.String())
		writer.Flush()
		newFile.Close()
		fmt.Print(stats)
	}
}
func args() (string, string, string, string, string) {
	username := flag.String(
		"username",
		"",
		"User name to use when connecting to the REDfly database")
	password := flag.String(
		"password",
		"",
		"Password to use when connecting to the REDfly database")
	host := flag.String(
		"host",
		"localhost",
		"Connect to the REDfly database on the given host")
	port := flag.String(
		"port",
		"3306",
		"TCP/IP port number for connecting to the REDfly database")
	name := flag.String(
		"name",
		"redfly",
		"The name of the database to use")
	flag.Parse()

	return *username, *password, *host, *port, *name
}

// Note: all the custom SQL functions here are used commonly by both Go source code
// here to build up its release statistics report and PHP source code to show such a
// general information in the main web page.
func fillDBStatistics(
	db *sql.DB,
	speciesID string,
	speciesScientificName string,
	databaseLastUpdatedFromReport string) statistics.DatabaseStatistics {
	var publicationsCuratedNumber int
	var sql, latestUpdateDate string
	if speciesID == "0" {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM (SELECT last_update
				     FROM BindingSite
				     WHERE state = 'current'
				     UNION
				     SELECT last_update
				     FROM CRMSegment
				     WHERE state = 'current' 
				     UNION
				     SELECT last_update
				     FROM PredictedCRM
				     WHERE state = 'current'
				     UNION
				     SELECT last_update
				     FROM ReporterConstruct
					 WHERE state = 'current') AS u;`
	} else {
		sql = `SELECT IFNULL(DATE(MAX(last_update)), CURDATE())
		       FROM (SELECT last_update
				     FROM BindingSite
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					 	 state = 'current'
				     UNION
				     SELECT last_update
				     FROM CRMSegment
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					     state = 'current'
				     UNION
				     SELECT last_update
				     FROM PredictedCRM
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					     state = 'current'
				     UNION
				     SELECT last_update
				     FROM ReporterConstruct
					 WHERE sequence_from_species_id = ` + speciesID + ` AND
					     state = 'current') AS u;`
	}
	if err := db.QueryRow(sql).Scan(&latestUpdateDate); err != nil {
		panic(err)
	}
	sql = `SELECT NumberOfCuratedPublications(` + speciesID + `);`
	if err := db.QueryRow(sql).Scan(&publicationsCuratedNumber); err != nil {
		panic(err)
	}
	if latestUpdateDate < databaseLastUpdatedFromReport {
		var errorMessage strings.Builder
		errorMessage.WriteString("Incompatiblity between the database and report dates: ")
		errorMessage.WriteString(latestUpdateDate)
		errorMessage.WriteString(" and ")
		errorMessage.WriteString(databaseLastUpdatedFromReport)
		panic(errorMessage.String())
	}

	return statistics.DatabaseStatistics{
		LastUpdated:           latestUpdateDate,
		SpeciesScientificName: speciesScientificName,
		RCs:                   fillRCStatistics(db, speciesID, databaseLastUpdatedFromReport),
		CRMs:                  fillCRMStatistics(db, speciesID, databaseLastUpdatedFromReport),
		NonCRMs:               fillNonCRMStatistics(db, speciesID, databaseLastUpdatedFromReport),
		TFBSs:                 fillTFBSStatistics(db, speciesID, databaseLastUpdatedFromReport),
		PredictedCRMs:         fillPredictedCRMStatistics(db, speciesID, databaseLastUpdatedFromReport),
		CRMSegments:           fillCRMSegmentStatistics(db, speciesID, databaseLastUpdatedFromReport),
		PublicationsCurated:   publicationsCuratedNumber}
}
