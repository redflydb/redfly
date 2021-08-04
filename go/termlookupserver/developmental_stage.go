package termlookupserver

import (
	"fmt"

	"redfly.edu/termlookupserver/rdf"
)

// DevelopmentalStageService provides a convenience service for fetching
// developmental stages from the FlyBase fly development ontology. See
// https://github.com/FlyBase/drosophila-anatomy-developmental-ontology.
type DevelopmentalStageService struct {
	store *rdf.Store
}

// DevelopmentalStage represents a term fetched from the ontology.
type DevelopmentalStage struct {
	ID   string
	Term string
}

// NewDevelopmentalStageService initializes a new development stage
// service backed by a RDF store.
func NewDevelopmentalStageService(s *rdf.Store) *DevelopmentalStageService {
	return &DevelopmentalStageService{store: s}
}

// GetAll fetches all the developmental stages terms from the FlyBase
// fly development ontology.
func (s *DevelopmentalStageService) GetAll() []DevelopmentalStage {
	query := `
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT DISTINCT ?fbdv ?term
		WHERE
		{
			?x oboInOwl:id ?fbdv .
			?x rdfs:label ?term .
			FILTER (STRSTARTS(?fbdv, "FBdv:"^^xsd:string)) .
		}
	`
	res := []DevelopmentalStage{}
	for row := range s.store.Query(query) {
		res = append(
			res,
			DevelopmentalStage{
				ID:   row["fbdv"],
				Term: row["term"],
			})
	}

	return res
}

// GetDescendants fetches all the developmental stages from the FlyBase
// fly development ontology that are descendants of the given
// developmental stage ID.
func (s *DevelopmentalStageService) GetDescendants(id string) []DevelopmentalStage {
	query := fmt.Sprintf(`
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT ?fbdv ?term
		WHERE
		{
			?x oboInOwl:id "%s"^^xsd:string .
			?child rdfs:subClassOf ?x .
			?child oboInOwl:id ?fbdv .
			?child rdfs:label ?term .
		}
	`, id)
	res := []DevelopmentalStage{}
	for row := range s.store.Query(query) {
		res = append(
			res,
			DevelopmentalStage{
				ID:   row["fbdv"],
				Term: row["term"],
			})
	}

	return res
}
