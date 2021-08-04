package obo

import (
	"bufio"
	"encoding/csv"
	"io"
	"os"
	"strings"
)

// Reader represents a OBO file ready for reading.
// See http://purl.obolibrary.org/obo/oboformat/spec.html
// for details on the OBO file format.
type Reader struct {
	reader *csv.Reader
}

// Record represents a record in a OBO file.
type Record struct {
	ID   string
	Term string
}

// NewReader constructs a new reader for a OBO file.
// OBO files are essentially text files, so the OBO reader wraps a csv.Reader struct.
func NewReader(file *os.File) *Reader {
	reader := csv.NewReader(bufio.NewReader(file))
	reader.Comma = ' '
	reader.FieldsPerRecord = -1
	reader.LazyQuotes = true

	return &Reader{reader: reader}
}

// Read parses the OBO file and returns a channel producing the records.
// Returning an channel allows the method to be called both synchronously or asynchronously.
func (reader *Reader) Read() <-chan Record {
	channel := make(chan Record)
	go func() {
		defer close(channel)
		record, _error := reader.reader.Read()
		id := ""
		foundID := false
		for _error != io.EOF {
			if _error != nil {
				panic(_error)
			}
			if 1 < len(record) {
				if record[0] == "id:" {
					// For both Anopheles gambiae and Aedes aegypti species
					if strings.Split(record[1], ":")[0] == "TGMA" {
						id = record[1]
						foundID = true
					}
					// For the Tribolium castaneum species
					if strings.Split(record[1], ":")[0] == "TrOn" {
						id = record[1]
						foundID = true
					}
				} else {
					if record[0] == "name:" {
						term := record[1]
						for index := 2; index < len(record); index++ {
							term = term + " " + record[index]
						}
						if foundID == true {
							channel <- Record{
								ID:   id,
								Term: term,
							}
							foundID = false
						}
					}
				}
			}
			record, _error = reader.reader.Read()
		}
	}()

	return channel
}
