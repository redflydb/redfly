<?php
/**
 * This file contains the routes for the application and uses the Slim Framework
 * router library which version is 3.X
 */
$c = $app->getContainer();
// -----------------------------------------------------------------------------
// admin
// -----------------------------------------------------------------------------
$app->group("/admin", function () {
    $this->get("/delete", function ($request, $response, $args) {
        $command = CCR\REDfly\Admin\Command\ArchiveRecordsMarkedForDeletion::fromRequest($request);
        $this->get("dispatcher")->send($command);
        return $response;
    });
    $this->get("/login", function ($request, $response) {
        return $response->withJson(CCR\REDfly\Service\Message\QueryResult::fromArray([]));
    });
    $this->get("/release", function ($request, $response, $args) {
        $command = CCR\REDfly\Admin\Command\ReleaseApprovedRecords::fromRequest($request);
        $this->get("dispatcher")->send($command);
        return $response;
    });
    $this->group("/update", function () {
        $this->get("/biological_processes", function ($request, $response, $args) {
            $command = CCR\REDfly\Admin\Command\UpdateBiologicalProcesses::fromRequest($request);
            $this->get("dispatcher")->send($command);
            return $response;
        });
        $this->get("/citations", function ($request, $response, $args) {
            $command = CCR\REDfly\Admin\Command\UpdateCitations::fromRequest($request);
            $this->get("dispatcher")->send($command);
            return $response;
        });
        $this->get("/developmental_stages", function ($request, $response, $args) {
            $command = CCR\REDfly\Admin\Command\UpdateDevelopmentalStages::fromRequest($request);
            $this->get("dispatcher")->send($command);
            return $response;
        });
        $this->get("/anatomical_expressions", function ($request, $response, $args) {
            $command = CCR\REDfly\Admin\Command\UpdateAnatomicalExpressions::fromRequest($request);
            $this->get("dispatcher")->send($command);
            return $response;
        });
        $this->get("/features", function ($request, $response, $args) {
            $command = CCR\REDfly\Admin\Command\UpdateFeatures::fromRequest($request);
            $this->get("dispatcher")->send($command);
            return $response;
        });
        $this->get("/genes", function ($request, $response, $args) {
            $command = CCR\REDfly\Admin\Command\UpdateGenes::fromRequest($request);
            $this->get("dispatcher")->send($command);
            return $response;
        });
    });
})->add($container->get("auth-middleware"))->add($container->get("debug-middleware"));
// -----------------------------------------------------------------------------
// audit
// -----------------------------------------------------------------------------
$app->group("/audit", function () {
    $this->get("/crm_segment", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\CrmSegmentSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->post("/crm_segment", function ($request, $response, $args) {
        $command = CCR\REDfly\Audit\Command\UpdateCrmSegmentStates::fromRequest($request);
        $this->get("dispatcher")->send($command);
        return $response;
    });
    $this->get("/crm_segment_no_ts", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\CrmSegmentNoTsSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/crm_segment_ts", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\CrmSegmentTsSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->group("/notify", function () {
        $this->post("/authors", function ($request, $response, $args) {
            $query = CCR\REDfly\Audit\Query\ApprovedEntitiesAuthorsNotification::fromRequest($request);
            $results = $this->get("dispatcher")->request($query);
            return $response->withJson($results);
        });
        $this->post("/rejections", function ($request, $response, $args) {
            $query = CCR\REDfly\Audit\Query\RejectedRecordsCuratorsNotification::fromRequest($request);
            $results = $this->get("dispatcher")->request($query);
            return $response->withJson($results);
        });
    });
    $this->get("/predicted_crm", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\PredictedCrmSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->post("/predicted_crm", function ($request, $response, $args) {
        $command = CCR\REDfly\Audit\Command\UpdatePredictedCrmStates::fromRequest($request);
        $this->get("dispatcher")->send($command);
        return $response;
    });
    $this->get("/predicted_crm_no_ts", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\PredictedCrmNoTsSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/predicted_crm_ts", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\PredictedCrmTsSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/rc", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\RcSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->post("/rc", function ($request, $response, $args) {
        $command = CCR\REDfly\Audit\Command\UpdateRcStates::fromRequest($request);
        $this->get("dispatcher")->send($command);
        return $response;
    });
    $this->get("/rc_no_ts", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\RcNoTsSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/rc_ts", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\RcTsSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/tfbs", function ($request, $response, $args) {
        $query = CCR\REDfly\Audit\Query\TfbsSearch::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->post("/tfbs", function ($request, $response, $args) {
        $command = CCR\REDfly\Audit\Command\UpdateTfbsStates::fromRequest($request);
        $this->get("dispatcher")->send($command);
        return $response;
    });
})->add($container->get("auth-middleware"));
// -----------------------------------------------------------------------------
// batch
// -----------------------------------------------------------------------------
$app->group("/batch", function () {
    $this->post("/import", function ($request, $response, $args) {
        $command = CCR\REDfly\Import\Command\ImportData::fromRequest($request);
        $this->get("dispatcher")->send($command);
        // Really?
        return $response->withJson(["success" => true]);
    });
    $this->post("/validate", function ($request, $response, $args) {
        $query = CCR\REDfly\Import\Query\ValidateImportFiles::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
})->add($container->get("auth-middleware"))->add($container->get("debug-middleware"));
// -----------------------------------------------------------------------------
// datasource
// -----------------------------------------------------------------------------
$app->group("/datasource", function () {
    $this->group("/blat", function () {
        $this->post("/search", function ($request, $response, $args) {
            $query = CCR\REDfly\Datasource\Blat\Query\GetAlignmentList::fromRequest($request);
            $results = $this->get("dispatcher")->request($query);
            return $response->withJson($results);
        });
    });
})->add($container->get("auth-middleware"))->add($container->get("debug-middleware"));
// -----------------------------------------------------------------------------
// dynamic
// -----------------------------------------------------------------------------
$app->group("/dynamic", function () {
    $this->get("/anatomical_expression", function ($request, $response, $args) {
        $query = CCR\REDfly\Dynamic\Query\AnatomicalExpressionList::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/biological_process", function ($request, $response, $args) {
        $query = CCR\REDfly\Dynamic\Query\BiologicalProcessList::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/chromosome", function ($request, $response, $args) {
        $query = CCR\REDfly\Dynamic\Query\ChromosomeList::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/curator", function ($request, $response, $args) {
        $query = CCR\REDfly\Dynamic\Query\CuratorList::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/developmental_stage", function ($request, $response, $args) {
        $query = CCR\REDfly\Dynamic\Query\DevelopmentalStageList::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/gene", function ($request, $response, $args) {
        $query = CCR\REDfly\Dynamic\Query\GeneList::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
    $this->get("/species", function ($request, $response, $args) {
        $query = CCR\REDfly\Dynamic\Query\SpeciesList::fromRequest($request);
        $results = $this->get("dispatcher")->request($query);
        return $response->withJson($results);
    });
});
// -----------------------------------------------------------------------------
// file download
// -----------------------------------------------------------------------------
$app->group("/file/{format:BED|CSV|FASTA|GBrowse|GFF3}", function () {
    $this->post("/crm", function ($request, $response, $args) {
        $primaryQuery = new CCR\REDfly\Download\Query\BatchDownloadCRMs();
        $params = $request->getQueryParams();
        $primaryQuery->speciesScientificName = isset($params["species_scientific_name"])
            ? $params["species_scientific_name"]
            : null;
        $primaryQuery->bedFileType = isset($params["bed_file_type"])
            ? $params["bed_file_type"]
            : null;
        $primaryQuery->bedTrackName = isset($params["bed_track_name"])
            ? $params["bed_track_name"]
            : null;
        $primaryQuery->bedTrackDescription = isset($params["bed_track_description"])
            ? $params["bed_track_description"]
            : null;
        $primaryQuery->fastaInclude = isset($params["fasta_seq"])
            ? $params["fasta_seq"]
            : null;
        $result = $this->get("dispatcher")->request($primaryQuery);
        $secondaryQuery = new CCR\REDfly\Download\Query\BatchDownloadReporterConstructStagingData();
        $stagingData = $this->get("dispatcher")->request($secondaryQuery);
        $redflyVersion = $this->get("config")->general->redfly_version;
        $encoder = (new CCR\REDfly\Download\Service\EncoderFactory($redflyVersion))->create($args["format"]);
        $response->getBody()->write($encoder->encode(
            $result->getResults(),
            $primaryQuery,
            $stagingData->getResults()
        ));
        if ( $args["format"] !== "GBrowse" ) {
            $fileType = strtolower($args["format"]);
        } else {
            $fileType = "fff";
        }
        return $response
            ->withHeader("Content-Type", "application/octet-stream; charset=utf-8")
            ->withHeader("Content-Disposition", "attachment; filename=all_" .
                str_replace(" ", "_", lcfirst($primaryQuery->speciesScientificName)) .
                "_crms." . $fileType);
    });
    $this->post("/crm_segment", function ($request, $response, $args) {
        $primaryQuery = new CCR\REDfly\Download\Query\BatchDownloadCRMSegments();
        $params = $request->getQueryParams();
        $primaryQuery->speciesScientificName = isset($params["species_scientific_name"])
            ? $params["species_scientific_name"]
            : null;
        $primaryQuery->bedFileType = isset($params["bed_file_type"])
            ? $params["bed_file_type"]
            : null;
        $primaryQuery->bedTrackName = isset($params["bed_track_name"])
            ? $params["bed_track_name"]
            : null;
        $primaryQuery->bedTrackDescription = isset($params["bed_track_description"])
            ? $params["bed_track_description"]
            : null;
        $primaryQuery->fastaInclude = isset($params["fasta_seq"])
            ? $params["fasta_seq"]
            : null;
        $result = $this->get("dispatcher")->request($primaryQuery);
        $secondaryQuery = new CCR\REDfly\Download\Query\BatchDownloadCRMSegmentStagingData();
        $stagingData = $this->get("dispatcher")->request($secondaryQuery);
        $redflyVersion = $this->get("config")->general->redfly_version;
        $encoder = (new CCR\REDfly\Download\Service\EncoderFactory($redflyVersion))->create($args["format"]);
        $response->getBody()->write($encoder->encode(
            $result->getResults(),
            $primaryQuery,
            $stagingData->getResults()
        ));
        if ( $args["format"] !== "GBrowse" ) {
            $fileType = strtolower($args["format"]);
        } else {
            $fileType = "fff";
        }
        return $response
            ->withHeader("Content-Type", "application/octet-stream; charset=utf-8")
            ->withHeader("Content-Disposition", "attachment; filename=all_" .
                str_replace(" ", "_", lcfirst($primaryQuery->speciesScientificName)) .
                "_crm_segments." . $fileType);
    });
    $this->post("/predicted_crm", function ($request, $response, $args) {
        $primaryQuery = new CCR\REDfly\Download\Query\BatchDownloadPredictedCRMs();
        $params = $request->getQueryParams();
        $primaryQuery->speciesScientificName = isset($params["species_scientific_name"])
            ? $params["species_scientific_name"]
            : null;
        $primaryQuery->bedFileType = isset($params["bed_file_type"])
            ? $params["bed_file_type"]
            : null;
        $primaryQuery->bedTrackName = isset($params["bed_track_name"])
            ? $params["bed_track_name"]
            : null;
        $primaryQuery->bedTrackDescription = isset($params["bed_track_description"])
            ? $params["bed_track_description"]
            : null;
        $primaryQuery->fastaInclude = isset($params["fasta_seq"])
            ? $params["fasta_seq"]
            : null;
        $result = $this->get("dispatcher")->request($primaryQuery);
        $secondaryQuery = new CCR\REDfly\Download\Query\BatchDownloadPredictedCRMStagingData();
        $stagingData = $this->get("dispatcher")->request($secondaryQuery);
        $redflyVersion = $this->get("config")->general->redfly_version;
        $encoder = (new CCR\REDfly\Download\Service\EncoderFactory($redflyVersion))->create($args["format"]);
        $response->getBody()->write($encoder->encode(
            $result->getResults(),
            $primaryQuery,
            $stagingData->getResults()
        ));
        if ( $args["format"] !== "GBrowse" ) {
            $fileType = strtolower($args["format"]);
        } else {
            $fileType = "fff";
        }
        return $response
            ->withHeader("Content-Type", "application/octet-stream; charset=utf-8")
            ->withHeader("Content-Disposition", "attachment; filename=all_" .
                str_replace(" ", "_", lcfirst($primaryQuery->speciesScientificName)) .
                "_predicted_crms." . $fileType);
    });
    $this->post("/rc", function ($request, $response, $args) {
        $primaryQuery = new CCR\REDfly\Download\Query\BatchDownloadReporterConstructs();
        $params = $request->getQueryParams();
        $primaryQuery->speciesScientificName = isset($params["species_scientific_name"])
            ? $params["species_scientific_name"]
            : null;
        $primaryQuery->bedFileType = isset($params["bed_file_type"])
            ? $params["bed_file_type"]
            : null;
        $primaryQuery->bedTrackName = isset($params["bed_track_name"])
            ? $params["bed_track_name"]
            : null;
        $primaryQuery->bedTrackDescription = isset($params["bed_track_description"])
            ? $params["bed_track_description"]
            : null;
        $primaryQuery->fastaInclude = isset($params["fasta_seq"])
            ? $params["fasta_seq"]
            : null;
        $result = $this->get("dispatcher")->request($primaryQuery);
        $secondaryQuery = new CCR\REDfly\Download\Query\BatchDownloadReporterConstructStagingData();
        $stagingData = $this->get("dispatcher")->request($secondaryQuery);
        $redflyVersion = $this->get("config")->general->redfly_version;
        $encoder = (new CCR\REDfly\Download\Service\EncoderFactory($redflyVersion))->create($args["format"]);
        $response->getBody()->write($encoder->encode(
            $result->getResults(),
            $primaryQuery,
            $stagingData->getResults()
        ));
        if ( $args["format"] !== "GBrowse" ) {
            $fileType = strtolower($args["format"]);
        } else {
            $fileType = "fff";
        }
        return $response
            ->withHeader("Content-Type", "application/octet-stream; charset=utf-8")
            ->withHeader("Content-Disposition", "attachment; filename=all_" .
                str_replace(" ", "_", lcfirst($primaryQuery->speciesScientificName)) .
                "_rcs." . $fileType);
    });
    $this->post("/tfbs", function ($request, $response, $args) {
        $primaryQuery = new CCR\REDfly\Download\Query\BatchDownloadTranscriptionFactorBindingSites();
        $params = $request->getQueryParams();
        $primaryQuery->speciesScientificName = isset($params["species_scientific_name"])
            ? $params["species_scientific_name"]
            : null;
        $primaryQuery->bedFileType = isset($params["bed_file_type"])
            ? $params["bed_file_type"]
            : null;
        $primaryQuery->bedTrackName = isset($params["bed_track_name"])
            ? $params["bed_track_name"]
            : null;
        $primaryQuery->bedTrackDescription = isset($params["bed_track_description"])
            ? $params["bed_track_description"]
            : null;
        $primaryQuery->fastaInclude = isset($params["fasta_seq"])
            ? $params["fasta_seq"]
            : null;
        $result = $this->get("dispatcher")->request($primaryQuery);
        $redflyVersion = $this->get("config")->general->redfly_version;
        $encoder = (new CCR\REDfly\Download\Service\EncoderFactory($redflyVersion))->create($args["format"]);
        $response->getBody()->write($encoder->encode(
            $result->getResults(),
            $primaryQuery,
            array()
        ));
        if ( $args["format"] !== "GBrowse" ) {
            $fileType = strtolower($args["format"]);
        } else {
            $fileType = "fff";
        }
        return $response
            ->withHeader("Content-Type", "application/octet-stream; charset=utf-8")
            ->withHeader("Content-Disposition", "attachment; filename=all_" .
                str_replace(" ", "_", lcfirst($primaryQuery->speciesScientificName)) .
                "_tfbss." . $fileType);
    });
});
// -----------------------------------------------------------------------------
// termlookup
// Reverse proxy to the term lookup service
// -----------------------------------------------------------------------------
$app->get("/termlookup/{rest:.*}", function ($request, $response, $args) use ($c) {
    $guzzle = new GuzzleHttp\Client(["base_uri" => $c->get("config")->termlookup->url]);
    $response->getBody()->write($guzzle->get($args["rest"])->getBody());
    return $response;
});
