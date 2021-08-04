package blatserver

import (
	"bytes"
	"fmt"
	"io"
	"io/ioutil"
	"net/http"
	"os"

	"github.com/gorilla/mux"
)

// BuildRoutes builds the routes for the RESTful API.
// The function takes the 2bit genome database files regarding to the following speces:
// 1) Aedes aegypti
// 2) Anopheles gambiae
// 3) Drosophila melanogaster
// 4) Tribolium castaneum
// as arguments for use in building the responses.
func BuildRoutes(
	aedesAegyptiGenomeFile *os.File,
	anophelesGambiaeGenomeFile *os.File,
	drosophilaMelanogasterGenomeFile *os.File,
	triboliumCastaneumGenomeFile *os.File,
) *mux.Router {
	r := mux.NewRouter()
	r.NotFoundHandler = http.HandlerFunc(func(
		w http.ResponseWriter,
		r *http.Request) {
		fmt.Fprint(w, "Error 404: Not found.")
	})
	r.HandleFunc("/",
		func(
			w http.ResponseWriter,
			r *http.Request) {
			var speciesShortName string
			var genomeDatabaseFile *os.File
			var filePath string
			var file *os.File
			var bytesNumber int64
			multipartReader, _error := r.MultipartReader()
			if _error != nil {
				panic(_error)
			}
			for {
				part, _error := multipartReader.NextPart()
				if _error == io.EOF {
					break
				}
				if part.FormName() == "speciesShortName" {
					buffer := new(bytes.Buffer)
					buffer.ReadFrom(part)
					speciesShortName = buffer.String()
					switch speciesShortName {
					case "aaeg":
						genomeDatabaseFile = aedesAegyptiGenomeFile
					case "agam":
						genomeDatabaseFile = anophelesGambiaeGenomeFile
					case "dmel":
						genomeDatabaseFile = drosophilaMelanogasterGenomeFile
					case "tcas":
						genomeDatabaseFile = triboliumCastaneumGenomeFile
					default:
						panic("Unknown species short name: " + speciesShortName)
					}
				} else {
					if part.FormName() == "input" {
						filePath = "./" + part.FileName()
						file, _error = os.Create(filePath)
						if _error != nil {
							panic(_error)
						}
						bytesNumber, _error = io.Copy(
							file,
							part)
						if _error != nil {
							panic(_error)
						}
						fmt.Println(bytesNumber, " bytes copied from part into file")
						_error = file.Sync()
						if _error != nil {
							panic(_error)
						}
						defer file.Close()
					}
				}
			}
			if speciesShortName == "" {
				panic("No species short name")
			}
			file, _error = os.OpenFile(
				filePath,
				os.O_RDONLY,
				0644)
			if _error != nil {
				panic(_error)
			}
			defer file.Close()
			in, _error := ioutil.TempFile(
				"",
				"fasta")
			if _error != nil {
				panic(_error)
			}
			defer in.Close()
			defer os.Remove(in.Name())
			out, _error := ioutil.TempFile(
				"",
				"pslx")
			if _error != nil {
				panic(_error)
			}
			defer out.Close()
			defer os.Remove(out.Name())
			bytesNumber, _error = io.Copy(
				in,
				file)
			if _error != nil {
				panic(_error)
			}
			fmt.Println(bytesNumber, " bytes copied from file into in")
			Search(genomeDatabaseFile,
				in,
				out)
			fmt.Println("Search done!")
			bytesNumber, _error = io.Copy(
				w,
				out)
			if _error != nil {
				panic(_error)
			}
			fmt.Println(bytesNumber, " bytes copied from out into w")
		}).Methods("POST")

	return r
}
