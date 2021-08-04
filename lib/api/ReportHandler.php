<?php
class ReportHandler
{
    // --------------------------------------------------------------------------------
    // Roles for authenticated users
    // --------------------------------------------------------------------------------
    const ROLE_admin = "admin";
    const ROLE_curator = "curator";
    // --------------------------------------------------------------------------------
    // Factory design pattern
    // --------------------------------------------------------------------------------
    public static function factory()
    {
        return new ReportHandler();
    }
    private function __construct()
    {
    }
    // --------------------------------------------------------------------------------
    // Get the path of the report directory
    // The returned path will always include a trailing slash.
    // @returns string The absolute path to the directory containing
    //   reports
    // --------------------------------------------------------------------------------
    private function getReportDir()
    {
        $dir = $GLOBALS["options"]->general->report_dir;
        if ( substr($dir, 0, 1) !== "/" ) {
            $dir = $GLOBALS["options"]->general->site_base_dir . "/" . $dir;
        }
        if ( substr($dir, -1) !== "/" ) {
            $dir = $dir . "/";
        }
        if ( ! is_dir($dir) ) {
            throw new Exception("Report directory \"" . $dir . "\" not found");
        }

        return $dir;
    }
    // --------------------------------------------------------------------------------
    // Return help for the "list" action
    // --------------------------------------------------------------------------------
    public function listHelp()
    {
        $description = "Return a list of all reports. The list will contain the " .
            "following fields: (name, file, type, date, time)";

        return RestResponse::factory(
            true,
            $description
        );
    }
    // --------------------------------------------------------------------------------
    // List all the reports
    // --------------------------------------------------------------------------------
    public function listAction(array $arguments)
    {
        try {
            Auth::authorize(array("admin"));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403
            );

            return RestResponse::factory(
                false,
                $e->getMessage(),
                array(),
                array(),
                $httpResponseCode
            );
        }
        $dir = $this->getReportDir();
        $handle = opendir($dir);
        if ( $handle === false ) {
            throw new Exception("Failed to open directory \"" . $dir . "\"");
        }
        $files = array();
        while ( $file = readdir($handle) ) {
            if ( substr($file, 0, 1) === "." ) {
                continue;
            }
            try {
                $files[] = $this->parseFilename($file);
            } catch ( Exception $e ) {
                // Just ignore the file
            }
        }
        sort($files);
        closedir($handle);

        return RestResponse::factory(
            true,
            null,
            $files
        );
    }
    // --------------------------------------------------------------------------------
    // Return help for the "download" action
    // --------------------------------------------------------------------------------
    public function downloadHelp()
    {
        $description = "Return a list of all reports";
        $options = array("file" => "The name of the file to download");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Download a report
    //
    // This should be used with the "raw" format
    // --------------------------------------------------------------------------------
    public function downloadAction(array $arguments)
    {
        try {
            Auth::authorize(array("admin"));
        } catch ( Exception $e ) {
            $httpResponseCode = ( Auth::getUser() === null
                ? 401
                : 403
            );

            return RestResponse::factory(
                false,
                $e->getMessage(),
                array(),
                array(),
                $httpResponseCode
            );
        }
        $dir = $this->getReportDir();
        $file = $arguments["file"];
        $path = $dir . $file;
        if ( ! is_file($path) ) {
            throw new Exception("File not found: \"" . $path . "\"");
        }
        $data = file_get_contents($path);
        $headers = array();
        if ( substr($file, -4) === ".csv" ) {
            $headers[] = array("Content-type", "text/csv");
        }
        $headers[] = array(
            "Content-disposition",
            "attachment; filename=" . $file
        );

        return RestResponse::factory(
            true,
            null,
            array($data),
            $headers
        );
    }
    // --------------------------------------------------------------------------------
    // Parse a report filename
    // @param string $name The name of a report file
    // @returns array
    // --------------------------------------------------------------------------------
    private function parseFilename($name)
    {
        $pattern = "/^(\w+)-(\d{4}-\d\d-\d\d)_(\d\d-\d\d-\d\d)\.(\w+)$/";
        if ( preg_match($pattern, $name, $matches) ) {
            return array(
                "name" => $name,
                "file" => $name,
                "type" => $matches[1],
                "date" => $matches[2],
                "time" => str_replace("-", ":", $matches[3]),
                "ext"  => $matches[4]
            );
        } else {
            throw new Exception("Unrecognized filename format: \"" . $name . "\"");
        }
    }
}
