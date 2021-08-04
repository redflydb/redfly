<?php
// ================================================================================
// Specification of the methods that must be implemented by all REST request
// handlers.
// These include a factory pattern, and method to check whether or not the handler
// requires authentication, a help method, and a method to process the request.
// ================================================================================
interface iRestRequestHandler
{
    // --------------------------------------------------------------------------------
    // Return help information for this handler
    // --------------------------------------------------------------------------------
    public static function factory();
    // --------------------------------------------------------------------------------
    // Return help information for this handler.  Help is returned where the
    // response message contains a description of the component and the results
    // contain an associative array of all available options where the array key
    // is the option and the value is the desription of that option.
    // @param $action Optional component, as specific in the API URL
    // @returns A RestResponse object containing help information for the component.
    // --------------------------------------------------------------------------------\
    public function help($action = null);
}
