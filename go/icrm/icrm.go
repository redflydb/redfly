package icrm

import (
	"errors"
	"sort"
)

// InferredCRM represents a calculated inferred cis regulatory module (CRM). An
// inferred CRM is defined as the overlap between two or more CRMs and the
// intersection of their expression terms, all which must be unique.
// For example, take those three CRMs that form an inferred CRM:
// |-----|     Expression Terms: (a, b, c)
//     |-----| Expression Terms: (c, d, e)
//  |-----|    Expression Terms: (b, c, d)
// The inferred CRM would then be:
//     |-|     Expression Terms: (c)
type InferredCRM struct {
	Components                   []int
	Expression                   []int
	CurrentGenomeAssemblyVersion string
	Coordinates                  Coordinates
}

// BuildInferredCRM builds an iCRM from one or more CRM overlaps.
func BuildInferredCRM(overlaps []CRMOverlap, minimum int) (InferredCRM, error) {
	components := []int{overlaps[0].ID, overlaps[0].OverlapID}
	expression := overlaps[0].Expression
	currentGenomeAssemblyVersion := overlaps[0].CurrentGenomeAssemblyReleaseVersion
	coordinates := overlaps[0].Coordinates
	for _, overlap := range overlaps[1:] {
		expression = intersect(expression, overlap.Expression)
		coordinates = coordinates.GetOverlap(overlap.Coordinates)
		if coordinates.Size() < minimum || len(expression) < 1 {
			return InferredCRM{}, errors.New("cannot make a valid iCRM from the given overlaps")
		}
		components = merge(components, []int{overlap.ID, overlap.OverlapID})
	}

	return InferredCRM{components, expression, currentGenomeAssemblyVersion, coordinates}, nil
}

// Merge merges this and another inferred CRM into a new inferred CRM
func (i InferredCRM) Merge(other InferredCRM) (InferredCRM, error) {
	if i.Coordinates.Overlaps(other.Coordinates) {
		components := merge(i.Components, other.Components)
		expression := intersect(i.Expression, other.Expression)
		currentGenomeAssemblyVersion := i.CurrentGenomeAssemblyVersion
		coordinates := i.Coordinates.GetOverlap(other.Coordinates)

		return InferredCRM{components, expression, currentGenomeAssemblyVersion, coordinates}, nil
	}

	return InferredCRM{}, errors.New("tried to merge non-overlapping inferred CRMs")
}

// Encompasses determines whether this inferred CRM completely encloses another
// inferred CRM. An inferred CRM completely encloses another if the two inferred
// CRMs share the same exact expression terms, and the coordinates of one
// encloses another.
// See Coordinates.Encloses() For the description of how a set of coordinates
// encloses another.
func (i InferredCRM) Encompasses(other InferredCRM) bool {
	return equal(i.Expression, other.Expression) && i.Coordinates.Encloses(other.Coordinates)
}

// InferredCRMCalculation represents a set of iCRMs. The set ensures that all
// added iCRMs are unique based on their coordinates.
type InferredCRMCalculation struct {
	InferredCRMs map[Coordinates]InferredCRM
	Invalid      map[Coordinates]bool
}

// AddCandidate tries to add an inferred CRM to the set. The inferred CRM must:
// a) Not occupy the same exact sequence as another inferred CRM; in this
//    case, a new inferred CRM will be created with the union of the two
//    inferred CRMs' components and the intersection of the two inferred
//    CRMs' expression terms, and this inferred CRM will replace the
//    existing inferred CRM in this collection, and
// b) Not enclose any other inferred CRMs in the collection; in this case,
//    the inferred CRM will simply be disregarded, and
// c) Not be enclosed by any another inferred CRM in the collection; in this
//    case the enclosing inferred CRM will be removed from the collection
//    and the new inferred CRM will be added.
// Note that it is possible (probable, even) that several inferred CRMs have
// the same 'unique' IDs but actually have different reporter constructs
// and/or expression terms.
// For example, the following two inferred CRMs
// |----|
//     |----|
// and
// |----|
//   |----|
//     |----|
// are calculated as different inferred CRMs, but share the same coordinates
// (and therefore the same uniqud IDs) and are therefore technically the
// same inferred CRM, and so need to be merged.
// If we find two inferred CRMs that share the same coordinates, and upon
// merging them, it was found that the result is an invalid inferred CRM,
// that means there cannot possibly be an inferred CRM with that exact set
// of coordinates, so this unique ID will be stored in a list which
// represents all sets of coordinates that cannot possibly be an inferred
// CRM.
func (c *InferredCRMCalculation) AddCandidate(i InferredCRM) error {
	if c.InferredCRMs == nil || c.Invalid == nil {
		c.InferredCRMs = make(map[Coordinates]InferredCRM)
		c.Invalid = make(map[Coordinates]bool)
	}
	if _, found := c.Invalid[i.Coordinates]; found {
		return errors.New("iCRM rejected; iCRM cannot possibly be valid")
	}
	if v, found := c.InferredCRMs[i.Coordinates]; found {
		merged, err := i.Merge(v)
		if err != nil {
			return err
		}
		if len(merged.Expression) < 1 {
			delete(c.InferredCRMs, i.Coordinates)
			c.Invalid[i.Coordinates] = true

			return errors.New("iCRM rejected; iCRM cannot possibly be valid")
		}
		c.InferredCRMs[i.Coordinates] = merged

		return nil
	}
	for _, v := range c.InferredCRMs {
		if i.Encompasses(v) {
			return errors.New("iCRM rejected; iCRM encloses another")
		}
		if v.Encompasses(i) {
			delete(c.InferredCRMs, v.Coordinates)
			c.InferredCRMs[i.Coordinates] = i

			return nil
		}
	}
	c.InferredCRMs[i.Coordinates] = i

	return nil
}

// CalculateInferredCRMs calculates and returns a set of iCRMs.
// This function checks every possible combindation of CRMs in each group of
// overlapping CRMs (consisting of all overlapping CRMs that share a single
// CRM), and then attempts to build an iCRM from each subset by checking if
// there is any expression terms in the intersection of all overlaps' expression
// terms, and checking whether they all overlap, and finally filters out any
// iCRMs in the resulting list that completely encloses another iCRM by
// determining whether any other iCRMs share the same exact expression terms and
// has coordinates that are enclosed by the iCRM's coordinates.
func CalculateInferredCRMs(overlaps CRMOverlapCalculation, minimum int) (icrms InferredCRMCalculation) {
	for _, sets := range overlaps.PowerSets() {
		for _, set := range sets {
			icrm, err := BuildInferredCRM(set, minimum)
			if err == nil {
				icrms.AddCandidate(icrm)
			}
		}
	}

	return icrms
}

func equal(a, b []int) bool {
	if len(a) != len(b) {
		return false
	}
	for i, v := range a {
		if v != b[i] {
			return false
		}
	}

	return true
}

func intersect(a, b []int) (set []int) {
	hash := make(map[int]bool)
	for _, v := range a {
		hash[v] = true
	}
	for _, v := range b {
		if _, found := hash[v]; found {
			set = append(set, v)
		}
	}
	sort.Ints(set)

	return set
}

func merge(a, b []int) (set []int) {
	hash := make(map[int]bool)
	for _, v := range a {
		hash[v] = true
	}
	for _, v := range b {
		hash[v] = true
	}
	for k := range hash {
		set = append(set, k)
	}
	sort.Ints(set)

	return set
}
