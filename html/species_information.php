<?php 
include("header.php");
$dbSpeciesUrl = $baseURL . "/api/rest/json/information/species";
$cUrlSession = curl_init($dbSpeciesUrl);
curl_setopt(
    $cUrlSession,
    CURLOPT_HEADER,
    false
);
curl_setopt(
    $cUrlSession,
    CURLOPT_RETURNTRANSFER,
    true
);
$result = curl_exec($cUrlSession);
if ( $result === false ) {
    print "<br> Error connecting to '" . $dbSpeciesUrl . "': " . curl_error($cUrlSession);
    curl_close($cUrlSession);
    exit();
}
$decode = json_decode($result);
$information = array_shift($decode->results);
$barcelona = 0;
?>
<br>
<div class="heading_c">Species</div>
<br>
<div>
  <a href="http:///199.109.192.174"
     target="_blank"><strong>BLAT</strong></a>
  Local implementation of 
  <a href="http://genome.ucsc.edu/cgi-bin/hgBlat"
     target="_blank"><strong>UCSC BLAT</strong></a> configured for all the species in the table below</div>
<br>
<div>
  <a href="http:///199.109.193.56"
     target="_blank"><strong>In-Silico PCR</strong></a>
  Local implementation of 
  <a href="http://genome.ucsc.edu/cgi-bin/hgPcr"
     target="_blank"><strong>UCSC In-Silico PCR</strong></a> configured for all the species in the table below</div>
<br>
<div>
  <table border="1" cellpadding="2">
    <tr>
      <th>&nbspScientific Name&nbsp</th>
      <th>&nbspGenome Version&nbsp</th>
      <th>&nbspCis-regulatory Modules&nbsp</th>
      <th>&nbspReporter Constructs&nbsp</th>
      <th>&nbspCis-regulatory Module Segments&nbsp</th>
      <th>&nbspPredicted Cis-regulatory Modules&nbsp</th>
      <th>&nbspTranscription Factor Binding Sites&nbsp</th>
    </tr>
      <?php
        $speciesNumber = count($information->species);
        for ( $speciesIndex = 0; $speciesIndex < $speciesNumber; $speciesIndex++) {
          print "<tr>";
          print "<td style=\"text-align:center\">" . $information->species[$speciesIndex]->scientific_name . "</td>";
          print "<td style=\"text-align:center\">" . $information->species[$speciesIndex]->release_version . "</td>";
          print "<td style=\"text-align:center\">" . $information->species[$speciesIndex]->crms_number . "</td>";
          print "<td style=\"text-align:center\">" . $information->species[$speciesIndex]->rcs_number . "</td>";
          print "<td style=\"text-align:center\">" . $information->species[$speciesIndex]->crmss_number . "</td>";
          print "<td style=\"text-align:center\">" . $information->species[$speciesIndex]->pcrms_number . "</td>";
          print "<td style=\"text-align:center\">" . $information->species[$speciesIndex]->tfbss_number . "</td>";
          print "</tr>";
        }
      ?>
  </table>
</div>  
<?php include("footer.php"); ?>
