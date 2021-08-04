package main

import (
	"database/sql"
	"fmt"
	"log"
	"net/http"
	"os"

	_ "github.com/lib/pq"
	_ "github.com/mattn/go-sqlite3"

	"redfly.edu/termlookupserver"
	"redfly.edu/termlookupserver/gff"
	"redfly.edu/termlookupserver/obo"
	"redfly.edu/termlookupserver/rdf"
)

func main() {
	database, _error := sql.Open("sqlite3", "file:../assets/cache.db")
	if _error != nil {
		panic(_error)
	}
	defer database.Close()
	if _, _error := os.Stat("../assets/cache.db"); os.IsNotExist(_error) {
		fmt.Println("No cache found. Rebuilding... ")
		buildCache(database)
		fmt.Println("Cache rebuilt.")
	}
	fmt.Println("Starting server; hit CTRL+C to exit.")
	// The anatomical expression ontology
	fbbtFile, _error := os.Open("../assets/fbbt.owl")
	if _error != nil {
		panic(_error)
	}
	defer fbbtFile.Close()
	fbbtRdf := rdf.Open(fbbtFile)
	defer fbbtRdf.Close()
	// The develpmental stage ontology
	fbdvFile, _error := os.Open("../assets/fbdv.owl")
	if _error != nil {
		panic(_error)
	}
	defer fbdvFile.Close()
	fbdvRdf := rdf.Open(fbdvFile)
	defer fbdvRdf.Close()
	// The biological process ontology
	goFile, _error := os.Open("../assets/go.owl")
	if _error != nil {
		panic(_error)
	}
	defer goFile.Close()
	goRdf := rdf.Open(goFile)
	defer goRdf.Close()
	server := termlookupserver.BuildRoutes(
		database,
		termlookupserver.NewAnatomicalExpressionService(fbbtRdf),
		termlookupserver.NewDevelopmentalStageService(fbdvRdf),
		termlookupserver.NewBiologicalProcessService(goRdf),
	)
	log.Fatal(http.ListenAndServe(":8080", server))
}

func buildCache(cache *sql.DB) {
	cb := termlookupserver.NewCacheBuilder(cache)
	fmt.Println("...anatomical expressions")
	cacheAnatomicalExpressions(cb)
	fmt.Println("Done!")
	fmt.Println("...biological processes")
	cacheBiologicalProcesses(cb)
	fmt.Println("Done!")
	fmt.Println("...developmental stages")
	cacheDevelopmentalStages(cb)
	fmt.Println("Done!")
	fmt.Println("...features")
	cacheFeatures(cb)
	fmt.Println("Done!")
	fmt.Println("...genes")
	cacheGenes(cb)
	fmt.Println("Done!")
	fmt.Println("...transgenic constructs")
	cacheTransgenicConstructs(cb)
	fmt.Println("Done!")
}

func cacheAnatomicalExpressions(cb *termlookupserver.CacheBuilder) {
	// The Anopheles gambiae species
	file, _error := os.Open("../assets/tgma.obo")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	cb.CacheAnatomicalExpressionTerms(
		"agam",
		obo.NewReader(file),
		nil,
	)
	// The Aedes aegypti species
	file, _error = os.Open("../assets/tgma.obo")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	cb.CacheAnatomicalExpressionTerms(
		"aaeg",
		obo.NewReader(file),
		nil,
	)
	// The Drosophila melanogaster species
	file, _error = os.Open("../assets/fbbt.owl")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	rdf := rdf.Open(file)
	defer rdf.Close()
	cb.CacheAnatomicalExpressionTerms(
		"dmel",
		nil,
		termlookupserver.NewAnatomicalExpressionService(rdf),
	)
	// The Tribolium castaneum species
	file, _error = os.Open("../assets/tron.obo")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	cb.CacheAnatomicalExpressionTerms(
		"tcas",
		obo.NewReader(file),
		nil,
	)
}

func cacheBiologicalProcesses(cb *termlookupserver.CacheBuilder) {
	file, _error := os.Open("../assets/go.owl")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	service := rdf.Open(file)
	defer service.Close()
	cb.CacheBiologicalProcessTerms(service)
}

func cacheDevelopmentalStages(cb *termlookupserver.CacheBuilder) {
	// The Drosophila melanogaster species
	file, _error := os.Open("../assets/fbdv.owl")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	service := rdf.Open(file)
	defer service.Close()
	cb.CacheDevelopmentalStageTerms(
		"dmel",
		service)
}

func cacheFeatures(cb *termlookupserver.CacheBuilder) {
	// The Aedes aegypt species
	file, _error := os.Open("../assets/aaeg.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader := gff.NewReader(file)
	cb.CacheFeatureTerms(
		"aaeg",
		reader)
	// The Anopheles gambiae species
	file, _error = os.Open("../assets/agam.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader = gff.NewReader(file)
	cb.CacheFeatureTerms(
		"agam",
		reader)
	// The Drosophila melanogaster species
	file, _error = os.Open("../assets/dmel.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader = gff.NewReader(file)
	cb.CacheFeatureTerms(
		"dmel",
		reader)
	// The Tribolium castaneum species
	file, _error = os.Open("../assets/tcas.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader = gff.NewReader(file)
	cb.CacheFeatureTerms(
		"tcas",
		reader)
}

func cacheGenes(cb *termlookupserver.CacheBuilder) {
	// The Aedes aegypti species
	file, _error := os.Open("../assets/aaeg.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader := gff.NewReader(file)
	// The current genome assembly release version for
	// the Aedes aegypti species is "aaeg5"
	cb.CacheGeneTerms(
		"aaeg",
		"aaeg5",
		reader)
	// The Anopheles gambiae species
	file, _error = os.Open("../assets/agam.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader = gff.NewReader(file)
	// The current genome assembly release version for
	// the Anopheles gambiae species is "agam4"
	cb.CacheGeneTerms(
		"agam",
		"agam4",
		reader)
	// The Drosophila melanogaster species
	file, _error = os.Open("../assets/dmel.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader = gff.NewReader(file)
	// The current genome assembly release version for
	// the Drosophila melanogaster species is "dm6"
	cb.CacheGeneTerms(
		"dmel",
		"dm6",
		reader)
	// The Tribolium castaneum species
	file, _error = os.Open("../assets/tcas.gff")
	if _error != nil {
		panic(_error)
	}
	defer file.Close()
	reader = gff.NewReader(file)
	// The current genome assembly release version for
	// the Tribolium castaneum species is "tcas5.2"
	cb.CacheGeneTerms(
		"tcas",
		"tcas5.2",
		reader)
}

func cacheTransgenicConstructs(cb *termlookupserver.CacheBuilder) {
	// The Drosophila melanogaster species
	db, _error := sql.Open("postgres", "postgres://flybase@chado.flybase.org/flybase?sslmode=disable")
	if _error != nil {
		panic(_error)
	}
	defer db.Close()
	cb.CacheTransgenicConstructTerms(db)
}
