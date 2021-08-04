<?php
// ================================================================================
// REST response message. This class is responsible for constructing and
// formating a response to the REST API. Valid response formats are indicated
// by the definition of a method with the response format (lowercase) followed
// by "Format" (e.g., jsonFormat, xmlFormat, etc.). Each format method should
// have a corresponding method for returning (but not actually sending to the
// browser) a list of headers which should include the MIME header string (e.g.,
// jsonHeader). The handlers can provide an optional list of headers, which is
// especially useful for the raw handler and file downloads.
// See http://www.iana.org/assignments/media-types/application/
// ================================================================================

class RestResponse
{
    private $_success = true;
    private $_message = null;
    private $_numResults = 0;
    private $_results = array();
    private $_headers = null;
    private $_httpResponseCode = null;
    // --------------------------------------------------------------------------------
    // Factory pattern.
    // @params $success 1 for success, 0 for an error
    // @params $msg Optional message
    // @params $results An array of results. Single responses should still be
    //                  placed into an array to keep things normalized
    // @params $headers An optional array of headers that can be used by some of
    //                  the formatting methods (e.g., raw)
    // --------------------------------------------------------------------------------
    public static function factory(
        $success,
        $msg = null,
        array $results = array(),
        array $headers = array(),
        $httpResponseCode = 200,
        $num = null
    ) {
        return new RestResponse(
            $success,
            $msg,
            $results,
            $headers,
            $httpResponseCode,
            $num
        );
    }
    // --------------------------------------------------------------------------------
    // @see factory()
    // --------------------------------------------------------------------------------
    private function __construct(
        $success,
        $msg = null,
        array $results = array(),
        array $headers = array(),
        $httpResponseCode = 200,
        $num = null
    ) {
        $this->_success = $success;
        $this->_message = $msg;
        $this->_numResults = $num ?? count($results);
        $this->_results = $results;
        $this->_headers = $headers;
        $this->_httpResponseCode = $httpResponseCode;
    }
    // --------------------------------------------------------------------------------
    // @returns  A description of the "json" format for display by the self-discovery
    //           mechanism.
    // --------------------------------------------------------------------------------
    public function jsonHelp()
    {
        return "Display the entire response object encoded as a JSON object. The " .
            "response will be returned as an object where the results is an array arrays.";
    }
    // --------------------------------------------------------------------------------
    // Format the response for JSON
    // success: 0 = FALSE, 1 = TRUE
    // message: Optional message
    // num: Number of results
    // results: Array of result objects
    // @returns A JSON formatted response.
    // --------------------------------------------------------------------------------
    public function jsonFormat()
    {
        $retval = array(
            "success" => ($this->_success ? 1 : 0),
            "message" => $this->_message,
            "num"     => $this->_numResults,
            "results" => $this->_results);

        return json_encode($retval);
    }
    // --------------------------------------------------------------------------------
    // @returns An array containing the Content-Type header for JSON
    // --------------------------------------------------------------------------------
    public function jsonHeader()
    {
        return array(array("Content-Type", "application/json"));
    }
    // ------------------------------------------------------------------------------------
    // @returns  A description of the "jsonstore" format for display by the self-discovery
    //           mechanism.
    // ------------------------------------------------------------------------------------
    public function jsonstoreHelp()
    {
        return "Display the entire response object encoded as a JSON object formatted for " .
            "use by the ExtJS JsonStore. This differs from the \"json\" format in that the " .
            "results are returned as an array of objects rather than an array of arrays. The " .
            "\"callback\" parameter is also supported for cross-domain data requests using the " .
            "\"jsonP\" proxy in ExtJS 4.0+";
    }
    // --------------------------------------------------------------------------------
    // Format the response for an ExtJS JsonStore.
    // The ExtJS JsonStore expects the results to be an array of objects.
    // success: 0 = FALSE, 1 = TRUE
    // message: Optional message
    // num: Number of results
    // results: Array of result objects
    // @returns A JSON formatted response.
    // --------------------------------------------------------------------------------
    public function jsonstoreFormat($queryString = null)
    {
        // The ExtJS JsonStore expects the results to be an array of objects and
        // also expects "true" or "false" as the success.  We are treating the
        // results as an array of records.
        $callback = null;
        if ( $queryString !== null ) {
            $qsComponents = array();
            parse_str($queryString, $qsComponents);
            if ( array_key_exists("callback", $qsComponents) ) {
                $callback = $qsComponents["callback"];
            }
        }
        $results = array();
        foreach ( $this->_results as $id => $result ) {
            foreach ( $result as $tag => &$value ) {
                if ( is_array($value) ) {
                    $value = (object) $value;
                }
            }
            $results[] = (object) $result;
        }
        $retval = array(
            "success" => ($this->_success ? true : false),
            "message" => $this->_message,
            "num"     => $this->_numResults,
            "results" => $results
        );
        // Add a callback method to support ExtJS 4+ jsonP proxy, if requested. (???)
        return ( $callback === null
            ? json_encode($retval)
            : $callback . "( " . json_encode($retval) . ");" );
    }
    // --------------------------------------------------------------------------------
    // @returns An array containing the Content-Type header for gzipped JSON
    // --------------------------------------------------------------------------------
    public function jsonstoreHeader()
    {
        return array(array(
            "Content-Type",
            "application/json",
            "Content-Encoding",
            "gzip"
        ));
    }
    // --------------------------------------------------------------------------------
    // @returns  A description of the "text" format for display by the self-discovery
    //           mechanism.
    // --------------------------------------------------------------------------------
    public function textHelp()
    {
        return "Display the entire response as text. Useful for debugging via a browser.";
    }
    // --------------------------------------------------------------------------------
    // Format the response for text
    // success: 0 = FALSE, 1 = TRUE
    // message: Optional message
    // num: Number of results
    // results: Results list
    // @returns A JSON formatted response.
    // --------------------------------------------------------------------------------
    public function textFormat()
    {
        $retval = array(
            "success" => ($this->_success ? 1 : 0),
            "message" => $this->_message,
            "num"     => $this->_numResults,
            "results" => $this->_results
        );

        return print_r($retval, 1);
    }
    // --------------------------------------------------------------------------------
    // @returns An array containing the Content-Type header for text
    // --------------------------------------------------------------------------------
    public function textHeader()
    {
        return array(array("Content-Type", "text/plain"));
    }
    // --------------------------------------------------------------------------------
    // @returns  A description of the "raw" format for display by the self-discovery
    //           mechanism.
    // --------------------------------------------------------------------------------
    public function rawHelp()
    {
        return "Display the first item in the result array along with any headers that " .
            "have been set by the API handler. This is useful when an handler needs to " .
            "return unformatted data such as a file download.";
    }
    // --------------------------------------------------------------------------------
    // Format a raw response, typically for download or binary data.
    // This response type must be supported by the handler (i.e., it must set the
    // correct content type and other headers)
    // @returns The raw result.
    // --------------------------------------------------------------------------------
    public function rawFormat()
    {
        return array_shift($this->_results);
    }
    // --------------------------------------------------------------------------------
    // @returns The list of headers for a raw response type.
    // --------------------------------------------------------------------------------
    public function rawHeader()
    {
        return $this->_headers;
    }
    public function success()
    {
        return $this->_success;
    }
    public function message()
    {
        return $this->_message;
    }
    public function numResults()
    {
        return $this->_numResults;
    }
    public function results()
    {
        return $this->_results;
    }
    public function headers()
    {
        return $this->_headers;
    }
    public function httpResponseCode()
    {
        return $this->_httpResponseCode;
    }
}
