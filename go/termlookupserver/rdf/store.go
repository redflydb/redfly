package rdf

// #cgo pkg-config: raptor2 rasqal
// #cgo LDFLAGS: -lrdf
// #include <stdlib.h>
// #include <librdf.h>
import "C"
import (
	"os"
	"unsafe"
)

// Store represents a Redland execution environment.
type Store struct {
	world   *C.librdf_world
	storage *C.librdf_storage
	model   *C.librdf_model
}

// Open initializes a Redland execution environment with a RDF/XML file and
// readies it for querying. Because this uses the Redland C library directly and
// C does not have a garbage collector, memory reosurces must be freed manually.
// For this reason, make sure to call the Close method to free up all resources
// used by the RDF instance.
func Open(file *os.File) *Store {
	cStorageName := C.CString("hashes")
	defer C.free(unsafe.Pointer(cStorageName))
	cOptions := C.CString("hash-type='memory'")
	defer C.free(unsafe.Pointer(cOptions))
	cURIString := C.CString("file:" + file.Name())
	defer C.free(unsafe.Pointer(cURIString))
	cName := C.CString("rdfxml")
	defer C.free(unsafe.Pointer(cName))
	cMIMEType := C.CString("application/rdf+xml")
	defer C.free(unsafe.Pointer(cMIMEType))
	world := C.librdf_new_world()
	storage := C.librdf_new_storage(world, cStorageName, nil, cOptions)
	model := C.librdf_new_model(world, storage, nil)
	uri := C.librdf_new_uri(world, (*C.uchar)(unsafe.Pointer(cURIString)))
	defer C.librdf_free_uri(uri)
	parser := C.librdf_new_parser(world, cName, cMIMEType, nil)
	defer C.librdf_free_parser(parser)
	C.librdf_parser_parse_into_model(parser, uri, nil, model)
	C.librdf_world_open(world)

	return &Store{world: world, storage: storage, model: model}
}

// Query prepares an SPARQL query against the model and returns a channel
// producing the results. Returning a channel allows the method to be used both
// synchronously or asynchronously.
func (s *Store) Query(sparql string) <-chan map[string]string {
	cQuery := C.CString(sparql)
	defer C.free(unsafe.Pointer(cQuery))
	cName := C.CString("sparql")
	defer C.free(unsafe.Pointer(cName))
	query := C.librdf_new_query(s.world, cName, nil, (*C.uchar)(unsafe.Pointer(cQuery)), nil)
	defer C.librdf_free_query(query)
	results := C.librdf_model_query_execute(s.model, query)
	ch := make(chan map[string]string)
	go func() {
		defer C.librdf_free_query_results(results)
		defer close(ch)
		for C.librdf_query_results_finished(results) == 0 {
			row := map[string]string{}
			cBindingCount := C.librdf_query_results_get_bindings_count(results)
			for i := 0; i < int(cBindingCount); i++ {
				node := C.librdf_query_results_get_binding_value(results, C.int(i))
				cBindingName := C.librdf_query_results_get_binding_name(results, C.int(i))
				cLiteralValue := C.librdf_node_get_literal_value(node)
				row[C.GoString(cBindingName)] = C.GoString((*C.char)(unsafe.Pointer(cLiteralValue)))
				C.librdf_free_node(node)
			}
			ch <- row
			C.librdf_query_results_next(results)
		}
	}()

	return ch
}

// Close cleans up memory resources held by the Redland execution environment.
// This must be called explicitly because the Redland C library is being used
// directly, and C does not have a garbage collector like Go does.
func (s *Store) Close() {
	C.librdf_free_model(s.model)
	C.librdf_free_storage(s.storage)
	C.librdf_free_world(s.world)
}
