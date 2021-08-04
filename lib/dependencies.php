<?php
/**
 * This file contains the dependency injection container configuration. Pimple
 * (@see https://pimple.symfony.com) is used as the underlying dependency
 * injection container implementation.
 */
$container = $app->getContainer();
// config
$container["config"] = function () {
    return $GLOBALS["options"];
};
// logger
$container["logger"] = function () {
    $logger = new Monolog\Logger("redfly");
    $handler = new Monolog\Handler\FingersCrossedHandler(new Monolog\Handler\ErrorLogHandler());
    $formatter = new Monolog\Formatter\LineFormatter();
    $formatter->allowInlineLineBreaks();
    $formatter->ignoreEmptyContextAndExtra();
    $handler->setFormatter($formatter);
    $logger->pushHandler($handler);

    return $logger;
};
// data sources
$container["blat"] = function ($c) {
    return new CCR\REDfly\Service\External\BlatDataSource(
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->blat->url])
    );
};
$container["db"] = function ($c) {
    return Doctrine\DBAL\DriverManager::getConnection([
        "pdo" => $c->get("pdo")
    ]);
};
$container["easydb"] = function ($c) {
    return new \ParagonIE\EasyDB\EasyDB(
        $c->get("pdo"),
        "mysql"
    );
};
$container["entrez"] = function ($c) {
    return new CCR\REDfly\Service\External\EntrezDataSource(
        $c->get("easydb"),
        $c->get("latitude"),
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->entrez->endpoint]),
        $c->get("config")->entrez->api_key
    );
};
$container["latitude"] = function () {
    return new Latitude\QueryBuilder\QueryFactory("mysql");
};
$container["pdo"] = function ($c) {
    $host = $c->get("config")->database->host;
    $db = $c->get("config")->database->name;
    $user = $c->get("config")->database->user;
    $pass = $c->get("config")->database->password;
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=utf8",
        $host,
        $db
    );
    $opt = [
        \PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
        \PDO::MYSQL_ATTR_LOCAL_INFILE => true,
        \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false
    ];

    return new \PDO(
        $dsn,
        $user,
        $pass,
        $opt
    );
};
// mail
$container["mailer"] = function ($c) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->SMTPSecure = "tls";
    $mail->SMTPAuth = true;
    $mail->AuthType = "XOAUTH2";
    $mail->setOAuth(
        new PHPMailer\PHPMailer\OAuth([
            "provider"     => new League\OAuth2\Client\Provider\Google([
                "clientId"     => $c->get("config")->email->gmail_client_id,
                "clientSecret" => $c->get("config")->email->gmail_client_secret
            ]),
            "clientId"     => $c->get("config")->email->gmail_client_id,
            "clientSecret" => $c->get("config")->email->gmail_client_secret,
            "refreshToken" => $c->get("config")->email->gmail_refresh_token,
            "userName"     => $c->get("config")->email->gmail_address
        ])
    );
    $mail->setFrom(
        $c->get("config")->email->gmail_address,
        "REDfly Team"
    );
    $mail->CharSet = "utf-8";
    $mail->Encoding = "quoted-printable";
    
    return $mail;
};
// dispatcher
$container["dispatcher"] = function ($c) {
    return new CCR\REDfly\Service\Dispatcher\LoggedDispatcher(
        new CCR\REDfly\Service\Dispatcher\ValidatingDispatcher(
            new CCR\REDfly\Service\Dispatcher\SynchronizedDispatcher(
                new CCR\REDfly\Service\Dispatcher\TransactionalDispatcher(
                    new CCR\REDfly\Service\Dispatcher\Dispatcher(
                        new CCR\REDfly\Service\Dispatcher\ClassNameCallableResolver($c)
                    ),
                    $c->get("easydb")
                ),
                $c->get("easydb")
            )
        ),
        $c->get("logger")
    );
};
// middlewares
$container["auth-middleware"] = function ($c) {
    return new CCR\REDfly\Middleware\AuthMiddleware(
        $c->get("easydb"),
        $c->get("latitude"),
        $c->get("config")->general->site_auth_realm
    );
};
$container["debug-middleware"] = function ($c) {
    return new CCR\REDfly\Middleware\DebugMiddleware();
};
// command handlers
$container[CCR\REDfly\Admin\Command\ArchiveRecordsMarkedForDeletionHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\ArchiveRecordsMarkedForDeletionHandler($c->get("easydb"));
};
$container[CCR\REDfly\Admin\Command\ReleaseApprovedRecordsHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\ReleaseApprovedRecordsHandler($c->get("easydb"));
};
$container[CCR\REDfly\Admin\Command\UpdateAnatomicalExpressionsHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\UpdateAnatomicalExpressionsHandler(
        $c->get("easydb"),
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->termlookup->url])
    );
};
$container[CCR\REDfly\Admin\Command\UpdateBiologicalProcessesHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\UpdateBiologicalProcessesHandler(
        $c->get("easydb"),
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->termlookup->url])
    );
};
$container[CCR\REDfly\Admin\Command\UpdateCitationsHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\UpdateCitationsHandler($c->get("easydb"));
};
$container[CCR\REDfly\Audit\Command\UpdateCrmSegmentStatesHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Command\UpdateCrmSegmentStatesHandler($c->get("db"));
};
$container[CCR\REDfly\Admin\Command\UpdateDevelopmentalStagesHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\UpdateDevelopmentalStagesHandler(
        $c->get("easydb"),
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->termlookup->url])
    );
};
$container[CCR\REDfly\Admin\Command\UpdateFeaturesHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\UpdateFeaturesHandler(
        $c->get("easydb"),
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->termlookup->url])
    );
};
$container[CCR\REDfly\Admin\Command\UpdateGenesHandler::class] = function ($c) {
    return new CCR\REDfly\Admin\Command\UpdateGenesHandler(
        $c->get("easydb"),
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->termlookup->url])
    );
};
$container[CCR\REDfly\Audit\Command\UpdatePredictedCrmStatesHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Command\UpdatePredictedCrmStatesHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Command\UpdateRcStatesHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Command\UpdateRcStatesHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Command\UpdateTfbsStatesHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Command\UpdateTfbsStatesHandler($c->get("db"));
};
$container[CCR\REDfly\Import\Command\ImportDataHandler::class] = function ($c) {
    return new CCR\REDfly\Import\Command\ImportDataHandler(
        $c->get("blat"),
        new GuzzleHttp\Client(["base_uri" => $c->get("config")->general->site_base_url]),
        new CCR\REDfly\Import\Service\EntityExistenceDao(
            $c->get("easydb"),
            $c->get("latitude"),
            $c->get("entrez"),
            $c->get("config")->crm_segment->error_margin,
            $c->get("config")->predicted_crm->error_margin,
            $c->get("config")->rc->error_margin,
            $c->get("config")->tfbs->error_margin
        ),
        new CCR\REDfly\Import\Service\EntityIdDao(
            $c->get("easydb"),
            $c->get("latitude")
        ),
        new CCR\REDfly\Import\Service\EntityInformationDao(
            $c->get("easydb"),
            $c->get("latitude")
        )
    );
};
// query handlers
$container[CCR\REDfly\Audit\Query\ApprovedEntitiesAuthorsNotificationHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\ApprovedEntitiesAuthorsNotificationHandler(
        $c->get("easydb"),
        $c->get("latitude"),
        $c->get("mailer")
    );
};
$container[CCR\REDfly\Audit\Query\CrmSegmentSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\CrmSegmentSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\CrmSegmentNoTsSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\CrmSegmentNoTsSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\CrmSegmentTsSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\CrmSegmentTsSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\PredictedCrmSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\PredictedCrmSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\PredictedCrmNoTsSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\PredictedCrmNoTsSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\PredictedCrmTsSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\PredictedCrmTsSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\RcSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\RcSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\RcNoTsSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\RcNoTsSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\RcTsSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\RcTsSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Audit\Query\RejectedRecordsCuratorsNotificationHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\RejectedRecordsCuratorsNotificationHandler(
        $c->get("easydb"),
        $c->get("latitude"),
        $c->get("mailer")
    );
};
$container[CCR\REDfly\Audit\Query\TfbsSearchHandler::class] = function ($c) {
    return new CCR\REDfly\Audit\Query\TfbsSearchHandler($c->get("db"));
};
$container[CCR\REDfly\Datasource\Blat\Query\GetAlignmentListHandler::class] = function ($c) {
    return new CCR\REDfly\Datasource\Blat\Query\GetAlignmentListHandler(
        new CCR\REDfly\Datasource\Blat\Service\AlignmentMatcher($c->get("blat")),
        new CCR\REDfly\Datasource\Blat\Service\ChromosomeIdDao(
            $c->get("easydb"),
            $c->get("latitude")
        )
    );
};
$container[CCR\REDfly\Download\Query\BatchDownloadCRMsHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadCRMsHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadCRMStagingDataHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadCRMStagingDataHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadCRMSegmentsHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadCRMSegmentsHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadCRMSegmentStagingDataHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadCRMSegmentStagingDataHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadPredictedCRMsHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadPredictedCRMsHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadPredictedCRMStagingDataHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadPredictedCRMStagingDataHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadReporterConstructsHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadReporterConstructsHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadReporterConstructStagingDataHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadReporterConstructStagingDataHandler($c->get("db"));
};
$container[CCR\REDfly\Download\Query\BatchDownloadTranscriptionFactorBindingSitesHandler::class] = function ($c) {
    return new CCR\REDfly\Download\Query\BatchDownloadTranscriptionFactorBindingSitesHandler($c->get("db"));
};
$container[CCR\REDfly\Dynamic\Query\AnatomicalExpressionListHandler::class] = function ($c) {
    return new CCR\REDfly\Dynamic\Query\AnatomicalExpressionListHandler($c->get("db"));
};
$container[CCR\REDfly\Dynamic\Query\BiologicalProcessListHandler::class] = function ($c) {
    return new CCR\REDfly\Dynamic\Query\BiologicalProcessListHandler($c->get("db"));
};
$container[CCR\REDfly\Dynamic\Query\ChromosomeListHandler::class] = function ($c) {
    return new CCR\REDfly\Dynamic\Query\ChromosomeListHandler($c->get("db"));
};
$container[CCR\REDfly\Dynamic\Query\CuratorListHandler::class] = function ($c) {
    return new CCR\REDfly\Dynamic\Query\CuratorListHandler($c->get("db"));
};
$container[CCR\REDfly\Dynamic\Query\DevelopmentalStageListHandler::class] = function ($c) {
    return new CCR\REDfly\Dynamic\Query\DevelopmentalStageListHandler($c->get("db"));
};
$container[CCR\REDfly\Dynamic\Query\GeneListHandler::class] = function ($c) {
    return new CCR\REDfly\Dynamic\Query\GeneListHandler($c->get("db"));
};
$container[CCR\REDfly\Dynamic\Query\SpeciesListHandler::class] = function ($c) {
    return new CCR\REDfly\Dynamic\Query\SpeciesListHandler($c->get("db"));
};
$container[CCR\REDfly\Import\Query\ValidateImportFilesHandler::class] = function ($c) {
    return new CCR\REDfly\Import\Query\ValidateImportFilesHandler(
        new CCR\REDfly\Import\Service\FieldChecker(),
        new CCR\REDfly\Import\Service\AttributeTsvFileValidatorFactory(
            new CCR\REDfly\Import\Service\FluentAttributeRecordValidator(
                new CCR\REDfly\Import\Service\EntityExistenceDao(
                    $c->get("easydb"),
                    $c->get("latitude"),
                    $c->get("entrez"),
                    $c->get("config")->crm_segment->error_margin,
                    $c->get("config")->predicted_crm->error_margin,
                    $c->get("config")->rc->error_margin,
                    $c->get("config")->tfbs->error_margin
                )
            )
        ),
        new CCR\REDfly\Import\Service\FastaFileValidator(),
        new CCR\REDfly\Import\Service\AnatomicalExpressionTsvFileValidatorFactory(
            new CCR\REDfly\Import\Service\FluentAnatomicalExpressionRecordValidator(
                new CCR\REDfly\Import\Service\EntityExistenceDao(
                    $c->get("easydb"),
                    $c->get("latitude"),
                    $c->get("entrez"),
                    $c->get("config")->crm_segment->error_margin,
                    $c->get("config")->predicted_crm->error_margin,
                    $c->get("config")->rc->error_margin,
                    $c->get("config")->tfbs->error_margin
                ),
                new CCR\REDfly\Import\Service\EntityIdDao(
                    $c->get("easydb"),
                    $c->get("latitude")
                ),
                new CCR\REDfly\Import\Service\EntityInformationDao(
                    $c->get("easydb"),
                    $c->get("latitude")
                )
            )
        ),
        new CCR\REDfly\Import\Service\UniqueRowChecker(
            new CCR\REDfly\Import\Service\EntityInformationDao(
                $c->get("easydb"),
                $c->get("latitude")
            )
        ),
        new CCR\REDfly\Import\Service\CrossNameChecker(),
        new CCR\REDfly\Import\Service\CrossReferenceChecker(
            new CCR\REDfly\Import\Service\EntityInformationDao(
                $c->get("easydb"),
                $c->get("latitude")
            ),
            $c->get("blat")
        )
    );
};
