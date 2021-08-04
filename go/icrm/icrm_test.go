package icrm

import (
	"reflect"
	"testing"
)

func TestMerge(t *testing.T) {
	tests := []struct {
		a, b, expected InferredCRM
	}{{
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		InferredCRM{[]int{3, 4, 5}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		InferredCRM{[]int{1, 2, 3, 4, 5}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		InferredCRM{[]int{1, 2, 3}, []int{2, 3, 4}, "dm6", Coordinates{1, 0, 5, 0}},
		InferredCRM{[]int{1, 2, 3}, []int{2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 10, 0}},
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 15, 0}},
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
	}, {
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 10, 15, 0}},
		InferredCRM{},
	}}
	for _, test := range tests {
		actual, _ := test.a.Merge(test.b)
		if !reflect.DeepEqual(actual, test.expected) {
			t.Errorf("Expected %v, got %v", test.expected, actual)
		}
	}
}

func TestEncompasses(t *testing.T) {
	tests := []struct {
		a, b     InferredCRM
		expected bool
	}{{
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 15, 0}},
		InferredCRM{[]int{2, 3, 4}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
		true,
	}, {
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
		InferredCRM{[]int{1, 2, 3}, []int{2, 3, 4}, "dm6", Coordinates{1, 0, 15, 0}},
		false,
	}, {
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 10}},
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 15, 10}},
		true,
	}, {
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 15, 0}},
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{2, 5, 10, 0}},
		false,
	}}
	for _, test := range tests {
		actual := test.a.Encompasses(test.b)
		if actual != test.expected {
			t.Errorf("Expected %t, got %t", test.expected, actual)
		}
	}
}

func TestAddCandidate(t *testing.T) {
	tests := []struct {
		a        InferredCRMCalculation
		b        InferredCRM
		expected InferredCRMCalculation
	}{{
		InferredCRMCalculation{},
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
		InferredCRMCalculation{
			map[Coordinates]InferredCRM{
				Coordinates{1, 5, 10, 0}: InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
			},
			make(map[Coordinates]bool),
		},
	}, {
		InferredCRMCalculation{
			map[Coordinates]InferredCRM{
				Coordinates{1, 5, 10, 0}: InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
			},
			make(map[Coordinates]bool),
		},
		InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 15, 0}},
		InferredCRMCalculation{
			map[Coordinates]InferredCRM{
				Coordinates{1, 5, 10, 0}: InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
			},
			make(map[Coordinates]bool),
		},
	}, {
		InferredCRMCalculation{
			map[Coordinates]InferredCRM{
				Coordinates{1, 5, 10, 0}: InferredCRM{[]int{1, 2, 3}, []int{1, 2, 3}, "dm6", Coordinates{1, 5, 10, 0}},
			},
			make(map[Coordinates]bool),
		},
		InferredCRM{[]int{1, 2, 3}, []int{3, 4, 5}, "dm6", Coordinates{1, 5, 10, 0}},
		InferredCRMCalculation{
			map[Coordinates]InferredCRM{
				Coordinates{1, 5, 10, 0}: InferredCRM{[]int{1, 2, 3}, []int{3}, "dm6", Coordinates{1, 5, 10, 0}},
			},
			make(map[Coordinates]bool),
		},
	}, {
		InferredCRMCalculation{
			map[Coordinates]InferredCRM{
				Coordinates{1, 5, 10, 0}: InferredCRM{[]int{1, 2, 3}, []int{3}, "dm6", Coordinates{1, 5, 10, 0}},
			},
			make(map[Coordinates]bool),
		},
		InferredCRM{[]int{1, 2, 3}, []int{4, 5, 6}, "dm6", Coordinates{1, 5, 10, 0}},
		InferredCRMCalculation{
			make(map[Coordinates]InferredCRM),
			map[Coordinates]bool{
				Coordinates{1, 5, 10, 0}: true,
			},
		},
	}, {
		InferredCRMCalculation{
			make(map[Coordinates]InferredCRM),
			map[Coordinates]bool{
				Coordinates{1, 5, 10, 0}: true,
			},
		},
		InferredCRM{[]int{1, 2, 3}, []int{4, 5, 6}, "dm6", Coordinates{1, 5, 10, 0}},
		InferredCRMCalculation{
			make(map[Coordinates]InferredCRM),
			map[Coordinates]bool{
				Coordinates{1, 5, 10, 0}: true,
			},
		},
	}, {
		InferredCRMCalculation{
			make(map[Coordinates]InferredCRM),
			map[Coordinates]bool{
				Coordinates{1, 5, 10, 0}: true,
			},
		},
		InferredCRM{[]int{1, 2, 3}, []int{3, 4, 5}, "dm6", Coordinates{1, 6, 9, 0}},
		InferredCRMCalculation{
			map[Coordinates]InferredCRM{
				Coordinates{1, 6, 9, 0}: InferredCRM{[]int{1, 2, 3}, []int{3, 4, 5}, "dm6", Coordinates{1, 6, 9, 0}},
			},
			map[Coordinates]bool{
				Coordinates{1, 5, 10, 0}: true,
			},
		},
	}}
	for _, test := range tests {
		test.a.AddCandidate(test.b)
		if !reflect.DeepEqual(test.a, test.expected) {
			t.Errorf("Expected %v, got %v", test.expected, test.a)
		}
	}
}
