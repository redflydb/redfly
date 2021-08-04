<?php
// ================================================================================
// Handle a REST (http://en.wikipedia.org/wiki/Representational_State_Transfer)
// request to the REDfly API.
// The API is set up using Apache redirects so that the url represents the API
// call. The actual controller url called is /api/rest/index.php (the
// controller) with path info following but the apache redirect removes the
// index.php portion. URLs have the format:
// /api/rest/{format}/{entity}/{action}[?tag=value&tag=value&species=X]
// Where
// {format} is the return format: json, xml
// {entity} is the entity we will operate on: chromosome, gene, bindingsite
// {action} is an action to perform: list, search, delete, add, edit
// [?tag=value] are optional arguments to the action
// In order for an entity to be valid, a class must be defined to serve as a
// handler for that entity (e.g., "ChromosomeHandler" will handle a "chromosome"
// entity). The controller will instantiate RestRequest to process an API
// request. RestRequest will perform validation on the request URL, parse it,
// and attempt to load a handler for the entity. If a handler is not found, an
// "invalid url" message is returned. This allows the API to be extended simply
// by the addition of a handler class.
// Handlers are named for the entity (with the first letter capitalized)
// followed by "Handler" and (e.g., chromosome is handled by ChromosomeHandler).
// Handlers contain two types of methods: "help" and "action". If the handler
// class contains an action method (e.g., "listAction" or "searchAction")
// matching the requested action then it will be called with any arguments and
// post data sent with the API request. If an action method is not found, an
// "invalid action" response is returned. If "help=y" is specified as an option
// and a help method is present for an action (e.g., "listHelp" or "searchHelp")
// then an action-specific help message is returned.
// For example:
// /api/rest/json/chromosome/list
// /api/rest/json/chromosome/search?name=2R
// /api/rest/json/chromosome/search?help=y
// /api/rest/json/transcriptionfactorbindingsite/search?name=%25wow%25&gene=eve
// /api/rest/json/species/list
// ================================================================================
require_once("iRestRequestHandler.php");
require_once("RestResponse.php");
class RestRequest
{
    // Full API call url sent to the server including the query string
    private $_fullApiCall = null;
    // Path portion of the API call (same as $_SERVER['PATH_INFO'])
    private $_apiUrl = null;
    // The query string passed in the API call
    private $_queryString = null;
    // The form GET data accompanying the request
    private $_getData = null;
    // The form POST data accompanying the request
    private $_postData = null;
    // Return format parsed from API url
    private $_returnFormat = null;
    // Entity that we are querying
    private $_entity = null;
    // The action to perform on the entity
    private $_action = null;
    // API query handler object
    private $_handler = null;
    // Query options parsed from the query string in the API call
    private $_options = array();
    // Set to TRUE if the request included a help request (e.g., help=y)
    private $_displayHelp = false;
    // Response message
    private $_response = null;
    // --------------------------------------------------------------------------------
    // Factory pattern.  Generate RestRequest objects based on the API call.
    // @param $requestUrl The full url of the request, including any path
    //   information preceeding the api URL as well as any query string
    //   information.
    // @param $pathInfo The API path portion of the URL following the actual
    //   location of the API script and not including the query string.  This is
    //   typically provided by the $_SERVER['PATH_INFO'] variable.
    // @param $queryString The http query string, if any.
    // @param $getData The contents of the form GET, if any, as parsed by PHP.
    //   This differs from the query string in the handling of arrays specified
    //   using the "[]" construct (e.g. redfly_id[]).
    // @param $postData The contents of the form POST, if any, as parsed by PHP.
    // @returns RestRequest object
    // --------------------------------------------------------------------------------
    public static function factory(
        $requestUrl,
        $pathInfo,
        $queryString = null,
        $getData = null,
        $postData = null
    ) {
        return new RestRequest(
            $requestUrl,
            $pathInfo,
            $queryString,
            $getData,
            $postData
        );
    }
    // --------------------------------------------------------------------------------
    // Construct a new instance of a RestRequest object.  The constructor is
    // private and is meant to be called by the factory() pattern.
    // @param $requestUrl The full url of the request, including any path
    //   information preceeding the api URL as well as any query string
    //   information.
    // @param $pathInfo The API path portion of the URL following the actual
    //   location of the API script and not including the query string.  This is
    //   typically provided by the $_SERVER['PATH_INFO'] variable.
    // @param $queryString The http query string, if any.
    // @param $postData The contents of the form POST, if any.
    // @returns RestRequest object
    // --------------------------------------------------------------------------------
    private function __construct(
        $requestUrl,
        $pathInfo = null,
        $queryString = null,
        $getData = null,
        $postData = null
    ) {
    
        $this->_fullApiCall = $requestUrl;
        $this->_apiUrl = $pathInfo;
        $this->_queryString = ( ! empty($queryString) ? urldecode($queryString) : null );
        $this->_postData = $postData;
        $this->_getData = $getData;
        $this->_options = array();
        // Parse the path info to determine our return type, entity, action, and
        // any options.
        $this->parseUrl();
        if ( ! $this->verifyReturnFormat() ) {
            $msg = "Invalid return data format requested '{$this->_returnFormat}'";
            throw new Exception($msg);
        }
        $this->authenticate();
        $this->loadHandler();
    }
    // --------------------------------------------------------------------------------
    // Parse the API URL to extract the data return format, type of query, entity
    // to query, optional entity component, and optional query options.
    // --------------------------------------------------------------------------------
    private function parseUrl()
    {
        // Trim any preceeding or trailing slashes from the API URL before breaking
        // it apart.
        $splitPath = explode("/", trim($this->_apiUrl, "/"));
        $numParts = count($splitPath);
        if ( $numParts != 3 ) {
            $msg = "Invalid Url '{$this->_apiUrl}'";
            throw new Exception($msg);
        }
        list($returnFormat, $entity, $action) = $splitPath;
        $this->_returnFormat = $returnFormat;
        $this->_entity = $entity;
        $this->_action = $action;
        if ( null !== $this->_getData ) {
            $this->_displayHelp = ( array_key_exists("help", $this->_getData) &&
                              "y" == strtolower(substr($this->_getData['help'], 0, 1)) );

            foreach ( $this->_getData as $key => $value ) {
                  $this->_options[$key] = $value;
            }
        }
    }
    // --------------------------------------------------------------------------------
    // Verify the return format.
    // --------------------------------------------------------------------------------
    private function verifyReturnFormat()
    {
        $formatMethod = $this->_returnFormat . "Format";
        return method_exists('RestResponse', $formatMethod);
    }
    // --------------------------------------------------------------------------------
    // Authenticate the user session.
    // --------------------------------------------------------------------------------
    private function authenticate()
    {
    }
    // --------------------------------------------------------------------------------
    // Load the request handler and create an instance via its factory() method.
    // @throws Exception If the request handler was not found
    // @throws Exception If the request handler does not implement iRestRequestHandler
    // --------------------------------------------------------------------------------
    private function loadHandler()
    {
        $handlerClassName = ucfirst($this->_entity) . "Handler";
        $handlerClassFile = $handlerClassName . ".php";
        include($handlerClassFile);
        if ( ! class_exists($handlerClassName) ) {
            $msg = "Unknown handler '$handlerClassName' for entity '$this->_entity'";
            throw new Exception($msg);
        }
        $handlerFactory = $handlerClassName . "::factory()";
        eval("\$this->_handler = $handlerFactory;");
    }
    // --------------------------------------------------------------------------------
    // Process the request.
    // @returns A RestResponse object
    // --------------------------------------------------------------------------------
    public function process()
    {
        if ( null === $this->_handler ) {
            $this->loadHandler();
        }
        if ( $this->_displayHelp ) {
            $helpMethod = $this->_action . "Help";
            if ( method_exists($this->_handler, $helpMethod) ) {
                $evalStr = '$this->_response = $this->_handler->' . $helpMethod . '();';
                eval($evalStr);
            } else {
                $this->_response =
                RestResponse::factory(true, "No help available for action '" . $this->_action . "'");
            }
            return $this->_response;
        }
        $actionMethod = $this->_action . "Action";
        if ( ! method_exists($this->_handler, $actionMethod) ) {
            $msg = "Undefined action '{$this->_action}'";
            throw new Exception($msg);
        }
        $evalStr = '$this->_response = $this->_handler->' .
        $actionMethod . '($this->_options, $this->_postData);';
        try {
            eval($evalStr);
        } catch ( Exception $e ) {
            $this->_response = RestResponse::factory(false, $e->getMessage());
        }
        return $this->_response;
    }
    // --------------------------------------------------------------------------------
    // Return the response in the requested format.  If the "download" entity was
    // requested return NULL because the entity will take care of returning the
    // appropriate headers and content.
    // @returns A response formatted according to the API request or NULL if the
    //   "download" entity was requested.
    // --------------------------------------------------------------------------------
    public function formatResponse()
    {
        // Process the request if it hasn't already been done
        if ( null === $this->_response ) {
            $this->process();
        }
        // Call the appropriate format method.  Existance of the formatted should be
        // checked in the constructor.
        $formatMethod = $this->_returnFormat . "Format";
        $retval = $this->_response->$formatMethod($this->_queryString);

        return $retval;
    }
    // --------------------------------------------------------------------------------
    // Return the response header string for use in the content-type header.  If
    // the "download" entity was requested return NULL because the entity will
    // take care of returning the appropriate headers and content.
    // @returns A header string for use in the content-type header or NULL if the
    //   "download" entity was requested.
    // --------------------------------------------------------------------------------
    public function responseHeader()
    {
        // Process the request if it hasn't already been done
        if ( null === $this->_response ) {
            $this->process();
        }
        // Call the appropriate header method.  Existance of the formatted should be
        // checked in the constructor.
        $headerMethod = $this->_returnFormat . "Header";
        $retval = $this->_response->$headerMethod();

        return $retval;
    }
    public function __toString()
    {
        return $this->formatResponse();
    }
}
