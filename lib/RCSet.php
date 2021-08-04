<?php
// ======================================================================
// Simply a set of reporter constructs.
// This is meant to be extended to add set operations.
// ======================================================================
class RCSet implements Iterator
{
    // List of set members
    protected $rcList = array();
    // --------------------------------------------------------------------------------
    // Reset the internal state.
    // --------------------------------------------------------------------------------
    public function reset()
    {
        $this->rcList = array();
    }
    // --------------------------------------------------------------------------------
    // Add an PotentialCRM to the set.
    // --------------------------------------------------------------------------------
    public function addRc(RC $rc)
    {
        $this->rcList[] = $rc;
    }
    // --------------------------------------------------------------------------------
    // Sort comparison function. Sort the list of PotentialCRMs based
    // on their size.
    // --------------------------------------------------------------------------------
    private function _sort(
        RC $a,
        RC $b
    ) {
        return ( $a->size == $b->size ? 0 : ( $a->size > $b->size ? 1 : -1 ) );
    }
    // --------------------------------------------------------------------------------
    // Sort the list of PotentialCRMs based on their size.
    // --------------------------------------------------------------------------------
    private function sort()
    {
        uasort(
            $this->rcList,
            array(
                $this,
                "_sort"
            )
        );
    }
    public function __toString()
    {
        $str = "{\n";
        foreach ( $this->rcList as $rc ) {
            $str .= "  " . $rc->__toString();
        }
        $str .= "}\n";

        return $str;
    }
    // ================================================================================
    // Iterator interface methods
    // ================================================================================
    public function rewind()
    {
        return reset($this->rcList);
    }
    public function current()
    {
        return current($this->rcList);
    }
    public function key()
    {
        return key($this->rcList);
    }
    public function next()
    {
        return next($this->rcList);
    }
    public function valid()
    {
        return ( false !== current($this->rcList));
    }
    public function count()
    {
        return count($this->rcList);
    }
}
