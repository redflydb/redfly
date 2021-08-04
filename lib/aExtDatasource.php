<?php
// ======================================================================
// Class: aExtDatasource
// Abstract class that defines common functionality used by all external
// datasource classes. Note that in order for the wait time between
// request to be honored, the same object should be used for multiple
// queries.
// @see iExtDatasource
// ======================================================================
abstract class aExtDatasource
{
    // Name of the datasourcey
    protected $_dsName = null;
    // URL for connecting to the data source
    protected $_url = null;
    // Minimum wait time in seconds between requests so a service is not
    // overwhelmed
    protected $_waitTimeBetweenRequests;
    // Timeout in seconds when waiting for a search result
    protected $_socketTimeout;
    // Resource handle for accessing the datasource
    protected $_dsHandle = null;
    // Data structure populated as a result of the query
    protected $_queryResult = null;
    // Error returned from a query
    protected $_error = null;
    // Timestamp of the most recent query
    protected $_lastQueryTime = null;
    // ----------------------------------------------------------------------
    // @param $url The url used to connect to the external data source
    // @param $dsName Human-readable name of the datasource, typically used in
    //   error messages.
    // @param $minWaitTime The number of seconds to wait between queries.  This is
    //   typically used so the datasource is not overloaded.
    // @param $socketTimeout Connection socket timeout
    // @throws Exception If $url or $dsName are not provided
    // @throws Exception If $waitTime or $socketTimeout are provided and are not
    //   an integer.
    // ----------------------------------------------------------------------
    function __construct(
        $url,
        $dsName,
        $minWaitTime = 0,
        $timeout = 0
    ) {
        if ( (! isset($url)) ||
            (! isset($dsName)) ) {
            throw new Exception("Datasource URL and name are required");
        }
        if ( ! is_integer($minWaitTime) ) {
            throw new Exception("Invalid wait time, must be an integer");
        }
        if ( ! is_integer($timeout) ) {
            throw new Exception("Invalid search timeout, must be an integer");
        }
        $this->_url = $url;
        $this->_waitTimeBetweenRequests = $minWaitTime;
        $this->_socketTimeout = $timeout;
        $this->_dsName = $dsName;
        $this->initialize();
    }
    // ----------------------------------------------------------------------
    // Perform any initialization needed to connect to the external datasource.
    // This is defined by the extending class.
    // ----------------------------------------------------------------------
    abstract protected function initialize();
    // ----------------------------------------------------------------------
    // @see iExtDatasource::getDatasourceName()
    // ----------------------------------------------------------------------
    public function getDatasourceName()
    {
        return $this->_dsName;
    }
    // ----------------------------------------------------------------------
    // @see iExtDatasource::setWaitTimeBetweenRequests()
    // ----------------------------------------------------------------------
    public function setWaitTimeBetweenRequests($waitTime)
    {
        if ( ($waitTime === null) ||
            (! is_integer($waitTime)) ||
            empty($waitTime) ) {
            throw new Exception("Wait time empty or not provided");
        }
        $this->_waitTimeBetweenRequests = $waitTime;
    }
    // ----------------------------------------------------------------------
    // @see iExtDatasource::getWaitTimeBetweenRequests()
    // ----------------------------------------------------------------------
    public function getWaitTimeBetweenRequests()
    {
        return $this->_waitTimeBetweenRequests;
    }
    // ----------------------------------------------------------------------
    // @see iExtDatasource::setSocketTimeout()
    // ----------------------------------------------------------------------
    public function setSocketTimeout($timeout)
    {
        if ( ($timeout === null) ||
            (! is_integer($timeout)) ||
            empty($timeout) ) {
            throw new Exception("Socket timeout empty or not provided");
        }
        $this->_socketTimeout = $timeout;
    }
    // ----------------------------------------------------------------------
    // @see iExtDatasource::getSocketTimeout()
    // ----------------------------------------------------------------------
    public function getSocketTimeout()
    {
        return $this->_socketTimeout;
    }
    // ----------------------------------------------------------------------
    // @see iExtDatasource::getServiceUrl()
    // ----------------------------------------------------------------------
    public function getServiceUrl()
    {
        return $this->_url;
    }
    // ----------------------------------------------------------------------
    // @see iExtDatasource::getError()
    // ----------------------------------------------------------------------
    public function getError()
    {
        return $this->_error;
    }
    // ----------------------------------------------------------------------
    // Generate an error string containing the datasource name.  The
    // error string can be accessed using getError().
    // @param $error Actual error string
    // @return The generated error string
    // ----------------------------------------------------------------------
    protected function setErrorString($error)
    {
        return ($this->_error = $this->_dsName . ": $error");
    }
    // ----------------------------------------------------------------------
    // Update the last query time.
    // @return The time of the last query as a unix timestamp
    // ----------------------------------------------------------------------
    protected function setLastQueryTime()
    {
        return ( $this->_lastQueryTime = time() );
    }
    // ----------------------------------------------------------------------
    // Wait the required amount of time before performing another query, if
    // necessary.  This will ensure that we do not overload a remote service.
    // @return TRUE when it is safe to fire off another query.
    // ----------------------------------------------------------------------
    protected function waitBeforeQuery()
    {
        if ( $this->_lastQueryTime === null ) {
            return true;
        }
        $currTime = time();
        $delta = ceil($currTime - $this->_lastQueryTime);
        if ( $delta <= $this->_waitTimeBetweenRequests ) {
            $timeToWait = ceil($this->_waitTimeBetweenRequests - $delta);
            sleep($timeToWait);
        }

        return true;
    }
}
