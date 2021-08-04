package termlookupserver

import (
	"fmt"

	"redfly.edu/termlookupserver/rdf"
)

// AnatomicalExpressionService provides a convenience service for fetching
// anatomical expressions from the FlyBase fly anatomy ontology. See
// https://github.com/FlyBase/drosophila-anatomy-developmental-ontology.
type AnatomicalExpressionService struct {
	store *rdf.Store
}

// AnatomicalExpression represents a term fetched from the ontology.
type AnatomicalExpression struct {
	ID   string
	Term string
}

// NewAnatomicalExpressionService initializes a new anatomical expression
// service backed by a RDF store.
func NewAnatomicalExpressionService(s *rdf.Store) *AnatomicalExpressionService {
	return &AnatomicalExpressionService{store: s}
}

// GetAll fetches all the anatomical expression terms from the FlyBase
// fly anatomy ontology.
func (s *AnatomicalExpressionService) GetAll() []AnatomicalExpression {
	query := `
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT DISTINCT ?fbbt ?term
		WHERE
		{
			?x oboInOwl:id ?fbbt .
			?x rdfs:label ?term .
			FILTER (STRSTARTS(?fbbt, "FBbt:"^^xsd:string)) .
		}
	`
	res := []AnatomicalExpression{}
	for row := range s.store.Query(query) {
		res = append(
			res,
			AnatomicalExpression{
				ID:   row["fbbt"],
				Term: row["term"],
			})
	}

	return res
}

// GetDescendants fetches all the anatomical expression terms from the
// FlyBase fly anatomy ontology that are descendants of the given
// anatomical expression ID.
func (s *AnatomicalExpressionService) GetDescendants(id string) []AnatomicalExpression {
	query := fmt.Sprintf(`
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT ?fbbt ?term
		WHERE
		{
			?x oboInOwl:id "%s"^^xsd:string .
			?child rdfs:subClassOf ?x .
			?child oboInOwl:id ?fbbt .
			?child rdfs:label ?term .
		}
	`, id)
	res := []AnatomicalExpression{}
	for row := range s.store.Query(query) {
		res = append(
			res,
			AnatomicalExpression{
				ID:   row["fbbt"],
				Term: row["term"],
			})
	}

	return res
}
