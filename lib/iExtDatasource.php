<?php
// ======================================================================
// Interface: iExtlDatasource
// Define an interface to an external datasource.  All classes
// querying an external datasource must make these methods available.
// ======================================================================
// Record used to store parameters passed to the external datasource.
// ======================================================================
class ExtDatasourceParameters
{
    // Generic record identifier
    public $recordId = null;
    // Array of optional arguments
    public $options = array();
}
// ======================================================================
// Interface for results returned by an external datasource.  All results must
// be able to format their contents for display.
// ======================================================================
interface iExtDatasourceResult
{
    public function format();
}
// ======================================================================
// Define an interface to an external datasource.  All classes querying an
// external datasource must make these methods available.
// ======================================================================
interface iExtDatasource
{
    // ----------------------------------------------------------------------
    // @return The human-readable name of this datasource
    // ----------------------------------------------------------------------
    public function getDatasourceName();
    // ----------------------------------------------------------------------
    // Set the minimum amount oftime that the interface should wait
    // between subsequent requests to the same datasource.  Some
    // datasources will block you if you query too often.
    // @param $waitTime Wait time in seconds
    // ----------------------------------------------------------------------
    public function setWaitTimeBetweenRequests($waitTime);
    // ----------------------------------------------------------------------
    // @return The currrent wait time
    // ----------------------------------------------------------------------
    public function getWaitTimeBetweenRequests();
    // ----------------------------------------------------------------------
    // Set the timeout for waiting on a search result from the
    // datasource.  Not all datasources support this option.
    // @param $timeout Time to wait, in seconds
    // ----------------------------------------------------------------------
    public function setSocketTimeout($timeout);
    // ----------------------------------------------------------------------
    // @return The currrent timeout
    // ----------------------------------------------------------------------
    public function getSocketTimeout();
    // ----------------------------------------------------------------------
    // Set the service url for this datasource.  Normally this is
    // handled by the class implementing this interface.
    //
    // @param $url New url to use when connecting to the service
    // ----------------------------------------------------------------------
    // public function setServiceUrl($url);
    // ----------------------------------------------------------------------
    // @return The currrent url for the datasource service
    // ----------------------------------------------------------------------
    public function getServiceUrl();
    // ----------------------------------------------------------------------
    // @return The error returned by the most recent operation, or NULL
    //   if there was no error.
    // ----------------------------------------------------------------------
    public function getError();
    // ----------------------------------------------------------------------
    // Query the datasource for information.
    // @param $param The query parameters
    // @return TRUE if the query was successful
    // @throws Exception if there was an error during the query.
    // @see getError()
    // ----------------------------------------------------------------------
    public function query(ExtDatasourceParameters $param);
    // ----------------------------------------------------------------------
    // @return The result of the query.  This will be specifc to the
    //   search and implementing class.
    // @throws Exception if the query has not yet been made.
    // ----------------------------------------------------------------------
    public function getResult();
}
