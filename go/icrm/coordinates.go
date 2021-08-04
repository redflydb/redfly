package icrm

// Coordinates struct of the coordinates of a sequence on a chromosome. The
// struct contains a margin of error, which is used in the overlap and enclosure
// calculations.
type Coordinates struct {
	ChromosomeID, Start, End, ErrorMargin int
}

// Overlaps determines if this set of coordinates overlaps another set. A set
// of coordinates overlaps another if its starting and ending points 'overlaps'
// with the other set's starting and ending points.
func (c Coordinates) Overlaps(other Coordinates) bool {
	if c.ChromosomeID == other.ChromosomeID {
		start := max(c.Start, other.Start)
		end := min(c.End, other.End)

		return end-start+c.ErrorMargin > 0
	}

	return false
}

// Encloses determines if this set of coordinates encloses another set. A set of
// coordinates encloses another if the other set of coordinates are entirely
// within the starting and ending points of this set.
func (c Coordinates) Encloses(other Coordinates) bool {
	if c.ChromosomeID == other.ChromosomeID {
		start := other.Start + c.ErrorMargin
		end := other.End - c.ErrorMargin

		return c.Start <= start && c.End >= end
	}

	return false
}

// GetOverlap gets the coordinates of the overlap between two coordinates.
func (c Coordinates) GetOverlap(other Coordinates) Coordinates {
	if c.Overlaps(other) {
		start := max(c.Start, other.Start)
		end := min(c.End, other.End)

		return Coordinates{c.ChromosomeID, start, end, c.ErrorMargin}
	}

	return Coordinates{}
}

// Size gets the size of the set of coordinates.
func (c Coordinates) Size() int {
	return c.End - c.Start + 1
}
func max(x, y int) int {
	if x > y {
		return x
	}

	return y
}
func min(x, y int) int {
	if x < y {
		return x
	}

	return y
}
