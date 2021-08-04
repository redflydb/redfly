<?php
// --------------------------------------------------------------------------------
// General machinery for generating a file for export.
// The extending class implements the details of the desired file format in
// generateFileHeader(), generateFileBody(), and generateFileFooter().
// --------------------------------------------------------------------------------
class ExportFile implements iExportFile
{
    // Content-Type describing the generated file
    protected $_mimeType = null;
    // File extension, NULL for no extension
    protected $_fileExtension = null;
    // Optional, used when generating the file headers
    protected $_filename = "";
    // Array of cis-regulatory module segments to be included in the output
    protected $_cisRegulatoryModuleSegmentList = array();
    // Array of predicted cis-regulatory modules to be included in the output
    protected $_predictedCisRegulatoryModuleList = array();
    // Array of reporter constructs to be included in the output
    protected $_reporterConstructList = array();
    // Array of reporter constructs for culture cell only data to be included in the output
    protected $_reporterConstructCellCultureOnlyList = array();
    // Array of transcription factor binding sites to be included in the output
    protected $_transcriptionFactorBindingSiteList = array();
    // Generated header string and length
    protected $_header = null;
    protected $_headerLength = 0;
    // Generated body string and length
    protected $_body = null;
    protected $_bodyLength = 0;
    // Generated footer string and length
    protected $_footer = null;
    protected $_footerLength = 0;
    public static function factory(array $options = null)
    {
        $format = null;
        // Extract any options relevant to this class
        foreach ( $options ?? [] as $name => $value ) {
            if ( ($value !== false) &&
              (($value === null) || ($value === "")) ) {
                continue;
            }
            switch ( $name ) {
                case "format":
                    $format = $value;
                    break;
                default:
                    break;
            }
        }
        // Verify required options
        if ( $format === null ) {
            throw new Exception("Download file format not provided");
        }
        // Instantiate the correct exporter
        $format = ucfirst(strtolower($format));
        $exporterClassName = "ExportFile_" . $format;
        if ( ! class_exists($exporterClassName) ) {
            throw new Exception("Unsupported export format: '" . $format . "'");
        }
        $exporter = ($exporterClassName)::factory($options);

        return $exporter;
    }
    protected function __construct(
        $mimeType,
        $fileExt,
        array $options = array()
    ) {
        $this->parseOptions($options);
        $this->_mimeType = $mimeType;
        $this->_fileExtension = $fileExt;
    }
    // --------------------------------------------------------------------------------
    // Parse the available options and extract any that are specific to this class.
    // @param $options An array containing the options where the key is the option
    //   name.
    // --------------------------------------------------------------------------------
    private function parseOptions(array $options = null)
    {
        foreach ( $options ?? [] as $name => $value ) {
            if ( ($value !== false)
              && (($value === null) || ($value === "")) ) {
                continue;
            }
            switch ( $name ) {
                case "filename":
                    $this->_filename = $value;
                    break;
                default:
                    break;
            }
        }
    }
    // --------------------------------------------------------------------------------
    // Return general help for file exports. This function is meant to be called
    // from classes that extend this class and describes general or common
    // options. Children should modify the results accordingly.
    // --------------------------------------------------------------------------------
    public function help()
    {
        $description = "Export REDfly data in a file";
        $options = array("filename" => "Optional filename to be used by the download");

        return RestResponse::factory(
            true,
            $description,
            $options
        );
    }
    // --------------------------------------------------------------------------------
    // Set the filename used when generating the download header. The extension
    // will be appended automatically.
    // @param $filename The base of the filename (does not include the extension.)
    // --------------------------------------------------------------------------------
    public function setFilename($filename)
    {
        $this->_filename = $filename;
    }
    // --------------------------------------------------------------------------------
    // Set the cis-regulatory module segments to download
    // @param $list An array containing the results of a cis-regulatory module segment
    // search
    // --------------------------------------------------------------------------------
    public function setCisRegulatoryModuleSegments(array $list)
    {
        $this->_cisRegulatoryModuleSegmentList = $list;
    }
    // --------------------------------------------------------------------------------
    // Set the predicted cis-regulatory modules to download
    // @param $list An array containing the results of a predicted cis-regulatory module
    // search
    // --------------------------------------------------------------------------------
    public function setPredictedCisRegulatoryModules(array $list)
    {
        $this->_predictedCisRegulatoryModuleList = $list;
    }
    // --------------------------------------------------------------------------------
    // Set the reporter constructs to download
    // @param $list An array containing the results of a reporter construct search
    // --------------------------------------------------------------------------------
    public function setReporterConstructs(array $list)
    {
        $this->_reporterConstructList = $list;
    }
    // --------------------------------------------------------------------------------
    // Set the reporter constructs for culture cell only data to download
    // @param $list An array containing the results of a reporter construct search
    // --------------------------------------------------------------------------------
    public function setReporterConstructsCellCultureOnly(array $list)
    {
        $this->_reporterConstructCellCultureOnlyList = $list;
    }
    // --------------------------------------------------------------------------------
    // Set the transcription factor binding sites to download
    // @param $list An array containing the results of a transcription factor binding
    // site search
    // --------------------------------------------------------------------------------
    public function setTranscriptionFactorBindingSites(array $list)
    {
        $this->_transcriptionFactorBindingSiteList = $list;
    }
    // --------------------------------------------------------------------------------
    // Retrieve the complete export file contents.
    // @return The export file contents.
    // --------------------------------------------------------------------------------
    public function getFile()
    {
        return $this->getHeader() . $this->getBody() . $this->getFooter();
    }
    // --------------------------------------------------------------------------------
    // @return The size of the export file.
    // --------------------------------------------------------------------------------
    public function getFileSize()
    {
        return $this->_headerLength + $this->_bodyLength + $this->_footerLength;
    }
    // --------------------------------------------------------------------------------
    // Generate the HTML headers for the export file download. This includes the
    // content type, content disposition, and content length. NOTE: This must be
    // called after the file has been generated or the content length will be 0!
    // @return An array containing the HTML headers.
    // --------------------------------------------------------------------------------
    public function getHtmlHeaders(
        $entityName,
        $selectedEntities
    ) {
        if ( ($entityName !== null) &&
            ($entityName !== "") ) {
            $filename = $entityName;
        } else {
            if ( ($selectedEntities !== null) &&
                ($selectedEntities !== "") ) {
                $filename = $selectedEntities;
            } else {
                $filename = "redfly_data";
            }
        }
        $filename = $filename .
            ( $this->_fileExtension !== null
                ? "." . $this->_fileExtension
                : "" );
        $headerList = array();
        $headerList[] = array(
            "Content-Type",
            $this->_mimeType
        );
        $headerList[] = array(
            "Content-Disposition",
            "attachment; filename=\"" . $filename . "\""
        );
        $headerList[] = array(
            "Content-Length",
            $this->getFileSize()
        );

        return $headerList;
    }
    // --------------------------------------------------------------------------------
    // Return the export file header, generating it if necessary.
    // @return A string containing the export file header.
    // --------------------------------------------------------------------------------
    public function getHeader()
    {
        if ( $this->_header === null ) {
            $this->generateFileHeader();
        }

        return $this->_header;
    }
    // --------------------------------------------------------------------------------
    // Return the export file body, generating it if necessary.
    // @return A string containing the export file body.
    // --------------------------------------------------------------------------------
    public function getBody()
    {
        if ( $this->_body === null ) {
            $this->generateFileBody();
        }

        return $this->_body;
    }
    // --------------------------------------------------------------------------------
    // Return the export file footer, generating it if necessary.
    // @return A string containing the export file footer.
    // --------------------------------------------------------------------------------
    public function getFooter()
    {
        if ( $this->_footer === null ) {
            $this->generateFileFooter();
        }

        return $this->_footer;
    }
    // --------------------------------------------------------------------------------
    // Methods for generating the actual file which must be defined in the extending
    // class.
    // --------------------------------------------------------------------------------
    // Generate the export file header. By default the header is an empty string.
    // --------------------------------------------------------------------------------
    public function generateFileHeader()
    {
        $this->_header = "";
    }
    // --------------------------------------------------------------------------------
    // Generate the export file body. By default the body is an empty string.
    // --------------------------------------------------------------------------------
    public function generateFileBody()
    {
        $this->_body = "";
    }
    // --------------------------------------------------------------------------------
    // Generate the export file footer. By default the footer is an empty string.
    // --------------------------------------------------------------------------------
    public function generateFileFooter()
    {
        $this->_footer = "";
    }
}
