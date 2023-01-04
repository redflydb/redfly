# Detailed View Window

Results for each record are presented in a detailed view window composed of multiple tabs displaying different sections of the information for each entry.

## Information

The **Information** tab (see [Figure 2D](figures.md#figure-2)) contains the **genomic coordinates** of the feature based on the current sequence release. Coordinates for older releases can be obtained using the “**previous coordinates**” button. For RCs, the RC attributes — [has\_expression, is\_CRM, is\_minimized](data-types.md#reporter-constructs) — are listed. Other information contained in the Basic Info tab includes the "genome from" and "assayed in" **species**; the name of the **associated gene(s)** with links to relevant databases such as [FlyBase](http://flybase.org), [FlyMine](http://www.flymine.org), [VectorBase](https://www.vectorbase.org), [i5k Workspace@NAL](https://i5k.nal.usda.gov), etc.; and links to the [REDfly JBrowse](http://128.205.11.6/jbrowse) and [UCSC](http://genome.ucsc.edu/cgi-bin/hgGateway) genome browsers. Note that because TFBSs and CRMs are not strand-specific sequence features, no strand information is reflected in the graphical views. When accessing the REDfly JBrowse genome browser we have occasionally experienced a timeout error and are working to diagnose the cause. (Migration to JBrowse is under development.) The **REDfly ID** of the record and date of the **last update** are also provided.

## Location

 The Location tab (see [Figure 2E](figures.md#figure-2)) provides a snapshot of the genomic region and displays genes, transcripts, and CRMs. TFBSs, [inferred CRMs](data-types.md#inferred-crms), or new CRM annotations not yet in FlyBase are not currently displayed; however, a yellow vertical bar marks the position of the current feature. The [position](search-pane.md#position) of the feature relative to transcripts of the associated gene is provided above the graphic. This tab is not yet fully implemented for species other than _Drosophila melanogaster_.

## Images

The Images tab (RCs/CRMs only; see [Figure 2F](figures.md#figure-2)) shows the expression pattern of the reporter gene. A subset of these images are provided courtesy of [FlyExpress](http://www.flyexpress.net) and clicking on these will bring the user to the FlyExpress website, from which a search can be initiated for other genes with a similar expression pattern. Images are currently available for only a subset of _**RED**fly_ records. In many cases, if no image is available the figure number showing the RC in the published report is provided.

## Citation

The Citation tab displays the reference and PubMed ID(s) and links to the [PubMed](http://www.ncbi.nlm.nih.gov/pubmed/) record(s) for the current annotation. The name of the REDfly curator(s) responsible for annotating this feature is also provided. This tab also provides the [sequence source terms](detailed-view-window.md#citationevidence) and the [evidence](detailed-view-window.md#citationevidence) for the feature.

**Sequence Source Terms**: Many older references do not provide exact sequence referents (e.g., genome coordinates, PCR primer sequences, GenBank IDs). Most often, sequence ranges are given as restriction maps. Because sequence polymorphisms between the clones used by researchers and the published genome sequence can lead to gain or loss of restriction sites and thus affect our determination of the reported sequence, we differentiate between those sequences unambiguously provided in the reference or through communication with the authors and those inferred from restriction maps. In those places where we were unable to locate a referenced restriction site or where sizes of the restriction fragments were not well matched with the reported sizes, we list the sequence end as "estimated/uncertain." In time, we hope to reconcile all ambiguities through communication with the authors.

Sequences reported as "inferred from restriction map" use as endpoints the first nucleotide of the restriction site for both the 5' and 3' ends of the sequence. Depending on the actual cut site of the enzyme, therefore, and modification and/or sites used for subcloning, the exact CRM sequence tested by the authors may differ from the reported site by several basepairs.

Orientation of CRMs is given as matching the orientation of the transcription unit, i.e. "5' end estimated" refers to the 5' end of the CRM when oriented in the same 5' to 3' direction as the gene. Note that this may not be the 5' end of the sequence as shown/downloaded, as all sequences are listed relative to the + strand.

TFBS sequences initially from the [FlyReg database](https://academic.oup.com/bioinformatics/article/21/8/1747/249595) do not contain sequence source terms.

## RC/TFBS

All RC and CRM records are linked to the _**RED**fly_ annotations of any TFBSs that fall within them. These are listed in the TFBS tab (for RC/CRM records; see [Figure 2G](figures.md#figure-2)); clicking within a row will open a window with detailed results for that record. Similarly, if a TFBS falls within a known RC/CRM, the name of the RC/CRM and a link to its _**RED**fly_ record is provided in the RC tab. Searches of _**RED**fly_ can be restricted to just those TFBSs that map to known CRMs, and vice-versa, using the options in the [Advanced Search pane](search-pane.md#advanced-search).

## Sequence

The Sequence tab (see [Figure 2H](figures.md#figure-2)) displays the size (in basepairs) and sequence of the current feature. For TFBSs, the "sequence with flank" is also provided. This includes the TFBS sequence in capital letters, with approximately 20 bp of additional sequence extending on each end. This extended sequence allows for the usually short TFBSs to be mapped unambiguously to the genome.

## Anatomical Expressions

See also [Expression Pattern Annotations](expression-pattern-annotations.md) and [Searching Expression Patterns](search-pane.md#searching-expression-patterns).

The Expression tab (see [Figure 2I](figures.md#figure-2)) lists the expression terms and related attributes associated with each record, using the anatomy ontology as described [above](expression-pattern-annotations.md). Although TFBSs do not of themselves have expression patterns, where a TFBS maps in a RC/CRM, it inherits the expression pattern information from that RC/CRM. Clicking on a column header will sort by that column. Clicking on an expression term will initiate a _**RED**fly_ search in a new browser window for all records containing the specified term, while (for _Drosophila_ records) clicking on "FlyBaseID" will link to a FlyBase search for records associated with the expression term.

Additional columns provide information on developmental staging, sex-specific expression, ectopic expression (relative to the assigned target gene), and relevant Gene Ontology terms. A placeholder for enhancer/silencer information is also included; however, these designations are not yet implemented and the column can be disregarded. For more information about the attributes, see [Expression Pattern Annotations](expression-pattern-annotations.md)

## Notes

The Notes tab contains free-text notes that elaborate on the basic annotation of the feature. In particular, the notes can indicate details of expression patterns that cannot be adequately captured by the anatomy ontology.
