<?php include("header.php"); ?>
<div class="heading_c"> Release Notes </div>
<a name="2023-08-25"></a>
<?= HTML_REDFLY_LOGO ?> v9.6.2 Release Notes (August 25, 2023)
<ul>
  <li>Fix duplicate RCs bug.</li>
  <li>Correct error messages are reported when uploading attribute file and expression file.</li>
</ul>
<a name="2023-04-28"></a>
<?= HTML_REDFLY_LOGO ?> v9.6.1 Release Notes (April 28, 2023)
<ul>
  <li>Suspend updating of iCRM data until new iCRM algorithms are released.</li>
  <li>Some elements were renamed. #, ^ and ' were replaced with -, -, prime for better compatibility with computational parsers.</li>
</ul>
<a name="2023-01-12"></a>
<?= HTML_REDFLY_LOGO ?> v9.6.0 Release Notes (January 12, 2023)
<ul>
  <li>REDfly JBrowse component based on Drosophila melanogaster data goes live.</li>
</ul>
<a name="2022-10-21"></a>
<?= HTML_REDFLY_LOGO ?> v9.5.4 Release Notes (October 21, 2022)
<ul>
  <li>Refactor CRM algorithm and implement it in Rust by removing manual override and adding consideration for triple store data and fully nested set.</li>
  <li>Rewrite the entire update process in Python and Rust to improve performance by over 5x.</li>
</ul>
<a name="2022-08-19"></a>
<?= HTML_REDFLY_LOGO ?> v9.5.3 Release Notes (August 19, 2022)
<ul>
  <li>Fix bug that local BLAT service rejects shorter sequence entries.</li>
</ul>
<a name="2022-06-17"></a>
<?= HTML_REDFLY_LOGO ?> v9.5.2 Release Notes (June 17, 2022)
<ul>
  <li>Fix bug that the editor would be blocked when uploading a file with blank lines.</li>
  <li>Query PMID data from Flybase API serivce.</li>
</ul>
<a name="2021-08-18"></a>
<?= HTML_REDFLY_LOGO ?> v9.5.0 Release Notes (August 18, 2021)
<ul>
  <li>The REDfly source code available in GitHub</li>
  <li>Updated licensing and disclaimer information</li>
</ul>
<a name="2021-08-03"></a>
<?= HTML_REDFLY_LOGO ?> v9.4.4 Release Notes (August 3, 2021)
<ul>
  <li>Now the search by coordinates includes all the shorter sequences besides the sequences matched</li>
</ul>
<a name="2021-07-21"></a>
<?= HTML_REDFLY_LOGO ?> v9.4.3 Release Notes (July 21, 2021)
<ul>
  <li>A bug about not saving the previous coordinates of TFBS entities fixed</li>
  <li>Any absence of Google Analytics credentials handled</li>
</ul>
<a name="2021-07-19"></a>
<?= HTML_REDFLY_LOGO ?> v9.4.2 Release Notes (July 19, 2021)
<ul>
  <li>Audience reports generated from Google Reporting API 3</li>
  <li>No longer need to refresh the browser cache for new JavaScript versions</li>
</ul>
<a name="2021-07-01"></a>
<?= HTML_REDFLY_LOGO ?> v9.4.1 Release Notes (July 1, 2021)
<ul>
  <li>Google Analytics 4.0 being used by the REDfly project</li>
  <li>A multiple-empty-lines issue in the batch import menu fixed</li>
</ul>
<a name="2021-06-17"></a>
<?= HTML_REDFLY_LOGO ?> v9.4.0 Release Notes (June 17, 2021)
<ul>
  <li>New enhancer/silencer attribute available for curators and users</li>
  <li>Drosophila melanogaster ontology accessible for all the species annotation in staging data</li>
  <li>New relationship rule between CRM and negative attributes for updated reporter constructs</li>
  <li>The Stark lab link fixed for all the reporter constructs including VT</li>
  <li>Ambiguities about both cell line and cell culture terms fixed</li>
</ul>
<a name="2021-04-30"></a>
<?= HTML_REDFLY_LOGO ?> v9.3.2 Release Notes (April 30, 2021)
<ul>
  <li>Batch audit menu bugs fixed</li>
  <li>Better handling of FASTA headers in the batch import menu</li>
</ul>
<a name="2021-04-14"></a>
<?= HTML_REDFLY_LOGO ?> v9.3.0 Release Notes (April 14, 2021)
<ul>
  <li>Now developmental stages and biological processes included in the search interface</li>
  <li>Improvements and a better entity kind standardization applied on the search engine</li>
  <li>Inferred CRMs now handled by the search engine</li>
  <li>Batch downloads separated by species</li>
  <li>Batch downloads of predicted CRMs optimized for both CSV and FASTA formats</li>
  <li>All the fields dependent on the species in the user, curator, and batch interfaces being dynamically filtered on their species chosen</li>
  <li>The TFBS duplicate prevention mechanism improved regarding to their entity names and coordinates</li>
  <li>The REDfly database engine now being audited</li>
  <li>The diagram of the database schema has been updated for its version 9. Its link is <a href="./images/redfly_v9_schema.png">here</a></li>
</ul>
<a name="2021-02-26"></a>
<?= HTML_REDFLY_LOGO ?> v9.2.0 Release Notes (February 26, 2021)
<ul>
  <li>Now using the version 10.5 of MariaDB</li>
  <li>A few bugs fixed</li>
</ul>
<a name="2021-02-22"></a>
<?= HTML_REDFLY_LOGO ?> v9.1.0 Release Notes (February 22, 2021)
<ul>
  <li>Pubmed ID search expanded to include staging data</li>
  <li>Previous curators included in the entity information window</li>
  <li>A new web page about the distribution of REDfly entities by species and genome version</li>
  <li>Some obsolete source code cleanup</li>
</ul>
<a name="2021-02-04"></a>
<?= HTML_REDFLY_LOGO ?> v9.0.1 Release Notes (February 4, 2021)
<ul>
  <li>New Aedes aegypti data included in the database</li>
  <li>A few minor bugs discovered and fixed</li>
</ul>
<a name="2021-01-05"></a>
<?= HTML_REDFLY_LOGO ?> v9.0.0 Release Notes (January 5, 2021)
<ul>
  <li>New Tribolium castaneum data included in the database</li>
  <li>The termlookup mechanism improved with optimizations and species modularity for genes</li>
</ul>
<a name="2020-12-01"></a>
<?= HTML_REDFLY_LOGO ?> v8.2.2 Release Notes (December 1, 2020)
<ul>
  <li>Two new insect species, Aedes aegypti and Tribolium castaneum, available for REDfly curators</li>
  <li>Both BLAT and termlookup mechanisms improved for the multispecies factor</li>
</ul>
<a name="2020-11-19"></a>
<?= HTML_REDFLY_LOGO ?> v8.2.1 Release Notes (November 19, 2020)
<ul>
  <li>The coordinates search mechanism improved by including a common error margin for both coordinates</li>
  <li>BLAT searches following the new rule of 95% identity</li>
  <li>A few curation bugs fixed</li>
  <li>Some obsolete source code cleanup</li>
</ul>
<a name="2020-10-16"></a>
<?= HTML_REDFLY_LOGO ?> v8.2.0 Release Notes (October 16, 2020)
<ul>
  <li>The individual and batch editing of all REDfly entity kinds adapted for the multispecies factor</li>
  <li>Better entity list views in the curator interface</li>
  <li>The release dm3 coordinates retired from the website</li>
</ul>
<a name="2020-09-22"></a>
<?= HTML_REDFLY_LOGO ?> v8.1.0 Release Notes (September 22, 2020)
<ul>
  <li>New Anopheles gambiae data included in the database</li>
  <li>A few minor bugs discovered and fixed in the administration menu</li>
</ul>
<a name="2020-09-14"></a>
<?= HTML_REDFLY_LOGO ?> v8.0.0 Release Notes (September 14, 2020)
<ul>
  <li>Additional insect species included in the database</li>
  <li>A few old minor bugs discovered and fixed</li>
</ul>
<a name="2020-07-23"></a>
<?= HTML_REDFLY_LOGO ?> v7.1.1 Release Notes (July 23, 2020)
<ul>
  <li>Improvements in the curator interface</li>
  <li>Old minor bugs fixed in the GFF3 format from the individual and batch downloads</li>
  <li>Integrity checks in the database</li>
</ul>
<a name="2020-07-01"></a>
<?= HTML_REDFLY_LOGO ?> v7.1.0 Release Notes (July 1, 2020)
<ul>
  <li>Staging data available in the GFF3 format from the individual and batch downloads</li>
  <li>Secondary PMIDs from the staging data in the entity information windows</li>
  <li>Better awareness about expression terms having or not having staging data for curators</li>
</ul>
<a name="2020-06-17"></a>
<?= HTML_REDFLY_LOGO ?> v7.0.4 Release Notes (June 17, 2020)
<ul>
  <li>A new entity: <u>C</u>is-<u>R</u>egulatory <u>M</u>odule <u>Seg</u>ment (CRMseg) joining to its brotherly REDfly entities</li>
  <li>JSON data streams of REDfly entities following the HTTP compression</li>
  <li>New information windows for CRMseg and pCRM entities in the search results list</li>
  <li>Improved programming for both individual and batch downloads with the right file name and type for a better user experience</li>
  <li>A better defined role separation for both curator and administrator user kinds when working with new and existing REDfly records</li>
  <li>An enhanced awareness about the data transformation during each step of the REDfly release procedure which now archives current REDfly records no longer relevant</li>
  <li>Obsolete source code cleanup and formatting efforts for a better maintenance of the project software on the long term</li>
  <li>Many old minor bugs discovered and fixed</li>
</ul>
<a name="2020-02-18"></a>
<?= HTML_REDFLY_LOGO ?> v6.0.2 Release Notes (February 18, 2020)
<ul>
  <li>Fixed a bug about the UCSC links appointing to an older version dm5</li>
</ul>
<a name="2020-01-10"></a>
<?= HTML_REDFLY_LOGO ?> v6.0.1 Release Notes (January 10, 2020)
<ul>
  <li>Fixed a bug about the value inversion of the ectopic expression in the user display</li>
</ul>
<a name="2020-01-07"></a>
<?= HTML_REDFLY_LOGO ?> v6.0.0 Release Notes (January 7, 2020)
<ul>
  <li>New data model for RCs including developmental staging, associated GO terms, sex-specific expression, and ectopic expression</li>
  <li>Display of expanded data attributes in the detailed results “expression” tab</li>
  <li>Updated “help” documentation for new expression pattern attributes</li>
  <li>Bug fixes and refinements for curation processes including author notification emails, duplicate checking during data entry, and approval/rejection of records</li>
</ul>
<a name="2019-10-24"></a>
<?= HTML_REDFLY_LOGO ?> v5.6.1 Release Notes (October 24, 2019)
<ul>
  <li>Reorganized the field disposition in the RC editing panel for a better check about PMID contents</li>
  <li>Improved the window appearance and behavior of the information/warning/error messages in the RC editing panel</li>
  <li>Improved the curator information by email about the list of rejected records and their rejection reasons after their audits</li>
  <li>Fixed a minor bug in the release procedure</li>
</ul>
<a name="2019-08-26"></a>
<?= HTML_REDFLY_LOGO ?> v5.6 Release Notes (August 26, 2019)
<ul>
  <li>A new enhancement of developmental staging data for curators in the administration menu</li>
  <li>A new window frame to remind new REDfly users about the Twitter feedback and a new survey</li>
  <li>Included pCRMs in the number of publications curated</li>
  <li>Improved the lists of student curators to be thanked in a new yearly format</li>
  <li>Fixed a few minor bugs in the administration menu</li>
</ul>
<a name="2019-05-03"></a>
<?= HTML_REDFLY_LOGO ?> v5.5.3 Release Notes (May 3, 2019)
<ul>
  <li>Improved the performance of the Batch Audit dialog</li>
  <li>Fixed a bug where editing entities in the approved state could cause versioning issues</li>
  <li>Improve the curation pipeline by allowing multiple RCs to be saved with similar names and consolidated later</li>
  <li>Addressed an issue where certain PMIDs did not have citation data</li>
</ul>
<a name="2019-04-25"></a>
<?= HTML_REDFLY_LOGO ?> v5.5.2 Release Notes (April 25, 2019)
<ul>
  <li>Fixed a bug in the release process that resulted in ‘Attempt to update non-maximal version’ errors during curation</li>
  <li>Fixed a bug about not enabling curators to enter new RCs with no evidence subtype</li>
  <li>The TFBS misnaming issue is fixed</li>
  <li>The changes number in the updated database is finally reflected with all the new RC and TFBS records</li>
</ul>
<a name="2018-11-19"></a>
<?= HTML_REDFLY_LOGO ?> v5.5.1 Release Notes (November 19, 2018)
<ul>
  <li>Fixed BLAT screen scraper for updated results from UCSC Blat tool</li>
  <li>Ensure that Gene names are case sensitive</li>
  <li>Remove override when releasing data to ensure TFBS names start with TFBS name followed by Gene name (e.g., "abd-A_grim:REDFLY:TF000212")</li>
  <li>Updated help documentation, publications, and supporting funding</li>
</ul>
<a name="2018-08-24"></a>
<?= HTML_REDFLY_LOGO ?> v5.5 Release Notes (August 24, 2018)
<ul>
  <li>Back end updates to improve internal integration with FlyBase data</li>
  <li>Miscellanous updates to improve ease of deployments</li>
</ul>
<a name="2018-05-03"></a>
<?= HTML_REDFLY_LOGO ?> v5.4.3 Release Notes (May 03, 2018)
<ul>
  <li>An update to the back end to improve the speed of inferred CRM calculation</li>
</ul>
<a name="2018-02-15"></a>
<?= HTML_REDFLY_LOGO ?> v5.4.2 Release Notes (February 15, 2018)
<ul>
  <li>Inferred CRMs are now searchable by gene (both by name and by locus)</li>
  <li>The search interface is now ready for predicted CRM data (the first which will be appearing within the next few weeks)</li>
  <li>Predicted CRMs are searchable by gene (by locus only), name and PubMed ID</li>
  <li>Segment highlighting in the GBrowse images has been restored</li>
  <li>The help pages has been updated to reflect the above changes</li>
  <li>Many bug fixes and quality-of-life improvements to the curator tools</li>
</ul>
<a name="2018-01-03"></a>
<?= HTML_REDFLY_LOGO ?> v5.4.1 Release Notes (January 03, 2018)
<ul>
  <li>The BDGP in situ search tool has been discontinued. The FlyBase anatomy search tool is now used instead</li>
  <li>Location data has been restored to the detailed information window</li>
  <li>Based on feedback, the BED file downloads are now tab-delimited insstead of space-delimited</li>
  <li>Minor enhancements and updates to the help pages</li>
  <li>Many bug fixes and improvements to the curator tools</li>
</ul>
<a name="2017-11-20"></a>
<?= HTML_REDFLY_LOGO ?> v5.4 Release Notes (November 20, 2017)
<ul>
  <li>Brand new website design with a new look and feel</li>
  <li>The 'Contact' page is now back</li>
  <li>Inferred and predicted CRMs are now searchable using basic search</li>
  <li>Gene annotations and FlyBase anatomy ontology has been updated to the latest releases</li>
  <li>Many enhancements to the curator tools</li>
</ul>
<a name="2017-08-09"></a>
<?= HTML_REDFLY_LOGO ?> v5.3.1 Release Notes (August 09, 2017)
<ul>
  <li>Expression term filters in search were not working correctly unless you input the FBbt manually. This has been fixed</li>
  <li>Searches were not cleared correctly via the Clear Search Data button. This has been fixed</li>
  <li>A batch import feature has been added for curators</li>
  <li>And several bug fixes and improvements to the behind-the-scenes side of things</li>
</ul>
<a name="2017-07-18"></a>
<?= HTML_REDFLY_LOGO ?> v5.3 Release Notes (July 18, 2017)
<ul>
  <li>Pagination has been introduced to the search page. Searches are no longer limited to 1,000 results</li>
  <li>The search drop-down for expression terms was showing incomplete results. This has been fixed</li>
  <li>The FlyBase anatomy ontology has been updated to the 05/03/2017 release</li>
</ul>
<a name="2017-03-17"></a>
<?= HTML_REDFLY_LOGO ?> v5.2.2 Release Notes (March 17, 2017)
<ul>
  <li>The FlyBase anatomy ontology has been updated to the 02/15/2017 release</li>
  <li>The BED batch download now contains sequence coordinates in the half-open system</li>
  <li>Predicted CRMs has been added to the search interface -- predicted CRM data will be coming soon</li>
  <li>A bug that removes special characters in PubMed citations has been fixed</li>
  <li>Many improvements has been made to the back-end of the application resulting in a more confident and robust deployment process</li>
</ul>
<a name="2017-01-24"></a>
<?= HTML_REDFLY_LOGO ?> v5.2.1 Release Notes (January 24, 2017)
<ul>
  <li>125 new CRMs from the cispatterns paper (PubMed ID 26377945) has been added to the database</li>
  <li>152 new CRMs from the Fisher 2012 paper (PubMed ID 23236164) has been added to the database</li>
</ul>
<a name="2017-01-06"></a>
<?= HTML_REDFLY_LOGO ?> v5.2 Release Notes (January 6, 2017)
<ul>
  <li>Calculation of inferred CRMs has been implemented (administrators only)</li>
  <li>Inferred CRMs are now enabled. Inferred CRMs can only be browsed; searches is not implemented at this point</li>
  <li>Batch downloads now works again. Batch files can be downloaded in BED, CSV, FASTA, GFF3 and GBrowse formats for RCs, CRMs and TFBSes</li>
  <li>We have been querying the fly anatomy ontology via a database schema adapted to emulate an ontology. This has been replaced with an Apache Jena Fuseki SPARQL server, enabling us to query the ontology natively via SPARQL</li>
  <li>NCBI lookups was failing due to the switch from HTTP to HTTPS on their end. This has been fixed</li>
  <li>A thorough unit test suite has been developed for the new back end features</li>
  <li>The ontology data has been updated to include updates from the December 8, 2016 FlyBase fly anatomy ontology update</li>
  <li>The genes and features in the database has been updated based on the FB201605 release of the FlyBase genome data</li>
  <li>We have migrated to PHP 5.6 to take advantage of the new features and security updates -- PHP 5.5 has been end-of-lifed since July 2016</li>
  <li>We have completely migrated from PEAR to Composer for dependency management</li>
  <li>57 new CRMs from the Jin 2013 paper (PubMed ID 23326246) has been added to the database</li>
  <li>There were also miscellaneous minor bug and security fixes, especially surrounding database access, concurrency and external service access</li>
</ul>
<a name="2016-10-12"></a>
<?= HTML_REDFLY_LOGO ?> v5.1.1 Release Notes (October 12, 2016)
<ul>
  <li>Minor update to add a note to the search results informing the user that only 1,000 results are displayed</li>
</ul>
<a name="2016-07-22"></a>
<?= HTML_REDFLY_LOGO ?> v5.1 Release Notes (July 22, 2016)
<ul>
  <li>All genome and other supporting data updated to FlyBase version FB2016_03</li>
  <li>Add 10000+ cell-line only data</li>
  <li>Add "exclude cell-line only" option when searching</li>
  <li>Breakdown CRM numbers by category on homepage</li>
  <li>Link to Fly Enhancers</li>
  <li>Known Issues: Inferred CRM data is missing</li>
</ul>
<a name="2016-04-25"></a>
<?= HTML_REDFLY_LOGO ?> v5.0 Release Notes (April 25, 2016)
<ul>
  <li>Add "locus-based" search</li>
  <li>Update lengths of chromosome X, 2R, 2L, 3R, 3L, 4. Add Y chromosome</li>
  <li>Update the Gene table as follows:
  <ul>
       <li>delete unused genes</li>
       <li>update name and/or flybase id of remaining genes</li>
       <li>update RC and BS names to reflect gene name change</li>
       <li>repopulate the gene table with all the genes from FB2016_02</li>
       <li>add another column to the gene table to indicate associated chromosome</li>
  </ul></li>
  <li>Drop the features table and repopulate with only mRNA</li>
  <li>Update the Expression Term table as follows:
  <ul>
      <li>delete unused expression terms</li>
      <li>update name and/or id of remaining terms</li>
      <li>add a column in the Expression Term table to indicate if a term is deprecated</li>
      <li>repopulate the expression terms from the latest oncology data</li>
  </ul></li>
  <li>Add missing FBtp terms to 855 RCs</li>
  <li>Batch upload 129 RCs, 97 with images</li>
  <li>Make 13 citation entries ASCII compliant</li>
  <li>Fix 75 RC names not having gene name in them</li>
</ul>
<a name="2015-10-05"></a>
<?= HTML_REDFLY_LOGO ?> v4.1 Release Notes (October 05, 2015)
<ul>
  <li>Records can now be downloaded in BED Simple and BED Browser format. The BED Browser format is annotated for use with the Genome Browser</li>
  <li>The SOAP-based web service used for fetching PubMed citation was <a href="http://www.ncbi.nlm.nih.gov/books/NBK43082/">TERMINATED</a> on July 1, 2015. Code has been updated to use the current version of the <a href="http://www.ncbi.nlm.nih.gov/books/NBK25501/">Entrez Programming Utilities</a></li>
</ul>
<a name="2015-02-18"></a>
<?= HTML_REDFLY_LOGO ?> v4.0 Release Notes (February 18, 2015)
<ul>
  <li>All entities have been updated to include R6 coordinates. While R3 and R4 coordinates are maintained for historical reasons, they will no longer be available for download. If you require R3 or R4 coordinates please contact us</li>
  <li>GBrowse link has been restored to the current FlyBase GBrowse2 implementation. At present, we are experiencing slow performance as the REDfly custom annotations load. Please bear with us as we work to find a speedier solution</li>
  <li>Genomic region snapshots in the "location" results tab have been restored. Only Genes, Transcripts, and CRMs are currently displayed</li>
  <li>Minor errors in the "Advanced Search" functions have been corrected</li>
</ul>
<a name="2014-09-04"></a>
<?= HTML_REDFLY_LOGO ?> v3.3 Release Notes (September 04, 2014)
<ul>
  <li>Release v3.3 adds over 7830 new Reporter Constructs. This includes all of the Vienna Tile lines from Kvon et al. (2014) Nature 512(7512):91-5, PMID: 24896182. Note that because these lines have not been explicitly linked to target genes, the gene names in <?= HTML_REDFLY_LOGO ?> are given as "Unspecified." In future releases we will provide improved ways of searching for these lines as well as updated target gene information where possible</li>
  <li>The VT lines include "ubiquitous" as an expression term. As this term is not part of the Drosophila Anatomy Ontology, it will not appear in the "Expression" results  tab. However, this information, along with staging information for expression patterns, is included in the "Notes" tab</li>
  <li><?= HTML_REDFLY_LOGO ?> genome coordinates in this release remain at sequence version r5/dm3. We are in the process of updating to release 6 coordinates to bring <?= HTML_REDFLY_LOGO ?> back into harmony with the FlyBase genome annotation. At present, clicking the "GBrowse" link in the "Info" results pane links to the archived FlyBase FB2014_03 annotation. (If you are directed to the r6 GBrowse2 page, flush your browser cache or reload the page.) Also, until we have completed our migration to r6 coordinates, the "location" results tab has been disabled. We hope to have these updates completed in the near future. r5/dm3 coordinates will remain available for download for all entries</li>
  <li>We have fixed an error in how "minimization" status was being calculated</li>
</ul>
<?php include("footer.php"); ?>
