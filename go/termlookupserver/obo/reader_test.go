package obo

import (
	"os"
	"reflect"
	"testing"
)

func TestRead(t *testing.T) {
	expected := []Record{
		Record{
			ID:   "TGMA:0000000",
			Term: "hemolymph",
		},
		Record{
			ID:   "TGMA:0000001",
			Term: "hemocyte",
		},
		Record{
			ID:   "TGMA:0000002",
			Term: "adult head",
		},
		Record{
			ID:   "TGMA:0000003",
			Term: "adult cranium",
		},
		Record{
			ID:   "TGMA:0000004",
			Term: "adult gnathal appendages",
		},
	}
	file, _error := os.Open("./test_data.obo")
	if _error != nil {
		t.Fatalf("Failed to open file: %s", _error.Error())
	}
	defer file.Close()
	obo := NewReader(file)
	for actual := range obo.Read() {
		if !reflect.DeepEqual(actual, expected[0]) {
			t.Errorf("Expected %v, got %v", expected, actual)
		}
		if len(expected) > 0 {
			expected = expected[1:]
		}
	}
	if len(expected) != 0 {
		t.Errorf("Expected 5 results, got %d", 5-len(expected))
	}
}
