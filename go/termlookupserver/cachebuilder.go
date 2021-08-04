package termlookupserver

import (
	"database/sql"
	"fmt"
	"strings"

	"redfly.edu/termlookupserver/gff"
	"redfly.edu/termlookupserver/obo"
	"redfly.edu/termlookupserver/rdf"
)

// CacheBuilder represents a collection of methods that caches external terms to
// a local database for quick term lookups.
type CacheBuilder struct {
	cache *sql.DB
}

// NewCacheBuilder constructs a new Cacher instance.
func NewCacheBuilder(cache *sql.DB) *CacheBuilder {
	return &CacheBuilder{cache: cache}
}

// CacheAnatomicalExpressionTerms caches anatomical expression terms from both
// Drosophila melanogaster (FBbt) and Anopheles gambiae (TGMA) anatomical ontologies.
// See https://github.com/FlyBase/drosophila-anatomy-developmental-ontology.
func (cb *CacheBuilder) CacheAnatomicalExpressionTerms(
	speciesShortName string,
	reader *obo.Reader,
	service *AnatomicalExpressionService,
) {
	transaction, _error := cb.cache.Begin()
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	if _, _error := transaction.Exec(`
		CREATE TABLE IF NOT EXISTS anatomical_expression (
			species_short_name TEXT,
			identifier         TEXT,
			term               TEXT,
			PRIMARY KEY (species_short_name, identifier))`); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	statement, _error := transaction.Prepare(`
		INSERT INTO anatomical_expression (
			species_short_name,
			identifier,
			term)
		VALUES (
			?,
			?,
			?)`)
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	if reader != nil {
		for row := range reader.Read() {
			if _, _error := statement.Exec(
				speciesShortName,
				row.ID,
				row.Term,
			); _error != nil {
				if _error := transaction.Rollback(); _error != nil {
					panic(_error)
				}
				panic(_error)
			}
		}
		if _error := transaction.Commit(); _error != nil {
			if _error := transaction.Rollback(); _error != nil {
				panic(_error)
			}
			panic(_error)
		}
	}
	if service != nil {
		for _, expression := range service.GetAll() {
			if _, _error := statement.Exec(
				speciesShortName,
				expression.ID,
				expression.Term,
			); _error != nil {
				if _error := transaction.Rollback(); _error != nil {
					panic(_error)
				}
				panic(_error)
			}
		}
		if _error := transaction.Commit(); _error != nil {
			if _error := transaction.Rollback(); _error != nil {
				panic(_error)
			}
			panic(_error)
		}
	}
}

// CacheBiologicalProcessTerms caches biological process terms from the gene
// ontology.
// See http://www.geneontology.org.
func (cb *CacheBuilder) CacheBiologicalProcessTerms(store *rdf.Store) {
	transaction, _error := cb.cache.Begin()
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	if _, _error := transaction.Exec(`
		CREATE TABLE biological_process (
			identifier TEXT PRIMARY KEY,
			term       TEXT)`); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	statement, _error := transaction.Prepare(`
		INSERT INTO biological_process (
			identifier,
			term)
		VALUES (
			?,
			?)`)
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	query := `
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT DISTINCT ?go ?term
		WHERE
		{
			?x oboInOwl:id ?go .
			?x rdfs:label ?term .
			FILTER (STRSTARTS(?go, "GO:")) .
		}
	`
	for row := range store.Query(query) {
		if _, _error := statement.Exec(
			row["go"],
			row["term"],
		); _error != nil {
			if _error := transaction.Rollback(); _error != nil {
				panic(_error)
			}
			panic(_error)
		}
	}
	if _error := transaction.Commit(); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
}

// CacheDevelopmentalStageTerms caches developmental stage terms from the fly
// developmental (FBdv) ontology.
// See https://github.com/FlyBase/drosophila-anatomy-developmental-ontology.
func (cb *CacheBuilder) CacheDevelopmentalStageTerms(
	speciesShortName string,
	store *rdf.Store) {
	transaction, _error := cb.cache.Begin()
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	if _, _error := transaction.Exec(`
		CREATE TABLE IF NOT EXISTS developmental_stage (
			species_short_name TEXT,
			identifier         TEXT PRIMARY KEY,
			term               TEXT)`); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	statement, _error := transaction.Prepare(`
		INSERT INTO developmental_stage (
			species_short_name,
			identifier,
			term)
		VALUES (
			?,
			?,
			?)`)
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	// Drosophila melanogaster
	query := `
		PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
		PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
		PREFIX oboInOwl: <http://www.geneontology.org/formats/oboInOwl#>
		SELECT DISTINCT ?fbdv ?term
		WHERE
		{
			?x oboInOwl:id ?fbdv .
			?x rdfs:label ?term .
			FILTER (STRSTARTS(?fbdv, "FBdv:")) .
		}
	`
	for row := range store.Query(query) {
		if _, _error := statement.Exec(
			speciesShortName,
			row["fbdv"],
			row["term"],
		); _error != nil {
			if _error := transaction.Rollback(); _error != nil {
				panic(_error)
			}
			panic(_error)
		}
	}
	if _error := transaction.Commit(); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
}

// CacheFeatureTerms caches feature terms from both Drosophila melanogaster
// (See https://wiki.flybase.org/wiki/FlyBase:Downloads_Overview#GFF_files)
// and Anopheles gambiae precomputed GFF files.
func (cb *CacheBuilder) CacheFeatureTerms(
	speciesShortName string,
	reader *gff.Reader) {
	transaction, _error := cb.cache.Begin()
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	// The "type" word is already reserved by Go
	if _, _error := transaction.Exec(`
		CREATE TABLE IF NOT EXISTS feature (
			species_short_name TEXT,
			feature_type       TEXT,
			start              INTEGER,
			end                INTEGER,
			strand             TEXT,
			identifier         TEXT,
			name               TEXT,
			parent             TEXT)`); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	statement, _error := transaction.Prepare(`
		INSERT INTO feature (
			species_short_name,
			feature_type,
			start,
			end,
			strand,
			identifier,
			name,
			parent)
		VALUES (
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?)`)
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	// As the feature types in the commented lines are not included in the
	// initial specifications, such comments are necessary to avoid a cache
	// too big to be handled by the Apache/PHP server giving a memory crash.
	types := map[string]bool{
		//"CDS":             true,
		"exon": true,
		//"five_prime_UTR":  true,
		"intron": true,
		"mRNA":   true,
		//"ncRNA":           true,
		//"pseudogene": true,
		//"snoRNA":     true,
		//"snRNA":      true,
		//"three_prime_UTR": true,
		//"tRNA": true,
	}
	for row := range reader.Read() {
		if types[row.Feature] {
			switch row.Feature {
			case "exon", "intron":
				lineParts := strings.Split(row.Attributes["Parent"], ",")
				for _, value := range lineParts {
					if _, _error := statement.Exec(
						speciesShortName,
						strings.ToLower(row.Feature),
						row.Start,
						row.End,
						row.Strand,
						row.Attributes["ID"],
						row.Attributes["Name"],
						value,
					); _error != nil {
						if _error := transaction.Rollback(); _error != nil {
							panic(_error)
						}
						panic(_error)
					}
				}
			case "mRNA":
				if _, _error := statement.Exec(
					speciesShortName,
					strings.ToLower(row.Feature),
					row.Start,
					row.End,
					row.Strand,
					row.Attributes["ID"],
					row.Attributes["Name"],
					row.Attributes["Parent"],
				); _error != nil {
					if _error := transaction.Rollback(); _error != nil {
						panic(_error)
					}
					panic(_error)
				}
			default:
				fmt.Println("Unknown Feature Type: ", row.Feature)
			}
		}
	}
	if _error := transaction.Commit(); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
}

// CacheGeneTerms caches gene terms from both Drosophila melanogaster
// (See https://wiki.flybase.org/wiki/FlyBase:Downloads_Overview#GFF_files)
// and Anopheles gambiae precomputed GFF files.
func (cb *CacheBuilder) CacheGeneTerms(
	speciesShortName string,
	genomeAssemblyReleaseVersion string,
	reader *gff.Reader) {
	transaction, _error := cb.cache.Begin()
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	if _, _error := transaction.Exec(`
		CREATE TABLE IF NOT EXISTS gene (
			species_short_name              TEXT,
			genome_assembly_release_version TEXT,
			identifier                      TEXT PRIMARY KEY,
			term                            TEXT,
			chromosome_name                 TEXT,
			start                           INTEGER,
			end                             INTEGER,
			strand                          TEXT)`); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	statement, _error := transaction.Prepare(`
		INSERT INTO gene (
			species_short_name,
			genome_assembly_release_version,
			identifier,
			term,
			chromosome_name,
			start,
			end,
			strand)
		VALUES (
			?,
			?,
			?,
			?,
			?,
			?,
			?,
			?)`)
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	sequences := map[string]bool{
		"1":              true,
		"2":              true,
		"2L":             true,
		"2R":             true,
		"3":              true,
		"3L":             true,
		"3R":             true,
		"4":              true,
		"Mt":             true,
		"MT":             true,
		"NC_007416.3":    true,
		"NC_007417.3":    true,
		"NC_007418.3":    true,
		"NC_007419.2":    true,
		"NC_007420.3":    true,
		"NC_007421.3":    true,
		"NC_007422.5":    true,
		"NC_007423.3":    true,
		"NC_007424.3":    true,
		"NC_007425.3":    true,
		"NW_015450206.1": true,
		"NW_015450207.1": true,
		"NW_015450208.1": true,
		"NW_015450209.1": true,
		"NW_015450210.1": true,
		"NW_015450211.1": true,
		"NW_015450212.1": true,
		"NW_015450213.1": true,
		"NW_015450214.1": true,
		"NW_015450215.1": true,
		"NW_015450216.1": true,
		"NW_015450217.1": true,
		"NW_015450219.1": true,
		"NW_015450220.1": true,
		"NW_015450221.1": true,
		"NW_015450223.1": true,
		"NW_015450224.1": true,
		"NW_015450225.1": true,
		"NW_015450227.1": true,
		"NW_015450228.1": true,
		"NW_015450229.1": true,
		"NW_015450230.1": true,
		"NW_015450231.1": true,
		"NW_015450232.1": true,
		"NW_015450233.1": true,
		"NW_015450234.1": true,
		"NW_015450236.1": true,
		"NW_015450239.1": true,
		"NW_015450240.1": true,
		"NW_015450243.1": true,
		"NW_015450244.1": true,
		"NW_015450245.1": true,
		"NW_015450246.1": true,
		"NW_015450248.1": true,
		"NW_015450252.1": true,
		"NW_015450253.1": true,
		"NW_015450254.1": true,
		"NW_015450258.1": true,
		"NW_015450260.1": true,
		"NW_015450261.1": true,
		"NW_015450265.1": true,
		"NW_015450268.1": true,
		"NW_015450269.1": true,
		"NW_015450270.1": true,
		"NW_015450278.1": true,
		"NW_015450279.1": true,
		"NW_015450280.1": true,
		"NW_015450282.1": true,
		"NW_015450283.1": true,
		"NW_015450285.1": true,
		"NW_015450289.1": true,
		"NW_015450292.1": true,
		"NW_015450294.1": true,
		"NW_015450299.1": true,
		"NW_015450300.1": true,
		"NW_015450308.1": true,
		"NW_015450310.1": true,
		"NW_015450314.1": true,
		"NW_015450315.1": true,
		"NW_015450316.1": true,
		"NW_015450324.1": true,
		"NW_015450325.1": true,
		"NW_015450331.1": true,
		"NW_015450334.1": true,
		"NW_015450338.1": true,
		"NW_015450340.1": true,
		"NW_015450351.1": true,
		"NW_015450354.1": true,
		"NW_015450358.1": true,
		"NW_015450364.1": true,
		"NW_015450366.1": true,
		"NW_015450372.1": true,
		"NW_015450373.1": true,
		"NW_015450375.1": true,
		"NW_015450377.1": true,
		"NW_015450379.1": true,
		"NW_015450381.1": true,
		"NW_015450382.1": true,
		"NW_015450384.1": true,
		"NW_015450385.1": true,
		"NW_015450396.1": true,
		"NW_015450400.1": true,
		"NW_015450406.1": true,
		"NW_015450412.1": true,
		"NW_015450414.1": true,
		"NW_015450420.1": true,
		"NW_015450423.1": true,
		"NW_015450424.1": true,
		"NW_015450432.1": true,
		"NW_015450433.1": true,
		"NW_015450434.1": true,
		"NW_015450435.1": true,
		"NW_015450438.1": true,
		"NW_015450443.1": true,
		"NW_015450445.1": true,
		"NW_015450447.1": true,
		"NW_015450448.1": true,
		"NW_015450449.1": true,
		"NW_015450450.1": true,
		"NW_015450452.1": true,
		"NW_015450455.1": true,
		"NW_015450458.1": true,
		"NW_015450460.1": true,
		"NW_015450462.1": true,
		"NW_015450466.1": true,
		"NW_015450467.1": true,
		"NW_015450468.1": true,
		"NW_015450469.1": true,
		"NW_015450471.1": true,
		"NW_015450472.1": true,
		"NW_015450475.1": true,
		"NW_015450479.1": true,
		"NW_015450480.1": true,
		"NW_015450483.1": true,
		"NW_015450490.1": true,
		"NW_015450492.1": true,
		"NW_015450494.1": true,
		"NW_015450497.1": true,
		"NW_015450498.1": true,
		"NW_015450499.1": true,
		"NW_015450505.1": true,
		"NW_015450506.1": true,
		"NW_015450507.1": true,
		"NW_015450511.1": true,
		"NW_015450512.1": true,
		"NW_015450513.1": true,
		"NW_015450515.1": true,
		"NW_015450517.1": true,
		"NW_015450518.1": true,
		"NW_015450519.1": true,
		"NW_015450524.1": true,
		"NW_015450525.1": true,
		"NW_015450530.1": true,
		"NW_015450543.1": true,
		"NW_015450548.1": true,
		"NW_015450550.1": true,
		"NW_015450552.1": true,
		"NW_015450554.1": true,
		"NW_015450558.1": true,
		"NW_015450559.1": true,
		"NW_015450564.1": true,
		"NW_015450566.1": true,
		"NW_015450568.1": true,
		"NW_015450575.1": true,
		"NW_015450579.1": true,
		"NW_015450582.1": true,
		"NW_015450584.1": true,
		"NW_015450585.1": true,
		"NW_015450586.1": true,
		"NW_015450588.1": true,
		"NW_015450592.1": true,
		"NW_015450593.1": true,
		"NW_015450599.1": true,
		"NW_015450601.1": true,
		"NW_015450603.1": true,
		"NW_015450607.1": true,
		"NW_015450608.1": true,
		"NW_015450610.1": true,
		"NW_015450613.1": true,
		"NW_015450618.1": true,
		"NW_015450622.1": true,
		"NW_015450624.1": true,
		"NW_015450625.1": true,
		"NW_015450627.1": true,
		"NW_015450628.1": true,
		"NW_015450630.1": true,
		"NW_015450631.1": true,
		"NW_015450632.1": true,
		"NW_015450634.1": true,
		"NW_015450636.1": true,
		"NW_015450640.1": true,
		"NW_015450645.1": true,
		"NW_015450648.1": true,
		"NW_015450652.1": true,
		"NW_015450656.1": true,
		"NW_015450657.1": true,
		"NW_015450660.1": true,
		"NW_015450661.1": true,
		"NW_015450665.1": true,
		"NW_015450667.1": true,
		"NW_015450673.1": true,
		"NW_015450677.1": true,
		"NW_015450678.1": true,
		"NW_015450679.1": true,
		"NW_015450680.1": true,
		"NW_015450684.1": true,
		"NW_015450685.1": true,
		"NW_015450686.1": true,
		"NW_015450687.1": true,
		"NW_015450689.1": true,
		"NW_015450690.1": true,
		"NW_015450691.1": true,
		"NW_015450692.1": true,
		"NW_015450694.1": true,
		"NW_015450695.1": true,
		"NW_015450698.1": true,
		"NW_015450700.1": true,
		"NW_015450709.1": true,
		"NW_015450712.1": true,
		"NW_015450713.1": true,
		"NW_015450718.1": true,
		"NW_015450719.1": true,
		"NW_015450720.1": true,
		"NW_015450721.1": true,
		"NW_015450722.1": true,
		"NW_015450728.1": true,
		"NW_015450730.1": true,
		"NW_015450731.1": true,
		"NW_015450732.1": true,
		"NW_015450733.1": true,
		"NW_015450739.1": true,
		"NW_015450742.1": true,
		"NW_015450746.1": true,
		"NW_015450750.1": true,
		"NW_015450751.1": true,
		"NW_015450753.1": true,
		"NW_015450763.1": true,
		"NW_015450765.1": true,
		"NW_015450768.1": true,
		"NW_015450769.1": true,
		"NW_015450772.1": true,
		"NW_015450777.1": true,
		"NW_015450785.1": true,
		"NW_015450787.1": true,
		"NW_015450792.1": true,
		"NW_015450798.1": true,
		"NW_015450802.1": true,
		"NW_015450810.1": true,
		"NW_015450817.1": true,
		"NW_015450820.1": true,
		"NW_015450821.1": true,
		"NW_015450826.1": true,
		"NW_015450827.1": true,
		"NW_015450829.1": true,
		"NW_015450833.1": true,
		"NW_015450838.1": true,
		"NW_015450843.1": true,
		"NW_015450845.1": true,
		"NW_015450850.1": true,
		"NW_015450854.1": true,
		"NW_015450858.1": true,
		"NW_015450859.1": true,
		"NW_015450862.1": true,
		"NW_015450864.1": true,
		"NW_015450865.1": true,
		"NW_015450870.1": true,
		"NW_015450871.1": true,
		"NW_015450872.1": true,
		"NW_015450873.1": true,
		"NW_015450874.1": true,
		"NW_015450887.1": true,
		"NW_015450888.1": true,
		"NW_015450890.1": true,
		"NW_015450893.1": true,
		"NW_015450895.1": true,
		"NW_015450901.1": true,
		"NW_015450903.1": true,
		"NW_015450905.1": true,
		"NW_015450909.1": true,
		"NW_015450915.1": true,
		"NW_015450917.1": true,
		"NW_015450919.1": true,
		"NW_015450920.1": true,
		"NW_015450933.1": true,
		"NW_015450934.1": true,
		"NW_015450935.1": true,
		"NW_015450940.1": true,
		"NW_015450942.1": true,
		"NW_015450947.1": true,
		"NW_015450949.1": true,
		"NW_015450951.1": true,
		"NW_015450953.1": true,
		"NW_015450956.1": true,
		"NW_015450958.1": true,
		"NW_015450959.1": true,
		"NW_015450963.1": true,
		"NW_015450970.1": true,
		"NW_015450971.1": true,
		"NW_015450974.1": true,
		"NW_015450982.1": true,
		"NW_015450983.1": true,
		"NW_015450984.1": true,
		"NW_015450989.1": true,
		"NW_015450990.1": true,
		"NW_015450991.1": true,
		"NW_015450993.1": true,
		"NW_015450997.1": true,
		"NW_015451003.1": true,
		"NW_015451004.1": true,
		"NW_015451006.1": true,
		"NW_015451007.1": true,
		"NW_015451020.1": true,
		"NW_015451024.1": true,
		"NW_015451025.1": true,
		"NW_015451027.1": true,
		"NW_015451028.1": true,
		"NW_015451034.1": true,
		"NW_015451035.1": true,
		"NW_015451036.1": true,
		"NW_015451037.1": true,
		"NW_015451040.1": true,
		"NW_015451046.1": true,
		"NW_015451048.1": true,
		"NW_015451049.1": true,
		"NW_015451051.1": true,
		"NW_015451052.1": true,
		"NW_015451053.1": true,
		"NW_015451057.1": true,
		"NW_015451059.1": true,
		"NW_015451063.1": true,
		"NW_015451065.1": true,
		"NW_015451066.1": true,
		"NW_015451069.1": true,
		"NW_015451072.1": true,
		"NW_015451073.1": true,
		"NW_015451074.1": true,
		"NW_015451078.1": true,
		"NW_015451081.1": true,
		"NW_015451083.1": true,
		"NW_015451084.1": true,
		"NW_015451085.1": true,
		"NW_015451087.1": true,
		"NW_015451090.1": true,
		"NW_015451091.1": true,
		"NW_015451098.1": true,
		"NW_015451103.1": true,
		"NW_015451106.1": true,
		"NW_015451108.1": true,
		"NW_015451110.1": true,
		"NW_015451113.1": true,
		"NW_015451120.1": true,
		"NW_015451121.1": true,
		"NW_015451122.1": true,
		"NW_015451124.1": true,
		"NW_015451127.1": true,
		"NW_015451129.1": true,
		"NW_015451130.1": true,
		"NW_015451139.1": true,
		"NW_015451140.1": true,
		"NW_015451142.1": true,
		"NW_015451146.1": true,
		"NW_015451153.1": true,
		"NW_015451156.1": true,
		"NW_015451158.1": true,
		"NW_015451159.1": true,
		"NW_015451162.1": true,
		"NW_015451164.1": true,
		"NW_015451167.1": true,
		"NW_015451168.1": true,
		"NW_015451172.1": true,
		"NW_015451173.1": true,
		"NW_015451176.1": true,
		"NW_015451179.1": true,
		"NW_015451181.1": true,
		"NW_015451190.1": true,
		"NW_015451191.1": true,
		"NW_015451193.1": true,
		"NW_015451194.1": true,
		"NW_015451195.1": true,
		"NW_015451196.1": true,
		"NW_015451203.1": true,
		"NW_015451204.1": true,
		"NW_015451210.1": true,
		"NW_015451215.1": true,
		"NW_015451216.1": true,
		"NW_015451222.1": true,
		"NW_015451226.1": true,
		"NW_015451229.1": true,
		"NW_015451232.1": true,
		"NW_015451234.1": true,
		"NW_015451235.1": true,
		"NW_015451236.1": true,
		"NW_015451240.1": true,
		"NW_015451241.1": true,
		"NW_015451244.1": true,
		"NW_015451256.1": true,
		"NW_015451257.1": true,
		"NW_015451259.1": true,
		"NW_015451264.1": true,
		"NW_015451270.1": true,
		"NW_015451272.1": true,
		"NW_015451273.1": true,
		"NW_015451275.1": true,
		"NW_015451277.1": true,
		"NW_015451278.1": true,
		"NW_015451279.1": true,
		"NW_015451280.1": true,
		"NW_015451282.1": true,
		"NW_015451283.1": true,
		"NW_015451296.1": true,
		"NW_015451301.1": true,
		"NW_015451304.1": true,
		"NW_015451314.1": true,
		"NW_015451323.1": true,
		"NW_015451324.1": true,
		"NW_015451325.1": true,
		"NW_015451330.1": true,
		"NW_015451335.1": true,
		"NW_015451340.1": true,
		"NW_015451346.1": true,
		"NW_015451347.1": true,
		"NW_015451349.1": true,
		"NW_015451350.1": true,
		"NW_015451351.1": true,
		"NW_015451353.1": true,
		"NW_015451355.1": true,
		"NW_015451357.1": true,
		"NW_015451359.1": true,
		"NW_015451361.1": true,
		"NW_015451367.1": true,
		"NW_015451371.1": true,
		"NW_015451373.1": true,
		"NW_015451377.1": true,
		"NW_015451378.1": true,
		"NW_015451383.1": true,
		"NW_015451386.1": true,
		"NW_015451387.1": true,
		"NW_015451398.1": true,
		"NW_015451400.1": true,
		"NW_015451405.1": true,
		"NW_015451407.1": true,
		"NW_015451410.1": true,
		"NW_015451415.1": true,
		"NW_015451417.1": true,
		"NW_015451420.1": true,
		"NW_015451429.1": true,
		"NW_015451430.1": true,
		"NW_015451432.1": true,
		"NW_015451441.1": true,
		"NW_015451442.1": true,
		"NW_015451444.1": true,
		"NW_015451445.1": true,
		"NW_015451448.1": true,
		"NW_015451449.1": true,
		"NW_015451451.1": true,
		"NW_015451453.1": true,
		"NW_015451457.1": true,
		"NW_015451458.1": true,
		"NW_015451459.1": true,
		"NW_015451460.1": true,
		"NW_015451463.1": true,
		"NW_015451466.1": true,
		"NW_015451472.1": true,
		"NW_015451479.1": true,
		"NW_015451482.1": true,
		"NW_015451484.1": true,
		"NW_015451486.1": true,
		"NW_015451489.1": true,
		"NW_015451491.1": true,
		"NW_015451492.1": true,
		"NW_015451500.1": true,
		"NW_015451502.1": true,
		"NW_015451507.1": true,
		"NW_015451510.1": true,
		"NW_015451511.1": true,
		"NW_015451512.1": true,
		"NW_015451514.1": true,
		"NW_015451515.1": true,
		"NW_015451523.1": true,
		"NW_015451524.1": true,
		"NW_015451526.1": true,
		"NW_015451537.1": true,
		"NW_015451538.1": true,
		"NW_015451544.1": true,
		"NW_015451548.1": true,
		"NW_015451551.1": true,
		"NW_015451552.1": true,
		"NW_015451555.1": true,
		"NW_015451556.1": true,
		"NW_015451559.1": true,
		"NW_015451560.1": true,
		"NW_015451565.1": true,
		"NW_015451567.1": true,
		"NW_015451569.1": true,
		"NW_015451572.1": true,
		"NW_015451575.1": true,
		"NW_015451576.1": true,
		"NW_015451578.1": true,
		"NW_015451581.1": true,
		"NW_015451586.1": true,
		"NW_015451593.1": true,
		"NW_015451595.1": true,
		"NW_015451597.1": true,
		"NW_015451599.1": true,
		"NW_015451603.1": true,
		"NW_015451613.1": true,
		"NW_015451616.1": true,
		"NW_015451619.1": true,
		"NW_015451620.1": true,
		"NW_015451622.1": true,
		"NW_015451623.1": true,
		"NW_015451627.1": true,
		"NW_015451628.1": true,
		"NW_015451632.1": true,
		"NW_015451637.1": true,
		"NW_015451643.1": true,
		"NW_015451645.1": true,
		"NW_015451646.1": true,
		"NW_015451650.1": true,
		"NW_015451652.1": true,
		"NW_015451654.1": true,
		"NW_015451657.1": true,
		"NW_015451658.1": true,
		"NW_015451660.1": true,
		"NW_015451663.1": true,
		"NW_015451664.1": true,
		"NW_015451669.1": true,
		"NW_015451672.1": true,
		"NW_015451674.1": true,
		"NW_015451675.1": true,
		"NW_015451677.1": true,
		"NW_015451679.1": true,
		"NW_015451681.1": true,
		"NW_015451683.1": true,
		"NW_015451686.1": true,
		"NW_015451687.1": true,
		"NW_015451689.1": true,
		"NW_015451690.1": true,
		"NW_015451692.1": true,
		"NW_015451694.1": true,
		"NW_015451697.1": true,
		"NW_015451702.1": true,
		"NW_015451708.1": true,
		"NW_015451710.1": true,
		"NW_015451713.1": true,
		"NW_015451716.1": true,
		"NW_015451717.1": true,
		"NW_015451718.1": true,
		"NW_015451723.1": true,
		"NW_015451727.1": true,
		"NW_015451728.1": true,
		"NW_015451732.1": true,
		"NW_015451733.1": true,
		"NW_015451739.1": true,
		"NW_015451743.1": true,
		"NW_015451748.1": true,
		"NW_015451751.1": true,
		"NW_015451752.1": true,
		"NW_015451754.1": true,
		"NW_015451755.1": true,
		"NW_015451756.1": true,
		"NW_015451758.1": true,
		"NW_015451761.1": true,
		"NW_015451767.1": true,
		"NW_015451771.1": true,
		"NW_015451772.1": true,
		"NW_015451775.1": true,
		"NW_015451776.1": true,
		"NW_015451779.1": true,
		"NW_015451780.1": true,
		"NW_015451782.1": true,
		"NW_015451785.1": true,
		"NW_015451788.1": true,
		"NW_015451794.1": true,
		"NW_015451796.1": true,
		"NW_015451805.1": true,
		"NW_015451813.1": true,
		"NW_015451814.1": true,
		"NW_015451817.1": true,
		"NW_015451819.1": true,
		"NW_015451821.1": true,
		"NW_015451822.1": true,
		"NW_015451823.1": true,
		"NW_015451826.1": true,
		"NW_015451827.1": true,
		"NW_015451829.1": true,
		"NW_015451839.1": true,
		"NW_015451840.1": true,
		"NW_015451841.1": true,
		"NW_015451855.1": true,
		"NW_015451856.1": true,
		"NW_015451859.1": true,
		"NW_015451868.1": true,
		"NW_015451872.1": true,
		"NW_015451873.1": true,
		"NW_015451876.1": true,
		"NW_015451878.1": true,
		"NW_015451881.1": true,
		"NW_015451893.1": true,
		"NW_015451895.1": true,
		"NW_015451896.1": true,
		"NW_015451900.1": true,
		"NW_015451903.1": true,
		"NW_015451904.1": true,
		"NW_015451906.1": true,
		"NW_015451909.1": true,
		"NW_015451911.1": true,
		"NW_015451916.1": true,
		"NW_015451918.1": true,
		"NW_015451921.1": true,
		"NW_015451922.1": true,
		"NW_015451925.1": true,
		"NW_015451926.1": true,
		"NW_015451931.1": true,
		"NW_015451934.1": true,
		"NW_015451935.1": true,
		"NW_015451936.1": true,
		"NW_015451937.1": true,
		"NW_015451942.1": true,
		"NW_015451946.1": true,
		"NW_015451948.1": true,
		"NW_015451955.1": true,
		"NW_015451957.1": true,
		"NW_015451959.1": true,
		"NW_015451965.1": true,
		"NW_015451967.1": true,
		"NW_015451969.1": true,
		"NW_015451971.1": true,
		"NW_015451976.1": true,
		"NW_015451977.1": true,
		"NW_015451978.1": true,
		"NW_015451984.1": true,
		"NW_015451987.1": true,
		"NW_015451992.1": true,
		"NW_015451994.1": true,
		"NW_015452010.1": true,
		"NW_015452014.1": true,
		"NW_015452015.1": true,
		"NW_015452022.1": true,
		"NW_015452024.1": true,
		"NW_015452027.1": true,
		"NW_015452029.1": true,
		"NW_015452036.1": true,
		"NW_015452039.1": true,
		"NW_015452040.1": true,
		"NW_015452047.1": true,
		"NW_015452051.1": true,
		"NW_015452060.1": true,
		"NW_015452061.1": true,
		"NW_015452063.1": true,
		"NW_015452069.1": true,
		"NW_015452070.1": true,
		"NW_015452072.1": true,
		"NW_015452074.1": true,
		"NW_015452075.1": true,
		"NW_015452076.1": true,
		"NW_015452077.1": true,
		"NW_015452079.1": true,
		"NW_015452080.1": true,
		"NW_015452087.1": true,
		"NW_015452089.1": true,
		"NW_015452100.1": true,
		"NW_015452101.1": true,
		"NW_015452103.1": true,
		"NW_015452105.1": true,
		"NW_015452109.1": true,
		"NW_015452111.1": true,
		"NW_015452113.1": true,
		"NW_015452116.1": true,
		"NW_015452117.1": true,
		"NW_015452119.1": true,
		"NW_015452123.1": true,
		"NW_015452127.1": true,
		"NW_015452129.1": true,
		"NW_015452131.1": true,
		"NW_015452132.1": true,
		"NW_015452133.1": true,
		"NW_015452135.1": true,
		"NW_015452136.1": true,
		"NW_015452137.1": true,
		"NW_015452142.1": true,
		"NW_015452145.1": true,
		"NW_015452147.1": true,
		"NW_015452151.1": true,
		"NW_015452152.1": true,
		"NW_015452154.1": true,
		"NW_015452159.1": true,
		"NW_015452163.1": true,
		"NW_015452165.1": true,
		"NW_015452166.1": true,
		"NW_015452170.1": true,
		"NW_015452172.1": true,
		"NW_015452175.1": true,
		"NW_015452181.1": true,
		"NW_015452185.1": true,
		"NW_015452186.1": true,
		"NW_015452187.1": true,
		"NW_015452188.1": true,
		"NW_015452189.1": true,
		"NW_015452193.1": true,
		"NW_015452196.1": true,
		"NW_015452199.1": true,
		"NW_015452202.1": true,
		"NW_015452203.1": true,
		"NW_015452204.1": true,
		"NW_015452206.1": true,
		"NW_015452207.1": true,
		"NW_015452208.1": true,
		"NW_015452214.1": true,
		"NW_015452217.1": true,
		"NW_015452224.1": true,
		"NW_015452225.1": true,
		"NW_015452226.1": true,
		"NW_015452227.1": true,
		"NW_015452229.1": true,
		"NW_015452235.1": true,
		"NW_015452240.1": true,
		"NW_015452242.1": true,
		"NW_015452250.1": true,
		"NW_015452251.1": true,
		"NW_015452253.1": true,
		"NW_015452258.1": true,
		"NW_015452259.1": true,
		"NW_015452260.1": true,
		"NW_015452265.1": true,
		"NW_015452266.1": true,
		"NW_015452274.1": true,
		"NW_015452276.1": true,
		"U":              true,
		"UNKN":           true,
		"X":              true,
		"Y":              true,
		"Y_unplaced":     true,
	}
	var attributeIdentifier string
	var attributeName string
	for row := range reader.Read() {
		if row.Feature == "gene" && sequences[row.Sequence] {
			if speciesShortName != "tcas" {
				if row.Attributes["Name"] == "" {
					attributeIdentifier = row.Attributes["ID"]
					attributeName = row.Attributes["ID"]
				} else {
					attributeIdentifier = row.Attributes["ID"]
					attributeName = row.Attributes["Name"]
				}
			} else {
				attributeIdentifier = row.Attributes["Name"]
				attributeName = row.Attributes["Name"]
			}
			if _, _error := statement.Exec(
				speciesShortName,
				genomeAssemblyReleaseVersion,
				attributeIdentifier,
				attributeName,
				row.Sequence,
				row.Start,
				row.End,
				row.Strand,
			); _error != nil {
				if _error := transaction.Rollback(); _error != nil {
					panic(_error)
				}
				fmt.Println(
					speciesShortName,
					genomeAssemblyReleaseVersion,
					attributeIdentifier,
					attributeName)
				panic(_error)
			}
		}
	}
	if _error := transaction.Commit(); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
}

// CacheTransgenicConstructTerms caches transgenic construct terms and their
// PubMed citations from the FlyBase public database.
// See https://wiki.flybase.org/wiki/FlyBase:Downloads_Overview#The_FTP_archive.
// Credit goes to Dave Gerrard at the University of Manchester
// (http://personalpages.manchester.ac.uk/staff/David.Gerrard) for the query.
func (cb *CacheBuilder) CacheTransgenicConstructTerms(database *sql.DB) {
	transaction, _error := cb.cache.Begin()
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	if _, _error := transaction.Exec(`
		CREATE TABLE transgene (
			species_short_name TEXT,
			pubmed_id          TEXT,
			identifier         TEXT,
			term               TEXT)`); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	statement, _error := transaction.Prepare(`
		INSERT INTO transgene (
			species_short_name,
			pubmed_id,
			identifier,
			term)
		VALUES (
			?,
			?,
			?,
			?)`)
	if _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
	query := `
		SELECT 'dmel' AS species_short_name,
			dbx.accession AS pubmed_id,
			f.uniquename AS fbtp,
			f.name AS term
		FROM feature AS f,
			feature_cvterm AS fcvt,
			pub AS p,
			cvterm AS cvt,
			cv,
			pub_dbxref AS pdbx,
			dbxref AS dbx,
			db
		WHERE db.name = 'pubmed' AND
			pdbx.is_current AND
			p.pub_id = pdbx.pub_id AND
			pdbx.dbxref_id = dbx.dbxref_id AND
			dbx.db_id = db.db_id AND
			p.is_obsolete = 'f' AND
			cv.name = 'transgene_description' AND
			fcvt.pub_id = p.pub_id AND
			f.feature_id = fcvt.feature_id AND
			fcvt.cvterm_id = cvt.cvterm_id AND
			cvt.cv_id = cv.cv_id AND
			f.is_analysis = 'f'
	`
	rows, _error := database.Query(query)
	if _error != nil {
		panic(_error)
	}
	for rows.Next() {
		var speciesShortName, pubmed, identifier, term string
		if _error := rows.Scan(
			&speciesShortName,
			&pubmed,
			&identifier,
			&term,
		); _error != nil {
			if _error := transaction.Rollback(); _error != nil {
				panic(_error)
			}
			panic(_error)
		}
		if _, _error := statement.Exec(
			speciesShortName,
			pubmed,
			identifier,
			term,
		); _error != nil {
			if _error := transaction.Rollback(); _error != nil {
				panic(_error)
			}
			panic(_error)
		}
	}
	if _error := transaction.Commit(); _error != nil {
		if _error := transaction.Rollback(); _error != nil {
			panic(_error)
		}
		panic(_error)
	}
}
