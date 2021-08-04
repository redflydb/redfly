# Data Types

_**RED**fly_ seeks to include all experimentally verified insect cis-regulatory modules (CRMs) and transcription factor binding sites (TFBSs), along with their DNA sequence, their associated genes, and the expression patterns they direct. Sequences are stored in _**RED**fly_ as several main data classes: _**Reporter Constructs**_, _**CRM Segments**_, _**predicted CRMs (pCRMS)**_, _**inferred CRMs (iCRMs)**_, and _**TFBSs**_.

## Reporter Constructs

Any sequence tested by reporter gene assay is included in _**RED**fly_ as a “Reporter Construct” (RC) and has three associated attributes: (1) **expression**; (2) **CRM**; and (3) **minimization**.

1. Expression has value “positive” or “negative” and describes whether or not the sequence was reported to drive gene expression in the reporter gene assay. RCs with positive expression have their expression patterns annotated (see [Expression Pattern Annotations](expression-pattern-annotations.md)).

   Note that RCs recorded as “negative” for regulatory activity should be treated with caution by the user; as with any negative data, the failure to observe reporter gene activity could simply reflect a failure of the assay rather than a biological result. The sequence might still mediate gene regulation in a tissue not examined by the reporting researcher, require a promoter different than the one used in the reporter constuct in order to function, or be a silencer or other form of negative regulatory element not detectable in the assay.
   
   Because insect CRMs are often tested in transgenic _Drosophila_, _**RED**fly_ divides the sequence and gene feature data, and the expression pattern and cell line data, into separate components. Each record in _**RED**fly_ has both a “sequence from” and an “assayed in” component. Sequence and gene feature data are linked to the former, and anatomy and staging data to the latter. For instance, a CRM from the mosquito _Anopheles gambiae_ tested in a reporter gene construct in transgenic _Drosophila melanogaster_ would have its "sequence from" set to _A. gambiae_, with sequence coordinates, target gene name, and genomic location data drawn from the _A. gambiae_ sequence and genome annotation; but its "assayed in" component set to _D. melanogaster_, with expression patterns and staging described using the _D. melanogaster_ anatomy and development ontologies. On the other hand, if the same sequence had been tested in transgenic mosquitoes, it would be listed as "assayed in" _A. gambiae_, and mosquito-specific anatomy and staging provided.

2. If only a single sequence covering a given set of genomic coordinates is annotated, this sequence is considered a CRM. Where multiple nested sequences with identical activity are present, the shortest such sequence is designated as a CRM. In other words, we define CRM as the minimal-length reporter construct in a set of one or more nested reporter constructs that produce the same gene expression pattern (see [Figure 1](figures.md#figure-1)). A CRM can never have its expression attribute be "negative."

   Note that on occasion it will appear as though several nested RCs have the same activity (i.e., are associated with identical [expression terms](detailed-view-window.md#expression)) yet are all designated as CRMs. This situation arises when the constructs actually drive different gene expression patterns, but at a level that is not easily captured by the anatomy ontology used to annotate the expression (e.g., two different subsets of motor neuron, both annotated simply as “motor neuron”). These differences will usually be clarified in the free text notes accompanying the record.

3. When a CRM is part of a set of nested sequences, rather than a single tested sequence at a particular locus, we say that the CRM and associated RCs have undergone “**minimization**.” (see [Figure 1](figures.md#figure-1))

## CRM Segments

Sometimes experiments—for instance, analysis of small chromosomal deletions, or site-directed mutagenesis—demonstrate that a sequence is required for gene expression, but do not speak to the sufficiency of the sequence to be defined as a complete CRM. The “**CRM segment**” class holds any such necessary but not clearly sufficient sequences. CRM_segments are considered a separate data type and are cross-referenced with any RC/CRMs they overlap.

## Predicted CRMs

CRMs predicted based on computational or genomic assays, but not tested experimentally for regulatory function, are “predicted CRMs (pCRMs).” At present, pCRMs falling within the defined search locus are displayed in the *Predicted CRM* tab, but are not available for download and cannot be searched directly. More extensive pCRM search, display, and download capabilities will be included in future releases. In the meantime, you can [contact us](http://redfly.ccr.buffalo.edu/contact.php) for a full list of _**RED**fly_ pCRMs.

## Inferred CRMs

Sequences suspected to be CRMs based on regions of overlap between reporter constructs with similar activity, but not experimentally demonstrated to be so, are designated as “inferred CRMs.” Note that unlike Reporter Constructs, inferred CRMs have no empirical evidence supporting their functionality. (see [Figure 1](figures.md#figure-1)) At present, sets of overlapping RCs that include an RC with “negative” expression activity are excluded from determination of iCRMs.

## TFBSs

TFBSs in _**RED**fly_ derive mainly (but not exclusively) from two sources of evidence: DNAse I footprinting experiments and electrophoretic mobility shift assays (EMSA, “gel shift”).

For footprinting experiments, when a binding factor purified from nuclear extract has been shown to be the derivative of a specific gene, footprints were attributed to the gene encoding that factor, otherwise the binding factor for nuclear extract footprints has been left as "unspecified." Where possible we followed the rule of precedence in attributing footprint data to a particular reference, unless members of the same research group reported refined coordinates in a subsequent publication. When two or more overlapping motifs for the same transcription factor were reported for a single footprinted region, they were merged and annotated as one footprint. References that used non-_D. melanogaster_ proteins or non-D. melanogaster target DNA have been excluded, since these experiments do not represent biological meaningful regulatory interactions in vivo. The majority of footprinted sites were assembled initially from the [FlyReg database](https://academic.oup.com/bioinformatics/article/21/8/1747/249595).

Whereas DNAse I footprinting provides an exact sequence for the binding site, TFBSs obtained from EMSA experiments formally can be said only to bind somewhere within the sequence of the probe used in the assay (typically 20-50 bp in length). In most cases, the authors have provided a presumed binding sequence within the probe, and we have used this to represent the binding site.

Yeast one-hybrid (Y1H) data are derived from high-throughput Y1H studies such as those described by [Hens et al. (2011) Nature Methods, 8(12), 1065–1070](https://www.nature.com/articles/nmeth.1763). Unlike footprinting assays, which provide a defined binding site, and EMSAs, in which the binding sequence is often inferred by authors, YIH data, if derived from large bait sequences, can be of much lower sequence resolution. To prevent such sequences from showing up in TFBS search results, restrict the “evidence types” to exclude Y1H data, or use the [“maximum size” advanced search option](search-pane.md#maximum-size) to restrict results to short sequences.



