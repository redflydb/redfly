<?php
// ======================================================================
// Result of the query to NCBI Pubmed.
// @see iExtDatasourceResult
// ======================================================================
class Citation implements iExtDatasourceResult
{
    // Record identifier
    public $id = null;
    // List of authors (may be empty)
    public $authorList = array();
    // Article title
    public $title = null;
    // Journal name
    public $journalName = null;
    // Journal publication year
    public $journalYear = null;
    // Journal publication month (may be empty)
    public $journalMonth = null;
    // Journal publication volume (may be empty)
    public $journalVolume = null;
    // Journal issue (may be empty)
    public $journalIssue = null;
    // Journal pagination (may be empty)
    public $journalPages = null;
    // --------------------------------------------------------------------------------
    // Format the citation using Modern Language Association (MLA) style.
    //  See the MLA Formatting and Style guide at
    // http://owl.english.purdue.edu/owl/resource/747/01/
    // Author. "Title of Article." Title of Journal Volume number.Issue number (Year): Pages. Type of Material.
    // @returns A formatted citation string.
    // --------------------------------------------------------------------------------
    public function format()
    {
        $authorStr = ( 0 != count($this->authorList)
            ? implode(", ", $this->authorList) . ". "
            : "" );
        $titleStr = "\"" . $this->title . "\"";
        $volumeStr = ( null !== $this->journalVolume
            ? " " . $this->journalVolume . ( null !== $this->journalIssue ? "." . $this->journalIssue : "" )
            : "" );
        $dateStr = "(" . ( null !== $this->journalMonth
            ? $this->journalMonth . " "
            : "" ) .
        $this->journalYear . ")";
        $paginationStr = ( null !== $this->journalPages
            ? ": " . $this->journalPages . "."
            : "" );
        $journalStr = " " . $this->journalName . "$volumeStr $dateStr";

        return $authorStr . $titleStr . $journalStr . $paginationStr;
    }
}
