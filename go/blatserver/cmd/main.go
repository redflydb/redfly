package main

import (
	"fmt"
	"log"
	"net/http"
	"os"

	"redfly.edu/blatserver"
)

func main() {
	aedesAegyptiGenomeFile, _error := os.Open("../assets/aaeg5.2bit")
	if _error != nil {
		panic(_error)
	}
	defer aedesAegyptiGenomeFile.Close()
	anophelesGambiaeGenomeFile, _error := os.Open("../assets/agam4.2bit")
	if _error != nil {
		panic(_error)
	}
	defer anophelesGambiaeGenomeFile.Close()
	drosophilaMelanogasterGenomeFile, _error := os.Open("../assets/dm6.2bit")
	if _error != nil {
		panic(_error)
	}
	defer drosophilaMelanogasterGenomeFile.Close()
	triboliumCastaneumGenomeFile, _error := os.Open("../assets/tcas5.2.2bit")
	if _error != nil {
		panic(_error)
	}
	defer triboliumCastaneumGenomeFile.Close()
	fmt.Println("Starting server; hit CTRL+C to exit.")
	r := blatserver.BuildRoutes(
		aedesAegyptiGenomeFile,
		anophelesGambiaeGenomeFile,
		drosophilaMelanogasterGenomeFile,
		triboliumCastaneumGenomeFile)
	log.Fatal(http.ListenAndServe(":8080", r))
}
