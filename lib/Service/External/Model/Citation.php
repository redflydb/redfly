<?php
namespace CCR\REDfly\Service\External\Model;

/**
 * Model representing a citation returned from an Entrez query.
 */
class Citation
{
    /**
     * @var int $pubmedId Pubmed ID.
     */
    private $pubmedId;
    /**
     * @var array $authorList Author list.
     */
    private $authorList;
    /**
     * @var string $title Publication title.
     */
    private $title;
    /**
     * @var string $journalName Journal name.
     */
    private $journalName;
    /**
     * @var int $journalYear Year of publication.
     */
    private $journalYear;
    /**
     * @var string $journalMonth Month of publication.
     */
    private $journalMonth;
    /**
     * @var string $journalVolume Journal volume.
     */
    private $journalVolume;
    /**
     * @var string $journalIssue Journal issue.
     */
    private $journalIssue;
    /**
     * @var string $journalPages Citation pages.
     */
    private $journalPages;
    public function __construct(
        int $pubmedId,
        array $authorList,
        string $title,
        string $journalName,
        int $journalYear,
        string $journalMonth,
        string $journalVolume,
        string $journalIssue,
        string $journalPages
    ) {
        $this->pubmedId = $pubmedId;
        $this->authorList = $authorList;
        $this->title = $title;
        $this->journalName = $journalName;
        $this->journalYear = $journalYear;
        $this->journalMonth = $journalMonth;
        $this->journalVolume = $journalVolume;
        $this->journalIssue = $journalIssue;
        $this->journalPages = $journalPages;
    }
    public function getPubmedId()
    {
        return $this->pubmedId;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getAuthorList()
    {
        return $this->authorList;
    }
    public function getJournalName()
    {
        return $this->journalName;
    }
    public function getJournalYear()
    {
        return $this->journalYear;
    }
    public function getJournalMonth()
    {
        return $this->journalMonth;
    }
    public function getJournalVolume()
    {
        return $this->journalVolume;
    }
    public function getJournalIssue()
    {
        return $this->journalIssue;
    }
    public function getJournalPages()
    {
        return $this->journalPages;
    }
    // --------------------------------------------------------------------------------
    // Format the citation using Modern Language Association (MLA) style.  See the
    // MLA Formatting and Style guide at
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
        $dateStr = "(" .
        ( null !== $this->journalMonth
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
