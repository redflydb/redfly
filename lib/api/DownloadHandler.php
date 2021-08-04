<?php
// --------------------------------------------------------------------------------
// Handle download requests via the API.
// The download handler, in conjunction with the special "raw" output format,
// allows the application to provide files for download directly to the user.
// The "raw" output format allows the handler to specify a set of HTTP headers
// and a single result that will be sent to the requesting application.
// --------------------------------------------------------------------------------
class DownloadHandler
{
    // Available download formats to be displayed for the user
    private $_downloadFormats = array(
        "bed",
        "csv",
        "fasta",
        "fff",
        "gff3"
    );
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new DownloadHandler;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "Download a list of the entities specified by their REDfly " .
            "identifiers in the HTTP post in the requested format.";
        $options = array(
            "fasta_seq"     => "The type of sequence to download: 'both sequence and " .
                               "sequence with flank', 'sequence only', or 'flank only' " .
                               "(default is 'sequence only'). " .
                               "The options are both, seq, and flank",
            "filename"      => "The optional name for the downloaded file",
            "format"        => "The download format. " .
                               "The available formats are: " .
                               implode(",", $this->_downloadFormats)
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Generate a file dump of the selected REDfly entities specified in the post data.
    // If there are more than 1000 selected REDfly entities, only the first 1000 ones
    // are downloaded due to the PHP restriction of the "max_input_vars" configuration
    // variable
    // --------------------------------------------------------------------------------
    public function listAction(
        array $arguments,
        array $postData = null
    ) {
        $crmSegmentList = array();
        $predictedCrmList = array();
        $reporterConstructList = array();
        $transcriptionFactorBindingSiteList = array();
        $helper = RestHandlerHelper::factory();
        if ( (count($postData ?? []) === 0) ||
            (! array_key_exists("redfly_id", $postData)) ) {
            return RestResponse::factory(
                false,
                "No REDfly entities provided"
            );
        }
        // Verify that the specified download format is supported and
        // instantiate the exporter.
        $exporter = ExportFile::factory($arguments);
        // Split the list of entities into
        //  1) CRM segments
        //  2) Predicted CRMs
        //  3) Reporter constructs
        //  4) Transcription factor binding sites
        $type = $entity = $version = $dbId = null;
        foreach ( $postData["redfly_id"] as $id ) {
            $helper->parseEntityId(
                $id,
                $type,
                $entity,
                $version,
                $dbId
            );
            switch ( $type ) {
                //  1) CRM segments
                case CrmsegmentHandler::EntityCode:
                    $crmSegmentList[] = $id;
                    break;
                //  2) Predicted CRMs
                case PredictedcrmHandler::EntityCode:
                    $predictedCrmList[] = $id;
                    break;
                //  3) Reporter constructs
                case ReporterconstructHandler::EntityCode:
                    $reporterConstructList[] = $id;
                    break;
                //  4) Transcription factor binding sites
                case TranscriptionfactorbindingsiteHandler::EntityCode:
                    $transcriptionFactorBindingSiteList[] = $id;
                    break;
                default:
                    return RestResponse::factory(
                        false,
                        "Unknown entity type: " . $type
                    );
            }
        }
        $entityName = "";
        $selectedEntities = "";
        $entitiesNumber = 0;
        $entityKindsNumber = 0;
        //  1) CRM segments
        if ( count($crmSegmentList) !== 0 ) {
            $crmSegmentArguments = array_merge(
                $arguments,
                array("redfly_id" => $crmSegmentList)
            );
            $crmSegmentHandler = CrmsegmentHandler::factory();
            $crmSegmentResponse = $crmSegmentHandler->getAction(
                $crmSegmentArguments,
                $postData
            );
            $crmSegmentsNumber = $crmSegmentResponse->numResults();
            if ( $crmSegmentsNumber > 0 ) {
                $entitiesNumber = $entitiesNumber + $crmSegmentsNumber;
                $entityKindsNumber++;
                $exporter->setCisRegulatoryModuleSegments($crmSegmentResponse->results());
                if ( $entitiesNumber === 1 ) {
                    $entityName = $crmSegmentResponse->results()[0]["name"];
                } else {
                    $selectedEntities = "selected_crm_segments";
                }
            }
        }
        //  2) Predicted CRMs
        if ( count($predictedCrmList) !== 0 ) {
            $predictedCrmArguments = array_merge(
                $arguments,
                array("redfly_id" => $predictedCrmList)
            );
            $predictedCrmHandler = PredictedcrmHandler::factory();
            $predictedCrmResponse = $predictedCrmHandler->getAction(
                $predictedCrmArguments,
                $postData
            );
            $predictedCrmsNumber = $predictedCrmResponse->numResults();
            if ( $predictedCrmsNumber > 0 ) {
                $entitiesNumber = $entitiesNumber + $predictedCrmsNumber;
                $entityKindsNumber++;
                $exporter->setPredictedCisRegulatoryModules($predictedCrmResponse->results());
                if ( $predictedCrmsNumber === 1 ) {
                    if ( ($entitiesNumber === 1) &&
                        ($entityKindsNumber === 1) ) {
                        $entityName = $predictedCrmResponse->results()[0]["name"];
                    } else {
                        if ( ($entitiesNumber > 1) &&
                            ($entityKindsNumber === 1) ) {
                            $entityName = "";
                            $selectedEntities = "selected_predicted_crms";
                        } else {
                            $entityName = "";
                            $selectedEntities = "selected_entities";
                        }
                    }
                } else {
                    $entityName = "";
                    if ( $entityKindsNumber === 1 ) {
                        $selectedEntities = "selected_predicted_crms";
                    } else {
                        $selectedEntities = "selected_entities";
                    }
                }
            }
        }
        //  3) Reporter constructs
        if ( count($reporterConstructList) !== 0 ) {
            $reporterConstructArguments = array_merge(
                $arguments,
                array("redfly_id" => $reporterConstructList)
            );
            $reporterConstructHandler = ReporterconstructHandler::factory();
            $reporterConstructResponse = $reporterConstructHandler->getAction(
                $reporterConstructArguments,
                $postData
            );
            $reporterConstructsNumber = $reporterConstructResponse->numResults();
            if ( $reporterConstructsNumber > 0 ) {
                $entitiesNumber = $entitiesNumber + $reporterConstructsNumber;
                $entityKindsNumber++;
                $exporter->setReporterConstructs($reporterConstructResponse->results());
                if ( $reporterConstructsNumber === 1 ) {
                    if ( ($entitiesNumber === 1) &&
                        ($entityKindsNumber === 1) ) {
                        $entityName = $reporterConstructResponse->results()[0]["name"];
                    } else {
                        if ( ($entitiesNumber > 1) &&
                            ($entityKindsNumber === 1) ) {
                            $entityName = "";
                            $selectedEntities = "selected_rcs";
                        } else {
                            $entityName = "";
                            $selectedEntities = "selected_entities";
                        }
                    }
                } else {
                    $entityName = "";
                    if ( $entityKindsNumber === 1 ) {
                        $selectedEntities = "selected_rcs";
                    } else {
                        $selectedEntities = "selected_entities";
                    }
                }
            }
        }
        //  4) Transcription factor binding sites
        if ( count($transcriptionFactorBindingSiteList) !== 0 ) {
            $transcriptionFactorBindingSiteArguments = array_merge(
                $arguments,
                array("redfly_id" => $transcriptionFactorBindingSiteList)
            );
            $transcriptionFactorBindingSiteHandler = TranscriptionfactorbindingsiteHandler::factory();
            $transcriptionFactorBindingSiteResponse = $transcriptionFactorBindingSiteHandler->getAction(
                $transcriptionFactorBindingSiteArguments,
                $postData
            );
            $transcriptionFactorBindingSitesNumber = $transcriptionFactorBindingSiteResponse->numResults();
            if ( $transcriptionFactorBindingSitesNumber > 0 ) {
                $entitiesNumber = $entitiesNumber + $transcriptionFactorBindingSitesNumber;
                $entityKindsNumber++;
                $exporter->setTranscriptionFactorBindingSites($transcriptionFactorBindingSiteResponse->results());
                if ( $transcriptionFactorBindingSitesNumber === 1 ) {
                    if ( ($entitiesNumber === 1) &&
                        ($entityKindsNumber === 1) ) {
                        $entityName = $transcriptionFactorBindingSiteResponse->results()[0]["name"];
                    } else {
                        if ( ($entitiesNumber > 1) &&
                            ($entityKindsNumber === 1) ) {
                            $entityName = "";
                            $selectedEntities = "selected_tfbss";
                        } else {
                            $entityName = "";
                            $selectedEntities = "selected_entities";
                        }
                    }
                } else {
                    $entityName = "";
                    if ( $entityKindsNumber === 1 ) {
                        $selectedEntities = "selected_tfbss";
                    } else {
                        $selectedEntities = "selected_entities";
                    }
                }
            }
        }

        return RestResponse::factory(
            true,
            "",
            array($exporter->getFile()),
            $exporter->getHtmlHeaders(
                $entityName,
                $selectedEntities
            )
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "crmsegment" action
    // --------------------------------------------------------------------------------
    public function crmsegmentHelp()
    {
        $description = "Download a cis-regulatory module segment, optionally filtered";
        $options = array(
            "fasta_seq" => "The type of sequence to download: 'both sequence and " .
                           "sequence with flank', 'sequence only', or 'flank' only " .
                           "(default is 'sequence only'). " .
                           "The options are both, seq, and flank",
            "filename"  => "The optional name for the downloaded file",
            "format"    => "The download format. " .
                           "The available formats are: " .
                           implode(",", $this->_downloadFormats)
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Generate the file dump of a unique cis-regulatory module segment specified in
    // the post data
    // --------------------------------------------------------------------------------
    public function crmsegmentAction(
        array $arguments,
        array $postData = null
    ) {
        $entityName = "";
        // Verify that the specified download format is supported and instantiate
        // the exporter.
        $exporter = ExportFile::factory($arguments);
        // We can use the search action but we want to get a full list of results
        $arguments = array_merge(
            $arguments,
            array("view" => "full")
        );
        if ( $postData !== null ) {
            $arguments = array_merge(
                $arguments,
                array("redfly_id" => $postData["redfly_id"][0])
            );
        }
        $crmSegmentHandler = CrmsegmentHandler::factory();
        $crmSegmentResponse = $crmSegmentHandler->searchAction($arguments);
        if ( $crmSegmentResponse->numResults() > 0 ) {
            $entityName = $crmSegmentResponse->results()[0]["name"];
            $exporter->setCisRegulatoryModuleSegments($crmSegmentResponse->results());
        }

        return RestResponse::factory(
            true,
            "",
            array($exporter->getFile()),
            $exporter->getHtmlHeaders(
                $entityName,
                ""
            )
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "predictedcrm" action
    // --------------------------------------------------------------------------------
    public function predictedcrmHelp()
    {
        $description = "Download a predicted cis-regulatory module, optionally filtered";
        $options = array(
            "fasta_seq" => "The type of sequence to download: 'both sequence and " .
                           "sequence with flank', 'sequence only', or 'flank only' " .
                           "(default is 'sequence only'). " .
                           "The options are both, seq, and  flank",
            "filename"  => "The optional name for the downloaded file",
            "format"    => "The download format. " .
                           "The available formats are: " .
                           implode(",", $this->_downloadFormats)
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Generate the file dump of a unique predicted cis-regulatory module specified in
    // the post data
    // --------------------------------------------------------------------------------
    public function predictedcrmAction(
        array $arguments,
        array $postData = null
    ) {
        // Verify that the specified download format is supported and instantiate
        // the exporter.
        $exporter = ExportFile::factory($arguments);
        // We can use the search action but we want to get a full list of results
        $arguments = array_merge(
            $arguments,
            array("view" => "full")
        );
        if ( $postData !== null ) {
            $arguments = array_merge(
                $arguments,
                array("redfly_id" => $postData["redfly_id"][0])
            );
        }
        $predictedCrmHandler = PredictedcrmHandler::factory();
        $predictedCrmResponse = $predictedCrmHandler->searchAction($arguments);
        if ( $predictedCrmResponse->numResults() > 0 ) {
            $entityName = $predictedCrmResponse->results()[0]["name"];
            $exporter->setPredictedCisRegulatoryModules($predictedCrmResponse->results());
        }

        return RestResponse::factory(
            true,
            "",
            array($exporter->getFile()),
            $exporter->getHtmlHeaders(
                $entityName,
                ""
            )
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "reporterconstruct" action
    // --------------------------------------------------------------------------------
    public function reporterconstructHelp()
    {
        $description = "Download a reporter construct, optionally filtered";
        $options = array(
            "fasta_seq"       => "The type of sequence to download: 'both sequence and " .
                                 "sequence with flank', 'sequence only', or 'flank only' " .
                                 "(default is 'sequence only'). " .
                                 "The options are both, seq, and flank",
            "filename"        => "The optional name for the downloaded file",
            "format"          => "The download format. " .
                                 "The available formats are: " .
                                 implode(",", $this->_downloadFormats),
            "with_assoc_tfbs" => "Set to true to download all RC/CRM entries that " .
                                 "have an associated TFBS. " .
                                 "Associated TFBS entries are included in the download"
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Generate the file dump of a unique reporter construct specified in the post data
    // --------------------------------------------------------------------------------
    public function reporterconstructAction(
        array $arguments,
        array $postData = null
    ) {
        // Verify that the specified download format is supported and instantiate
        // the exporter.
        $exporter = ExportFile::factory($arguments);
        // We can use the search action but we want to get a full list of results
        $arguments = array_merge(
            $arguments,
            array("cell_culture_only" => "0"),
            array("view"              => "full")
        );
        if ( $postData !== null ) {
            $arguments = array_merge(
                $arguments,
                array("redfly_id" => $postData["redfly_id"][0])
            );
        }
        // If the "with_assoc_tfbs" parameter is set and TRUE then return all RCs
        // that have associated TFBSs and also all of those TFBSs.
        // Otherwise simply return the requested RCs.
        $reporterConstructHandler = ReporterconstructHandler::factory();
        if ( array_key_exists("with_assoc_tfbs", $arguments) &&
            $arguments["with_assoc_tfbs"] ) {
            // Generate the list of all RCs that have associated TFBSs and
            // create a unique list of RCs and TFBSs that we will need to query.
            $db = DbService::factory();
            $reporterConstructList = $db->generateRcWithAssocTfbsMapping();
            $rcIdList = array();
            $tfbsIdList = array();
            foreach ( $reporterConstructList as $rcId => $info ) {
                $rcIdList[] = $rcId;
                $tfbsIdList = array_merge(
                    $tfbsIdList,
                    $info["tfbs_id_list"]
                );
            }
            $tfbsIdList = array_unique($tfbsIdList);
            $arguments["rc_id"] = $rcIdList;
            $reporterConstructResponse = $reporterConstructHandler->searchAction($arguments);
            if ( $reporterConstructResponse->numResults() > 0 ) {
                $results = $reporterConstructResponse->results();
                foreach ( $results as $index => &$result ) {
                    $result["associated_tfbs"] = implode(",", $reporterConstructList[$result["id"]]["tfbs_name_list"]);
                }
                $entityName = $results()[0]["name"];
                $exporter->setReporterConstructs($results);
                // Bring in the list of TFBSs as well.
                $arguments["tfbs_id"] = $tfbsIdList;
                $transcriptionFactorBindingSiteHandler = TranscriptionfactorbindingsiteHandler::factory();
                $transcriptionFactorBindingSiteResponse = $transcriptionFactorBindingSiteHandler->searchAction($arguments);
                if ( $transcriptionFactorBindingSiteResponse->numResults() > 0 ) {
                    $exporter->setTranscriptionFactorBindingSites($transcriptionFactorBindingSiteResponse->results());
                }
            }
        } else {
            $reporterConstructResponse = $reporterConstructHandler->searchAction($arguments);
            if ( $reporterConstructResponse->numResults() > 0 ) {
                $entityName = $reporterConstructResponse->results()[0]["name"];
                $exporter->setReporterConstructs($reporterConstructResponse->results());
            }
        }

        return RestResponse::factory(
            true,
            "",
            array($exporter->getFile()),
            $exporter->getHtmlHeaders(
                $entityName,
                ""
            )
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "reporterconstructCellCultureOnly" action
    // --------------------------------------------------------------------------------
    public function reporterconstructCellCultureOnlyHelp()
    {
        $description = "Download a list of reporter constructs for cell culture only, " .
                       "optionally filtered";
        $options = array(
            "filename" => "The optional name for the downloaded file",
            "format"   => "The download format. " .
                          "The available formats are: " .
                          implode(",", $this->_downloadFormats)
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Generate a file dump of the reporter construct(s) having only cell culture
    // specified in the post data
    // --------------------------------------------------------------------------------
    public function reporterconstructCellCultureOnlyAction(
        array $arguments,
        array $postData = null
    ) {
        // Verify that the specified download format is supported and instantiate
        // the exporter.
        $exporter = ExportFile::factory($arguments);
        // We can use the search action but we want to get a full list of results
        $arguments["view"] = "full";
        $arguments["cell_culture_only"] = "1";
        // If the "with_assoc_tfbs" parameter is set and TRUE, return all RCs that have associated TFBS
        // and also all of those TFBS. Otherwise simply return the requested RCs.
        $reporterConstructHandler = ReporterconstructHandler::factory();
        $reporterConstructResponse = $reporterConstructHandler->searchAction($arguments);
        if ( $reporterConstructResponse->numResults() > 0 ) {
            $exporter->setReporterConstructsCellCultureOnly($reporterConstructResponse->results());
        }

        return RestResponse::factory(
            true,
            "",
            array($exporter->getFile()),
            // Not open for the public, only for internal use by the REDfly team.
            $exporter->getHtmlHeaders(
                "",
                ""
            )
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "transcriptionfactorbindingsite" action
    // --------------------------------------------------------------------------------
    public function transcriptionfactorbindingsiteHelp()
    {
        $description = "Download a transcription factor binding site, optionally filtered";
        $options = array(
            "fasta_seq" => "The type of sequence to download: 'both sequence and " .
                           "sequence with flank', 'sequence only', or 'flank only' " .
                           "(default is 'sequence only'). " .
                           "The options are both, seq, and flank",
            "filename"  => "The optional name for the downloaded file",
            "format"    => "The download format. " .
                           "The available formats are: " .
                           implode(",", $this->_downloadFormats)
        );

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Generate the file dump of a unique transcription factor binding site specified
    // in the post data
    // --------------------------------------------------------------------------------
    public function transcriptionfactorbindingsiteAction(
        array $arguments,
        array $postData = null
    ) {
        $entityName = "";
        // Verify that the specified download format is supported and instantiate
        // the exporter.
        $exporter = ExportFile::factory($arguments);
        // We can use the search action but we want to get a full list of results
        $arguments = array_merge(
            $arguments,
            array("view" => "full")
        );
        if ( $postData !== null ) {
            $arguments = array_merge(
                $arguments,
                array("redfly_id" => $postData["redfly_id"][0])
            );
        }
        $transcriptionFactorBindingSiteHandler = TranscriptionfactorbindingsiteHandler::factory();
        $transcriptionFactorBindingSiteResponse = $transcriptionFactorBindingSiteHandler->searchAction($arguments);
        if ( $transcriptionFactorBindingSiteResponse->numResults() > 0 ) {
            $entityName = $transcriptionFactorBindingSiteResponse->results()[0]["name"];
            $exporter->setTranscriptionFactorBindingSites($transcriptionFactorBindingSiteResponse->results());
        }

        return RestResponse::factory(
            true,
            "",
            array($exporter->getFile()),
            $exporter->getHtmlHeaders(
                $entityName,
                ""
            )
        );
    }
}
