package rdf

import (
	"os"
	"testing"
)

func TestQuery(t *testing.T) {
	file, _error := os.Open("./test_data.rdf")
	if _error != nil {
		t.Fatalf("Failed to open file: %s", _error.Error())
	}
	defer file.Close()
	s := Open(file)
	defer s.Close()
	query := `
		PREFIX dc: <http://purl.org/dc/elements/1.1/>
		SELECT ?x ?title
		WHERE {
			?x dc:title ?title
		}
	`
	count := 0
	for row := range s.Query(query) {
		count++
		if count == 1 {
			expected := "Dave Beckett's Home Page"
			actual := row["title"]
			if actual != expected {
				t.Errorf("Expected %v, got %v", expected, actual)
			}
		}
	}
	if count > 1 {
		t.Errorf("Expected 1 result, got %d", count)
	}
}
