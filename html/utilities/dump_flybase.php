<?php
//  Dump the current data in the GFF3 format for inclusion in FlyBase
require_once(dirname(__FILE__) . "/../../config/linker.php");
$exportReporterConstructs = false;
$exportTranscriptionFactorBindingSites = false;
$quietMode = false;
$outputFile = null;
if ( $_SERVER["argc"] === 1 ) { 
    usage_and_exit();
}
for ( $index = 1;
    $index < $_SERVER["argc"];
    $index++ ) {
    switch ( $_SERVER["argv"][$index] ) {
        case "--help":
            usage_and_exit();
            break;        
        case "--out":
            if ( ($index + 1) > $_SERVER["argc"] ) { 
                usage_and_exit("Output file not specified");
            }
            $file = trim($_SERVER["argv"][++$index]);
            if ( strpos($file, "/") === 0 ) {
                $outputFile = $file;
            } else {
                $outputFile = $GLOBALS["options"]->general->site_base_dir . "/" .
                    $GLOBALS["options"]->general->data_dump_dir . "/" . $file;
            }
            break;
        case "--quiet":
            $quietMode = true;
            break;    
        case "--rc":
            $exportReporterConstructs = true;
            break;
        case "--tfbs":  
            $exportTranscriptionFactorBindingSites = true;
            break;
        default:
            usage_and_exit();
            break;
    }
}
if ( $outputFile === null  ) {
    usage_and_exit("Output file not specified");
}
else if ( (! $exportReporterConstructs) &&
    (! $exportTranscriptionFactorBindingSites) ) {
    usage_and_exit("No export options specified");
}
$arguments = array(
    "format" => "gff3",
    "sort"   => "chr"
);
$downloader = DownloadHandler::factory();
if ( $exportReporterConstructs ) {
    $response = $downloader->reporterconstructAction($arguments);
    if ( $response->success() ) {
        file_put_contents(
            $outputFile,
            $response->results()
        );
        chmod($outputFile, 0644);
    }
    else {
        displayMessage("Reporter constructs dumping error: " .
            $response->message());
    }
}
if ( $exportTranscriptionFactorBindingSites ) {
    $response = $downloader->transcriptionfactorbindingsiteAction($arguments);
    if ( $response->success() ) {
        file_put_contents(
            $outputFile,
            $response->results()
        );
        chmod($outputFile, 0644);
    }
    else {
        displayMessage("Transcription factor binding sites dumping error: " .
            $response->message());
    }
}
exit();
function displayMessage($message)
{
    if ( $GLOBALS["quietMode"] ) return;
    fwrite(
        STDOUT,
        $message. "\n"
    );
}
function usage_and_exit($message = NULL)
{
    if ( $message !== null  ) {
        print "\n" . $message . "\n";
    }
    print "Usage: " .
        $_SERVER["argv"][0] . " ( --rc | --tfbs ) [ --quiet ] --out <output_filename>" .
        "\nIf the output filename does not begin with a / it will be placed relative to " .
        $GLOBALS["options"]->general->site_base_dir . "/" .
        $GLOBALS["options"]->general->data_dump_dir . "\n";
    exit(1);
}
?>
