package gff

import (
	"os"
	"reflect"
	"testing"
)

func TestRead(t *testing.T) {
	expected := []Record{
		Record{
			Sequence:   "ctg123",
			Source:     ".",
			Feature:    "exon",
			Start:      "1300",
			End:        "1500",
			Score:      ".",
			Strand:     "+",
			Phase:      ".",
			Attributes: map[string]string{"ID": "exon00001"},
		},
		Record{
			Sequence:   "ctg123",
			Source:     ".",
			Feature:    "exon",
			Start:      "1050",
			End:        "1500",
			Score:      ".",
			Strand:     "+",
			Phase:      ".",
			Attributes: map[string]string{"ID": "exon00002"},
		},
		Record{
			Sequence:   "ctg123",
			Source:     ".",
			Feature:    "exon",
			Start:      "3000",
			End:        "3902",
			Score:      ".",
			Strand:     "+",
			Phase:      ".",
			Attributes: map[string]string{"ID": "exon00003"},
		},
		Record{
			Sequence:   "ctg123",
			Source:     ".",
			Feature:    "exon",
			Start:      "5000",
			End:        "5500",
			Score:      ".",
			Strand:     "+",
			Phase:      ".",
			Attributes: map[string]string{"ID": "exon00004"},
		},
		Record{
			Sequence:   "ctg123",
			Source:     ".",
			Feature:    "exon",
			Start:      "7000",
			End:        "9000",
			Score:      ".",
			Strand:     "+",
			Phase:      ".",
			Attributes: map[string]string{"ID": "exon00005"},
		},
	}
	file, _error := os.Open("./test_data.gff")
	if _error != nil {
		t.Fatalf("Failed to open file: %s", _error.Error())
	}
	defer file.Close()
	gff := NewReader(file)
	for actual := range gff.Read() {
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
