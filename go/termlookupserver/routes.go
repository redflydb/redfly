package termlookupserver

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"

	"github.com/gorilla/mux"
)

// BuildRoutes builds the routes for the RESTful API. The function takes the
// cache database as an argument for use in building the responses.
func BuildRoutes(
	cache *sql.DB,
	anatomicalExpressionService *AnatomicalExpressionService,
	developmentalStageService *DevelopmentalStageService,
	biologicalProcessService *BiologicalProcessService,
) *mux.Router {
	router := mux.NewRouter()
	router.NotFoundHandler = http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		fmt.Fprintln(w, "Error 404: Not found.")
	})
	// Anatomical expressions
	router.HandleFunc("/anatomical_expressions", func(w http.ResponseWriter, r *http.Request) {
		rows, _error := cache.Query(`
			SELECT species_short_name,
				identifier,
				term
			FROM anatomical_expression`)
		if _error != nil {
			panic(_error)
		}
		defer rows.Close()
		data := []map[string]string{}
		for rows.Next() {
			var speciesShortName,
				identifier,
				term string
			rows.Scan(
				&speciesShortName,
				&identifier,
				&term,
			)
			data = append(
				data,
				map[string]string{
					"species_short_name": speciesShortName,
					"identifier":         identifier,
					"term":               term})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Descendants of a determined anatomical expression identifier
	router.HandleFunc("/anatomical_expressions/descendants/{id}", func(w http.ResponseWriter, r *http.Request) {
		vars := mux.Vars(r)
		data := []map[string]string{}
		for _, anatomicalExpressionRecord := range anatomicalExpressionService.GetDescendants(vars["id"]) {
			data = append(
				data,
				map[string]string{
					"id":   anatomicalExpressionRecord.ID,
					"term": anatomicalExpressionRecord.Term})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Biological processes
	router.HandleFunc("/biological_processes", func(w http.ResponseWriter, r *http.Request) {
		rows, _error := cache.Query(`
			SELECT identifier,
				term
			FROM biological_process`)
		if _error != nil {
			panic(_error)
		}
		defer rows.Close()
		data := []map[string]string{}
		for rows.Next() {
			var identifier,
				term string
			rows.Scan(
				&identifier,
				&term,
			)
			data = append(
				data,
				map[string]string{
					"identifier": identifier,
					"term":       term})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Descendants of a determined biological process identifier
	router.HandleFunc("/biological_processes/descendants/{id}", func(w http.ResponseWriter, r *http.Request) {
		vars := mux.Vars(r)
		data := []map[string]string{}
		for _, biologicalProcessRecord := range biologicalProcessService.GetDescendants(vars["id"]) {
			data = append(
				data,
				map[string]string{
					"id":   biologicalProcessRecord.ID,
					"term": biologicalProcessRecord.Term})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Developmental stages
	router.HandleFunc("/developmental_stages", func(w http.ResponseWriter, r *http.Request) {
		rows, _error := cache.Query(`
			SELECT species_short_name,
				identifier,
				term
			FROM developmental_stage`)
		if _error != nil {
			panic(_error)
		}
		defer rows.Close()
		data := []map[string]string{}
		for rows.Next() {
			var speciesShortName,
				identifier,
				term string
			rows.Scan(
				&speciesShortName,
				&identifier,
				&term,
			)
			data = append(
				data,
				map[string]string{
					"species_short_name": speciesShortName,
					"identifier":         identifier,
					"term":               term})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Descendants of a determined developmental stage identifier
	router.HandleFunc("/developmental_stages/descendants/{id}", func(w http.ResponseWriter, r *http.Request) {
		vars := mux.Vars(r)
		data := []map[string]string{}
		for _, developmentalStageRecord := range developmentalStageService.GetDescendants(vars["id"]) {
			data = append(
				data,
				map[string]string{
					"id":   developmentalStageRecord.ID,
					"term": developmentalStageRecord.Term})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Features
	// The use of the featureType argument is to avoid generating JSON files too big for
	// the Apache/PHP server giving a memory crash.
	router.HandleFunc("/features/{featureType}", func(w http.ResponseWriter, r *http.Request) {
		vars := mux.Vars(r)
		sqlConsult := `
			SELECT species_short_name,
			    feature_type,
				start,
				end,
				strand,
				identifier,
				name,
				parent
			FROM feature
			WHERE feature_type = '` + vars["featureType"] + `'`
		rows, _error := cache.Query(sqlConsult)
		if _error != nil {
			panic(_error)
		}
		defer rows.Close()
		data := []map[string]string{}
		for rows.Next() {
			// The "type" word is already reserved by GO
			var speciesShortName,
				featureType,
				start,
				end,
				strand,
				identifier,
				name,
				parent string
			rows.Scan(
				&speciesShortName,
				&featureType,
				&start,
				&end,
				&strand,
				&identifier,
				&name,
				&parent,
			)
			data = append(
				data,
				map[string]string{
					"species_short_name": speciesShortName,
					"feature_type":       featureType,
					"start":              start,
					"end":                end,
					"strand":             strand,
					"identifier":         identifier,
					"name":               name,
					"parent":             parent})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Genes
	router.HandleFunc("/genes", func(w http.ResponseWriter, r *http.Request) {
		rows, _error := cache.Query(`
			SELECT species_short_name,
				genome_assembly_release_version,
				identifier,
				term,
				chromosome_name,
				start,
				end,
				strand
			FROM gene`)
		if _error != nil {
			panic(_error)
		}
		defer rows.Close()
		data := []map[string]string{}
		for rows.Next() {
			var speciesShortName,
				genomeAssemblyReleaseVersion,
				identifier,
				term,
				chromosomeName,
				start,
				end,
				strand string
			rows.Scan(
				&speciesShortName,
				&genomeAssemblyReleaseVersion,
				&identifier,
				&term,
				&chromosomeName,
				&start,
				&end,
				&strand,
			)
			data = append(
				data,
				map[string]string{
					"species_short_name":              speciesShortName,
					"genome_assembly_release_version": genomeAssemblyReleaseVersion,
					"identifier":                      identifier,
					"term":                            term,
					"chromosome_name":                 chromosomeName,
					"start":                           start,
					"end":                             end,
					"strand":                          strand})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")
	// Transgenes
	router.HandleFunc("/transgenes", func(w http.ResponseWriter, r *http.Request) {
		rows, _error := cache.Query(`
			SELECT pubmed_id,
				identifier,
				term
			FROM transgene`)
		if _error != nil {
			panic(_error)
		}
		defer rows.Close()
		data := []map[string]string{}
		for rows.Next() {
			var pubmed,
				identifier,
				term string
			rows.Scan(
				&pubmed,
				&identifier,
				&term,
			)
			data = append(
				data,
				map[string]string{
					"pubmed":     pubmed,
					"identifier": identifier,
					"term":       term})
		}
		json, _error := json.Marshal(data)
		if _error != nil {
			panic(_error)
		}
		w.Header().Set("Content-Type", "application/json")
		if _, _error := w.Write(json); _error != nil {
			panic(_error)
		}
	}).Methods("GET")

	return router
}
