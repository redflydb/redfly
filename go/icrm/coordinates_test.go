package icrm

import (
	"testing"
)

func TestOverlaps(t *testing.T) {
	tests := []struct {
		a, b     Coordinates
		expected bool
	}{
		{Coordinates{1, 0, 10, 0}, Coordinates{1, 5, 15, 0}, true},
		{Coordinates{1, 0, 5, 0}, Coordinates{1, 10, 15, 0}, false},
		{Coordinates{1, 0, 5, 10}, Coordinates{1, 10, 15, 10}, true},
		{Coordinates{1, 0, 10, 0}, Coordinates{2, 5, 15, 0}, false},
	}
	for _, test := range tests {
		actual := test.a.Overlaps(test.b)
		if actual != test.expected {
			t.Errorf("Expected %t, got %t", test.expected, actual)
		}
	}
}
func TestEncloses(t *testing.T) {
	tests := []struct {
		a, b     Coordinates
		expected bool
	}{
		{Coordinates{1, 0, 15, 0}, Coordinates{1, 5, 10, 0}, true},
		{Coordinates{1, 5, 10, 0}, Coordinates{1, 0, 15, 0}, false},
		{Coordinates{1, 5, 10, 10}, Coordinates{1, 0, 15, 10}, true},
		{Coordinates{1, 0, 15, 0}, Coordinates{2, 5, 10, 0}, false},
	}
	for _, test := range tests {
		actual := test.a.Encloses(test.b)
		if actual != test.expected {
			t.Errorf("Expected %t, got %t", test.expected, actual)
		}
	}
}
func TestGetOverlap(t *testing.T) {
	tests := []struct {
		a, b, expected Coordinates
	}{
		{Coordinates{1, 0, 10, 0}, Coordinates{1, 5, 15, 0}, Coordinates{1, 5, 10, 0}},
		{Coordinates{1, 0, 5, 0}, Coordinates{1, 10, 15, 0}, Coordinates{}},
		{Coordinates{1, 0, 5, 10}, Coordinates{1, 10, 15, 10}, Coordinates{1, 10, 5, 10}},
		{Coordinates{1, 0, 10, 0}, Coordinates{2, 5, 15, 0}, Coordinates{}},
	}
	for _, test := range tests {
		actual := test.a.GetOverlap(test.b)
		if actual != test.expected {
			t.Errorf("Expected %v, got %v", test.expected, actual)
		}
	}
}
func TestSize(t *testing.T) {
	tests := []struct {
		a        Coordinates
		expected int
	}{
		{Coordinates{1, 0, 10, 0}, 11},
		{Coordinates{1, 0, 5, 0}, 6},
		{Coordinates{1, 10, 15, 10}, 6},
	}
	for _, test := range tests {
		actual := test.a.Size()
		if actual != test.expected {
			t.Errorf("Expected %d, got %d", test.expected, actual)
		}
	}
}
