package icrm

import (
	"reflect"
	"testing"
)

func TestInsert(t *testing.T) {
	tests := []struct {
		a        CRMOverlapSet
		b        CRMOverlap
		expected CRMOverlapSet
	}{{
		CRMOverlapSet{1, []CRMOverlap{}},
		CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlapSet{1, []CRMOverlap{CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}}}},
	}, {
		CRMOverlapSet{1, []CRMOverlap{CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}}}},
		CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlapSet{1, []CRMOverlap{CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}}}},
	}}
	for _, test := range tests {
		test.a.Insert(test.b)
		if !reflect.DeepEqual(test.a, test.expected) {
			t.Errorf("Expected %v, got %v", test.expected, test.a)
		}
	}
}
func TestPowerSet(t *testing.T) {
	test := CRMOverlapSet{1, []CRMOverlap{
		CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}}
	expected := [][]CRMOverlap{{
		CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}, {
		CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
	}}
	actual := test.PowerSet()
	if !reflect.DeepEqual(actual, expected) {
		t.Errorf("Expected %v, got %v", expected, actual)
	}
}
func TestAddOverlap(t *testing.T) {
	tests := []struct {
		a        CRMOverlapCalculation
		b        CRMOverlap
		expected CRMOverlapCalculation
	}{{
		CRMOverlapCalculation{},
		CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlapCalculation{
			1: CRMOverlapSet{1, []CRMOverlap{
				CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
		},
	}, {
		CRMOverlapCalculation{
			1: CRMOverlapSet{1, []CRMOverlap{
				CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
		},
		CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlapCalculation{
			1: CRMOverlapSet{1, []CRMOverlap{
				CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
				CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
		},
	}, {
		CRMOverlapCalculation{
			1: CRMOverlapSet{1, []CRMOverlap{
				CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
				CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
		},
		CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlapCalculation{
			1: CRMOverlapSet{1, []CRMOverlap{
				CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
				CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
			2: CRMOverlapSet{2, []CRMOverlap{
				CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
		},
	}, {
		CRMOverlapCalculation{
			1: CRMOverlapSet{1, []CRMOverlap{
				CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
				CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
			2: CRMOverlapSet{2, []CRMOverlap{
				CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
		},
		CRMOverlap{2, 4, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		CRMOverlapCalculation{
			1: CRMOverlapSet{1, []CRMOverlap{
				CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
				CRMOverlap{1, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
			2: CRMOverlapSet{2, []CRMOverlap{
				CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
				CRMOverlap{2, 4, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			}},
		},
	}}
	for _, test := range tests {
		test.a.AddOverlap(test.b)
		if !reflect.DeepEqual(test.a, test.expected) {
			t.Errorf("Expected %v, got %v", test.expected, test.a)
		}
	}
}
func TestPowerSets(t *testing.T) {
	test := CRMOverlapCalculation{
		1: CRMOverlapSet{1, []CRMOverlap{
			CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		}},
		2: CRMOverlapSet{2, []CRMOverlap{
			CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			CRMOverlap{2, 4, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		}},
	}
	expected := [][][]CRMOverlap{{
		{
			CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		}, {
			CRMOverlap{1, 1, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		}, {
			CRMOverlap{1, 2, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		},
	}, {
		{
			CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		}, {
			CRMOverlap{2, 3, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
			CRMOverlap{2, 4, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		}, {
			CRMOverlap{2, 4, []int{1, 2, 3}, "dm6", Coordinates{1, 0, 5, 0}},
		},
	}}
	actual := test.PowerSets()
	if !reflect.DeepEqual(actual, expected) {
		t.Errorf("Expected %v, got %v", expected, actual)
	}
}
