package termlookupserver

import (
	"fmt"

	"redfly.edu/termlookupserver/rdf"
)

// BiologicalProcessService provides a convenience service for fetching
// biological processes from the GO ontology.
type BiologicalProcessService struct {
	store *rdf.Store
}

// BiologicalProcess represents a term fetched from the ontology.
type BiologicalProcess struct {
	ID   string
	Term string
}

// NewBiologicalProcessService initializes a new biological process
// service backed by a RDF store.
func NewBiologicalProcessService(s *rdf.Store) *BiologicalProcessService {
	return &BiologicalProcessService{store: s}
}

// GetAll fetches all the biological process terms from the GO ontology.
func (s *BiologicalProcessService) GetAll() []BiologicalProcess {
	query := `
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT DISTINCT ?go ?term
		WHERE
		{
			?x oboInOwl:id ?go .
			?x rdfs:label ?term .
			FILTER (STRSTARTS(?go, "GO:"^^xsd:string)) .
		}
	`
	res := []BiologicalProcess{}
	for row := range s.store.Query(query) {
		res = append(
			res,
			BiologicalProcess{
				ID:   row["go"],
				Term: row["term"],
			})
	}

	return res
}

// GetDescendants fetches all the biological processes from the GO
// ontology that are descendants of the given biological process ID.
func (s *BiologicalProcessService) GetDescendants(id string) []BiologicalProcess {
	query := fmt.Sprintf(`
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT ?go ?term
		WHERE
		{
			?x oboInOwl:id "%s"^^xsd:string .
			?child rdfs:subClassOf ?x .
			?child oboInOwl:id ?go .
			?child rdfs:label ?term .
		}
	`, id)
	res := []BiologicalProcess{}
	for row := range s.store.Query(query) {
		res = append(
			res,
			BiologicalProcess{
				ID:   row["go"],
				Term: row["term"],
			})
	}

	return res
}
