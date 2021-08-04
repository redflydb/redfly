# Search Pane

Basic search (see [Figure 2A](figures.md#figure-2)) allows for searching by **gene name**, **gene ID** (e.g., FlyBase ID (FBgn)), **FlyBase FBtp number** (for _Drosophila_ only), **element name**, **PubMed ID**, or **recent updates**; the latter will return all records entered on the most recent date of data entry/update. Options to “browse all” records and to download all Reporter Constructs, all CRMs, or all TFBSs are also available.

By default, searches will not return Reporter Construct/CRM records discovered exclusively through cell-culture based assays (this is to prevent the results from being dominated by RCs proven to function in only a single cell type). To include these RCs/CRMs, uncheck the “**Exclude Cell Line Only**” box to the right of the “search” button.

_**Gene names**_ should be searched as official gene symbols (e.g., dpp, h, betaTub60D) or  IDs (e.g., FBgn0000490, FBgn0003888), using the appropriate species-specific nomenclature. Greek letters have been written out (e.g., alpha, delta). At present only valid primary gene symbols are accepted. If the name of a gene does not appear in the drop-down, it is likely that a synonym rather than the primary name is being used. In such cases, retrieving the proper name from FlyBase (or the relevant species-specific database) and searching again should resolve the problem. The gene name “unspecified” has been included to allow for searching for RCs or TFBSs where the associated gene or transcription factor, respectively, is not known.

If “_**by name**_” is selected, the search will retrieve only those records explicitly annotated with the gene name being searched. The default behavior is “_**by locus**_,” which will retrieve all records with the current gene name, but also all other Reporter Constructs/TFBSs within the defined region (default 10,000 bp upstream and downstream of the named gene). This allows for retrieval of Reporter Constructs annotated as “unspecified” and those lying near a gene of interest but annotated as being associated with a different gene. The size of the genomic region to be searched can be modified using the “[search range interval](search-pane.md#search-range-interval)” box under Advanced Search. (Similar behavior can be achieved by using coordinates to specify a genomic region to search using the [location search](search-pane.md#location-search) features in the Advanced Search area.)

A wild-card is automatically appended to the end of the search string for all element name searches (e.g., searching for “eve_stripe” will return “eve_stripe1”, “eve_stripe2”, “eve_stripe3+7”, etc.).

## Basic Search

Basic search (see [Figure 2A](figures.md#figure-2)) allows for searching by **gene name**, **FlyBase ID (FBgn)**, **FlyBase FBtp number**, **element name**, **PubMed ID**, or **recent updates**; the latter will return all records entered on the most recent date of data entry/update. Options to “browse all” records and to download all Reporter Constructs, all CRMs, or all TFBSs are also available.

By default, searches will not return Reporter Construct/CRM records discovered exclusively through cell-culture based assays (this is to prevent the results from being dominated by RCs proven to function in only a single cell type). To include these RCs/CRMs, uncheck the “**Exclude Cell Line Only**” box to the right of the “search” button.

_**Gene names**_ should be searched as official gene symbols (e.g., dpp, h, betaTub60D) or  IDs (e.g., FBgn0000490, FBgn0003888). Consult the appropriate species-specific resource for updated nomenclature. Greek letters have been written out (e.g., alpha, delta). At present only valid primary gene symbols are accepted. If the name of a gene does not appear in the drop-down, it is likely that a synonym rather than the primary name is being used. In such cases, retrieving the proper name from the appropriate species-specific resource (e.g., FlyBase, VectorBase) and searching again should resolve the problem. The gene name “unspecified” has been included to allow for searching for RCs or TFBSs where the associated gene or transcription factor, respectively, is not known.

If “_**by name**_” is selected, the search will retrieve only those records explicitly annotated with the gene name being searched. The default behavior is “by locus,” which will retrieve all records with the current gene name, but also all other Reporter Constructs/TFBSs within the defined region (default 10,000 bp upstream and downstream of the named gene). This allows for retrieval of Reporter Constructs annotated as “unspecified” and those lying near a gene of interest but annotated as being associated with a different gene. The size of the genomic region to be searched can be modified using the “[search range interval](search-pane.md#search-range-interval)” box under Advanced Search. (Similar behavior can be achieved by using coordinates to specify a genomic region to search using the [Location Search](search-pane.md#location-search) features in the Advanced Search area).

A wild-card is automatically appended to the end of the search string for all _**element name**_ searches (e.g., searching for “eve_stripe” will return “eve_stripe1”, “eve_stripe2”, “eve_stripe3+7”, etc.).

## Advanced Search

The Advanced Search pane (see [Figure 2B](figures.md#figure-2)) is divided into two tabs, one for Reporter Construct/CRM options and one for TFBS options.

## RC/CRM Options

These include searching for all records, for CRM records only, for CRMs with associated TFBS data only, or for “[inferred CRMs](data-types.md#inferred-crms)”. These can be further filtered for positive vs. [negative expression](data-types.md#reporter-constructs) and for whether or not an element has undergone [minimization](data-types.md#reporter-constructs).

## TFBS Options

These allow for searching all TFBSs or only those with associated CRM data. Gene names can be used to search all TFBS records or only those where the named gene is either the target or encodes the transcription factor, respectively.

Details on search options are as follows:

## Position

Position search will select any RCs/CRMs or TFBSs located in the specified position relative to their target gene. Options are 5’ to the gene, 3’ to the gene, within an intron, or within an exon. Options are non-exclusive, i.e., a RC that begins 5’ to the gene and extends through the first intron will be found by a search for any of 5’, intron, or exon.

To be considered as overlapping a genomic feature, a regulatory element must extend greater than five bp into that feature. Thus, a CRM in the proximal promoter region that begins 500 bp 5’ to the transcription start of its gene and extends two bp into the first exon is considered to be exclusively 5’ to the gene and will not be returned on a search for elements within exons.

Positional information is reported in the [detailed view windows](detailed-view-window.md) in the [Location](detailed-view-window.md#location) tab.

## Location Search

Location search will select any RCs/CRMs or TFBSs lying within the specified sequence range, using release 6/dm6 coordinates. Coordinates from older releases can be converted through FlyBase’s “[Coordinates Converter](http://flybase.org/static_pages/downloads/COORD.html)” tool.

## Maximum Size

Selecting a maximum size will exclude any RCs/CRMs or TFBSs whose length is greater than the specified value, in basepairs.

## Search Range Interval

Search Range Interval sets the size of the genomic region to be searched when the [Basic Search “by locus”](search-pane.md#basic-search) option is selected. Default is 10,000 bp.

## Restrict Evidence To

This field allows the user to restrict a search to sequences supported by only certain types of evidence, e.g., TFBSs supported by DNAse I footprinting only.

## Searching Anatomical Expression Patterns

Searching using the Anatomical Expression Term search field will select records containing the specified term or any of its descendant terms in the anatomy ontology hierarchy; checking the “Exact Anatomical Expression Term” box will restrict the search to only that term. 

See also [Expression Pattern Annotations](expression-pattern-annotations.md).

For example, searching for "mesoderm" using the Anatomical Expression Term search will return annotations such as

- FBbt:00000126, mesoderm
- FBbt:00000127, head mesoderm
- FBbt:00000128, trunk mesoderm

Using "exact match," only

- FBbt:00000126, mesoderm

would be returned.

In practice, exact anatomical expression term searches will often be too restrictive.

# Searching Developmental Stage Patterns

Searching using the Developmental Stage Term search field will select records containing the specified term or any of its descendant terms in the deveopment ontology hierarchy; checking the “Exact Developmental Stage Term” box will restrict the search to only that term. 

# Searching Biological Processe Patterns

Searching using the Biological Process Term search field will select records containing the specified term or any of its descendant terms in the GO ontology hierarchy; checking the “Exact Biological Process Term” box will restrict the search to only that term.

## Last Updated After/Entry Added After

Placing a value in these fields will restrict the search results to those records that have been added or updated on or after the chosen date, respectively. Use the Last Updated feature to check for additions and corrections since your last search.