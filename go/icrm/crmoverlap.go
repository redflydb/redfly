package icrm

import (
	"database/sql"
	"errors"
	"strconv"
	"strings"
)

// CRMOverlap represents a cis regulatory module (CRM) overlap. An overlap is
// when the coordinates of a CRM overlaps another. For example, the following is
// an representation of the coordinates of three imaginary CRMs along a
// chromosome:
// |-----|
//     |----|
//        |-----|
// The first and second CRMs and the second and third CRMs overlap; but the
// first and third CRMs do not.
// Take the first overlap above for example, this class represents the two CRMs
// along with the coordinates of the overlap and the expression terms that fall
// within the intersection of the two CRMs' expression terms.
type CRMOverlap struct {
	ID, OverlapID                       int
	Expression                          []int
	CurrentGenomeAssemblyReleaseVersion string
	Coordinates                         Coordinates
}

// CRMOverlapSet represents a set of overlapping CRMs that overlap a single CRM.
type CRMOverlapSet struct {
	ID       int
	Overlaps []CRMOverlap
}

// Insert inserts an CRM overlap into the set. This method ensures that the
// overlap shares the same CRM as the rest of the overlaps in this set and that
// the expression terms are grouped by their corresponding overlap.
func (s *CRMOverlapSet) Insert(overlap CRMOverlap) error {
	if s.ID == overlap.ID {
		s.Overlaps = append(s.Overlaps, overlap)

		return nil
	}

	return errors.New("Cannot insert overlap; the IDs do not match")
}

// PowerSet generates the power set of a CRM overlap group. A formally, a power
// set consists of every possible subsets of a set, including the empty set. For
// example, the power set of {1, 2, 3} would be {{1}, {2}, {3}, {1, 2}, {1, 3},
// {2, 3}, {1, 2, 3}} (the empty set is ignored as it serves no purpose in the
// context of CRM overlaps).
func (s *CRMOverlapSet) PowerSet() (sets [][]CRMOverlap) {
	for _, overlap := range s.Overlaps {
		for _, set := range sets {
			set = append(set, overlap)
			sets = append(sets, set)
		}
		sets = append(sets, []CRMOverlap{overlap})
	}

	return sets
}

// CRMOverlapCalculation represents a collection for the results of an CRM
// overlap calculation.
type CRMOverlapCalculation map[int]CRMOverlapSet

// AddOverlap adds an CRM overlap to the set. The collection automatically
// groups each overlap by CRM.
func (c *CRMOverlapCalculation) AddOverlap(overlap CRMOverlap) error {
	if v, ok := (*c)[overlap.ID]; ok {
		err := v.Insert(overlap)
		(*c)[overlap.ID] = v

		return err
	}
	(*c)[overlap.ID] = CRMOverlapSet{overlap.ID, []CRMOverlap{overlap}}

	return nil
}

// PowerSets generates the power set of every CRM overlap set in the collection
// and puts it into a channel for consumption.
func (c *CRMOverlapCalculation) PowerSets() (sets [][][]CRMOverlap) {
	for _, set := range *c {
		sets = append(sets, set.PowerSet())
	}

	return sets
}

// CalculateOverlaps calculates and returns all overlapping CRMs.
// The error margin is used to compensate for errors in the data and prevents
// the filtering out of any overlaps that fall within the error margin. The
// minimum size value is used for filtering out all overlaps that are smaller
// than that value
func CalculateOverlaps(margin, minimum int, db *sql.DB) (CRMOverlapCalculation, error) {
	rows, err := db.Query(
		`SELECT rc_id,
			overlap_id,
			sequence_from_species_id,
			current_genome_assembly_release_version,
			chromosome_id,
			start,
			end,
			assayed_in_species_id,
			terms
		FROM v_cis_regulatory_module_overlaps
		WHERE end - start + 1 + ? >= ?`,
		margin,
		minimum)
	if err != nil {
		panic(err)
	}
	overlaps := CRMOverlapCalculation{}
	if err != nil {
		return overlaps, err
	}
	for rows.Next() {
		var rcID,
			overlapID,
			sequenceFromSpeciesID,
			chromosomeID,
			start,
			end,
			assayedInSpeciesID int
		var currentGenomeAssemblyReleaseVersion,
			terms string
		if err := rows.Scan(
			&rcID,
			&overlapID,
			&sequenceFromSpeciesID,
			&currentGenomeAssemblyReleaseVersion,
			&chromosomeID,
			&start,
			&end,
			&assayedInSpeciesID,
			&terms); err != nil {
			return overlaps, err
		}
		expression := []int{}
		for _, v := range strings.Split(terms, ",") {
			term, err := strconv.Atoi(v)
			if err != nil {
				return overlaps, err
			}
			expression = append(expression, term)
		}
		overlaps.AddOverlap(CRMOverlap{
			rcID,
			overlapID,
			expression,
			currentGenomeAssemblyReleaseVersion,
			Coordinates{chromosomeID, start, end, margin}})
	}

	return overlaps, nil
}
