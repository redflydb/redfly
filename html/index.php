<?php
include("header.php");
include("reminder.php");
include("news.php");
?>
<br>
<div class="main_text">
  <p align="center" style="margin-top: 0;">
    <a href="http://128.205.11.6/search/options?sequence=1&assayed=1&cellCulture=1&range=10000#basic" target="_blank">
      <button class="new_search_button">Try to new Search Tool (beta)</button>
    </a>
    <a href="search.php">
      <button class="search_button" onclick="document.location.reload(true)">Search <?= HTML_REDFLY_LOGO ?></button>
    </a>
  </p>
  <p>Thank you for visiting <?= HTML_REDFLY_LOGO ?>! We are hard at work
bringing you new features and a better user experience.
We want to hear where you want to see us go!
  <br>
We appreciate your feedback!
  <div class="heading_release_l">
    </p>
    <div class="separator">
      <span class="red_circle"></span>
      <span class="red_circle"></span>
      <span class="red_circle"></span>
    </div>
  </div>
  <div class="info">
    <div>
      <img class="watermark" src="/images/logos/logo.png">
    </div>
    <p>
    <?= HTML_REDFLY_LOGO ?> is a curated collection of known insect transcriptional
<i>cis</i>-regulatory modules (CRMs), <i>cis</i>-regulatory module segments
(CRMsegs), predicted <i>cis</i>-regulatory modules (pCRMs), and transcription
factor binding sites (TFBSs). Our mission is to extract, accumulate, organize,
annotate, and link the growing body of information on insect transcriptional
<i>cis</i>-regulatory sequences, with an emphasis on empirically validated CRMs
curated from the published literature. The majority of the data are for
<i>Drosophila melanogaster</i>, but we also annotate an increasing number of other
insects.
Despite more than 20 years of experimental determination of these elements,
the data have never been collected into a single searchable database.
<?= HTML_REDFLY_LOGO ?> seeks to include all experimentally verified fly regulatory
elements along with their DNA sequence, their associated genes, and the expression
patterns they direct. Expression patterns are annotated using a
<a href="https://github.com/FlyBase/drosophila-anatomy-developmental-ontology/wiki"
   target="_blank">defined anatomy ontology</a> to enable high interoperability with
<a href="http://flybase.bio.indiana.edu/"
   target="_blank">FlyBase</a>,
<a href="http://www.flyexpress.net/"
   target="_blank">FlyExpress</a>, the
<a href="http://insitu.fruitfly.org/cgi-bin/ex/insitu.pl"
   target="_blank">BDGP in situ hybridization database</a>, and other model organism resources.
    </p>
    <p>
If you know of experimentally verified <i>cis</i>-regulatory elements that
are not included in the <?= HTML_REDFLY_LOGO ?> database, or have corrections to the
archived data, please <a href="contact.php">contact us</a>.
    </p>
    <p>
<?= HTML_REDFLY_LOGO ?> has
<?= $numberCrms ?> CRMs (<?= $numberCrmsInVivo ?> from in vivo reporter genes,
<?= $numberCrmsCellCultureOnly ?> from cell-culture assays, and
<?= $numberCrmsNonInVivoHavingNoCellCulture ?> from other evidence) associated with
<?= $numberCrmGenes ?> genes,
<?= $numberCrmSegments ?> CRMsegs associated with
<?= $numberCrmSegmentGenes  ?> genes,
<?= $numberPredictedCrms ?> pCRMs, and
<?= $numberTfbss ?> TFBSs bound by
<?= $numberTfbsTfs ?> transcription factors acting on
<?= $numberTfbsGenes ?> target genes. These data are based on
<?= $numberPublications ?> curated publications.
    </p>
    <div class="separator">
      <span class="red_circle"></span>
      <span class="red_circle"></span>
      <span class="red_circle"></span>
    </div>
    <div class="cite_reminder">
The regulatory data curated by <?= HTML_REDFLY_LOGO ?> are the result of the hard work of members of the research community.
When using specific CRM, CRMseg, pCRM, and TFBS data, please make sure to cite the original discoverers.
    </div>
    <p align="center">
      <a href="http://128.205.11.6/search/options?sequence=1&assayed=1&cellCulture=1&range=10000#basic" target="_blank">
        <button class="new_search_button">Try to new Search Tool (beta)</button>
      </a>
      <a href="search.php">
        <button class="search_button" onclick="document.location.reload(true)">Search <?= HTML_REDFLY_LOGO ?></button>
      </a>
    </p>
    <div align="center">
      <a href="https://twitter.com/REDfly_database" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @REDfly_database</a>
      <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
    </div>
  </div>
</div>
<?php include("footer.php"); ?>
