<?php
require_once(__DIR__ . "/../../lib/bootstrap.php");
if ( ($GLOBALS["options"]->google_applications !== null) &&
     ($GLOBALS["options"]->google_applications->oauth2_credentials_file !== null) &&
    ($GLOBALS["options"]->google_applications->analytics_api_3_view_id !== null) ) {
    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . "/../../config/" . $GLOBALS["options"]->google_applications->oauth2_credentials_file);
    $client->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
    // If the user has already authorized this app then get an access token
    // else redirect to ask the user to authorize access to Google Analytics.
    if ( isset($_SESSION["access_token"]) &&
        $_SESSION["access_token"] ) {
        // Set the access token on the client.
        $client->setAccessToken($_SESSION["access_token"]);
        // Create an authorized analytics service object.
        $analytics = new Google_Service_AnalyticsReporting($client);
        // Call the Analytics Reporting API V4.
        $response = getReport($analytics);
        // Print the response.
        printResults($response);
    } else {
        $redirect_uri = "http://" . $_SERVER["HTTP_HOST"] . "/admin/oauth2_callback.php";
        header("Location: " . filter_var(
            $redirect_uri,
            FILTER_SANITIZE_URL
        ));
    } 
} else {
    print("No Google Analytics credentials from the configuration file");
}
/**
 * Queries the Analytics Reporting API V4.
 * @param service An authorized Analytics Reporting API V4 service object.
 * @return The Analytics Reporting API V4 response.
 */
function getReport($analytics) {
    $VIEW_ID = $GLOBALS["options"]->google_applications->analytics_api_3_view_id;
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate("2021-01-01");
    $dateRange->setEndDate("2021-12-31");
    $users_metric = new Google_Service_AnalyticsReporting_Metric();    
    $users_metric->setExpression("ga:users");
    $users_metric->setAlias("Users");
    $pageviews_metric = new Google_Service_AnalyticsReporting_Metric();
    $pageviews_metric->setExpression("ga:pageviews");
    $pageviews_metric->setAlias("Page Views");    
    $country_dimension = new Google_Service_AnalyticsReporting_Dimension();
    $country_dimension->setName("ga:month");
    $pivot = new Google_Service_AnalyticsReporting_Pivot();
    $pivot->setDimensions(array($country_dimension));
    $pivot->setMaxGroupCount(1);
    $pivot->setStartGroup(0);
    $pivot->setMetrics(array(
        $users_metric,
        $pageviews_metric
    ));
    $requestReport = new Google_Service_AnalyticsReporting_ReportRequest();
    $requestReport->setViewId($VIEW_ID);
    $requestReport->setDateRanges($dateRange);
    $requestReport->setMetrics(array(
        $users_metric,
        $pageviews_metric
    ));
    $requestReport->setDimensions(array($country_dimension));
    $requestReport->setPivots(array($pivot));
    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests( array( $requestReport) );
    return $analytics->reports->batchGet( $body );
}
/**
 * Parses and prints the Analytics Reporting API V4 response.
 * @param An Analytics Reporting API V4 response.
 */
function printResults($reports) {
    for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
        $report = $reports[$reportIndex];
        $header = $report->getColumnHeader();
        $dimensionHeaders = $header->getDimensions();
        $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
        $rows = $report->getData()->getRows();
        print("<html><head><style>");        
        print("table, th, td { border: 1px solid black; }");
        print("th, td { padding: 15px; }");
        print("</style></head><body>");
        print("<table style=\"width:100%\">");
        if ( $rows !== null ) {
            for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[$rowIndex];
                $metrics = $row->getMetrics();
                if ( $metrics !== null ) {
                    print("<tr><th></th>");
                    for ($metricHeaderIndex = 0; $metricHeaderIndex < count($metricHeaders); $metricHeaderIndex++) {
                        print("<th>" . $metricHeaders[$metricHeaderIndex]->getName() . "</th>");
                    }
                    print("<tr>");
                }
                $dimensions = $row->getDimensions();
                if ( $dimensions !== null ) {
                    for ($dimensionHeaderIndex = 0; $dimensionHeaderIndex < count($dimensionHeaders); $dimensionHeaderIndex++) {
                        $monthName = "";
                        switch($dimensions[$dimensionHeaderIndex]) {
                            case "01":
                                $monthName = "January";
                                break;
                            case "02":
                                $monthName = "February";
                                break;
                            case "03":
                                $monthName = "March";
                                break;
                            case "04":
                                $monthName = "April";
                                break;
                            case "05":
                                $monthName = "May";
                                break;
                            case "06":
                                $monthName = "June";
                                break;
                            case "07":
                                $monthName = "July";
                                break;
                            case "08":
                                $monthName = "August";
                                break;
                            case "09":
                                $monthName = "September";
                                break;
                            case "10":
                                $monthName = "October";
                                break;
                            case "11":
                                $monthName = "November";
                                break;
                            case "12":
                                $monthName = "December";
                                break;
                            default:
                                $monthName = "Unknown";
                        }
                        print("<tr><th>" . $monthName . "</th>");
                        for ($metricIndex = 0; $metricIndex < count($metrics); $metricIndex++) {
                            $values = $metrics[$metricIndex]->getValues();
                            for ($valueIndex = 0; $valueIndex < count($values); $valueIndex++) {
                                print("<th>" . $values[$valueIndex] . "</th>");
                            }
                        }
                        print("</tr>");
                    }
                }
            }
        }
        print("</table>");
    }
}
