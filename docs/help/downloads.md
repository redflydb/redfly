# Downloads

The "Download" button will download the checked CRM records in one of a variety of formats. At present, _**RED**fly_ supports the following options:

## FASTA

Sequences in multi-FASTA format. The FASTA header contains the following data:

\>CRM_name|species|gene|FlyBase_ID|chromosome

## CSV

Comma-separated list, one line per record. Fields are: "name", "species name", "gene_name", "identifier", "chromosome", "sequence"

## GFFv3

Data in GFF version 3 format. The "attributes" field holds the CRM name ("ID="); database identifiers ("dbxref=") for FlyBase, PubMed (PMID), and _**RED**fly_; and the expression terms("Ontology_term="). Note that because TFBSs and CRMs are not strand-specific sequence features, no strand information is specified in the GFF file.

## GBrowse Annotation Format

The format used by [GBrowse](http://gmod.org/wiki/GFF3) is to load local custom annotations. Newer versions of GBrowse (GBrowse2) use a modified version of this format. Downloads in this format will be included in the future. However, note that most GBrowse implementations can also accept custom annotations in GFFv3 format.

## BED

Data in [BED](https://genome.ucsc.edu/FAQ/FAQformat.html#format1) format. File type “BED simple” downloads a four-column BED file (chrom, start, end, name) with no headers, suitable for direct analysis or for use with a genome browser. File type “BED browser” produces a eight-column BED file with additional header information to enable richer functionality when used with the UCSC Genome [Browser](https://genome.ucsc.edu/index.html). Default track name of “CRMs” or “TFBS” and default track description of “CRMs (or TFBSs) selected from REDfly” is specified.

_**NOTE**_: There is a current download limit of 1000 records. To download more results, use the check box to select all displayed records in the current “Search Results” page and download; then move to the next page and repeat; and so on. Future _**RED**fly_ releases will allow direct downloading of the full search results.
