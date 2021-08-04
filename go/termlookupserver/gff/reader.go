package gff

import (
	"bufio"
	"encoding/csv"
	"io"
	"os"
	"strings"
)

// Reader represents a GFF file ready for reading.
// See http://gmod.org/wiki/GFF3
// for details on the GFF file format.
type Reader struct {
	reader *csv.Reader
}

// Record represents a record in a GFF file.
type Record struct {
	Sequence   string
	Source     string
	Feature    string
	Start      string
	End        string
	Score      string
	Strand     string
	Phase      string
	Attributes map[string]string
}

// NewReader constructs a new reader for a GFF file.
// GFF files are essentially TSV files, so the GFF reader wraps a csv.Reader struct.
// Because GFF files may have FASTA sequences concatenated to the end of the file,
// the number of fields had to be set to -1 to avoid an error when reading FASTA lines.
func NewReader(file *os.File) *Reader {
	reader := csv.NewReader(bufio.NewReader(file))
	reader.Comma = '\t'
	reader.Comment = '#'
	reader.FieldsPerRecord = -1
	reader.LazyQuotes = true

	return &Reader{reader: reader}
}

// Read parses the GFF file and returns a channel producing the records.
// Because GFF files may have FASTA sequences concatenated to the end of the file,
// the number of records are checked at every line to avoid caching the FASTA data,
// since all GFF records will have 9 fields.
// Returning an channel allows the method to be called both synchronously or asynchronously.
func (reader *Reader) Read() <-chan Record {
	channel := make(chan Record)
	go func() {
		defer close(channel)
		record, _error := reader.reader.Read()
		for _error != io.EOF {
			if _error != nil {
				panic(_error)
			}
			if len(record) == 9 {
				channel <- Record{
					Sequence:   record[0],
					Source:     record[1],
					Feature:    record[2],
					Start:      record[3],
					End:        record[4],
					Score:      record[5],
					Strand:     record[6],
					Phase:      record[7],
					Attributes: extract(record[8]),
				}
			}
			record, _error = reader.reader.Read()
		}
	}()

	return channel
}

func extract(attributes string) map[string]string {
	ret := map[string]string{}
	for _, v := range strings.Split(attributes, ";") {
		attribute := strings.Split(v, "=")
		ret[attribute[0]] = attribute[1]
	}

	return ret
}
