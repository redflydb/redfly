<?php
// ======================================================================
// Machinery to query the external datasource at NCBI Pubmed
// Implement methods to access the NCBI Pubmed web services.
// More information can be found under the NCBI E-Utilities page at
// http://www.ncbi.nlm.nih.gov/books/NBK25501/
// The following url provides specifics on searching the literature
// databases:
// http://www.ncbi.nlm.nih.gov/books/NBK25499/#chapter4.EFetch
// @see aExtDatasource
// @see iExtDatasource
// ======================================================================
class ExtDatasource_Pubmed extends aExtDatasource implements iExtDatasource
{
    // Name of the external datasource for error reporting
    const dsName = "NCBI Pubmed via Entrez";
    // Make no more than one request every 1 second
    const waitTimeBetweenQueries = 5;
    const url = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi";
    // NCBI database name. The following url (EInfo) lists all available databases:
    // http://www.ncbi.nlm.nih.gov/entrez/eutils/einfo.fcgi?
    const db = "pubmed";
    // Record type (valid are uilist, abstract, citation, medline full)
    const recordType = "citation";
    // Retrieval mode (valid are xml, html, text, asn.1)
    const retrievalMode = "xml";
    // Used to contact you if there are problems with your queries or
    // if we are changing software interfaces that might specifically
    // affect your requests.
    const email = "redflyteam@gmail.com";
    // A string with no internal spaces that identifies the resource
    // which is using Entrez links.
    const tool = "redfly";
    // The character conversion table for converting common non-ASCII characters
    // to ASCII characters.
    private $_convertTable = [
        "&amp;" => "and", "@" => "at", "©" => "c", "®" => "r", "À" => "a",
        "Á" => "a", "Â" => "a", "Ä" => "a", "Å" => "a", "Æ" => "ae","Ç" => "c",
        "È" => "e", "É" => "e", "Ë" => "e", "Ì" => "i", "Í" => "i", "Î" => "i",
        "Ï" => "i", "Ò" => "o", "Ó" => "o", "Ô" => "o", "Õ" => "o", "Ö" => "o",
        "Ø" => "o", "Ù" => "u", "Ú" => "u", "Û" => "u", "Ü" => "u", "Ý" => "y",
        "ß" => "ss", "à" => "a", "á" => "a", "â" => "a", "ä" => "a", "å" => "a",
        "æ" => "ae", "ç" => "c", "è" => "e", "é" => "e", "ê" => "e", "ë" => "e",
        "ì" => "i", "í" => "i", "î" => "i", "ï" => "i", "ò" => "o", "ó" => "o",
        "ô" => "o", "õ" => "o", "ö" => "o", "ø" => "o", "ù" => "u", "ú" => "u",
        "û" => "u", "ü" => "u", "ý" => "y", "þ" => "p", "ÿ" => "y", "Ā" => "a",
        "ā" => "a", "Ă" => "a", "ă" => "a", "Ą" => "a", "ą" => "a", "Ć" => "c",
        "ć" => "c", "Ĉ" => "c", "ĉ" => "c", "Ċ" => "c", "ċ" => "c", "Č" => "c",
        "č" => "c", "Ď" => "d", "ď" => "d", "Đ" => "d", "đ" => "d", "Ē" => "e",
        "ē" => "e", "Ĕ" => "e", "ĕ" => "e", "Ė" => "e", "ė" => "e", "Ę" => "e",
        "ę" => "e", "Ě" => "e", "ě" => "e", "Ĝ" => "g", "ĝ" => "g", "Ğ" => "g",
        "ğ" => "g", "Ġ" => "g", "ġ" => "g", "Ģ" => "g", "ģ" => "g", "Ĥ" => "h",
        "ĥ" => "h", "Ħ" => "h", "ħ" => "h", "Ĩ" => "i", "ĩ" => "i", "Ī" => "i",
        "ī" => "i", "Ĭ" => "i", "ĭ" => "i", "Į" => "i", "į" => "i", "İ" => "i",
        "ı" => "i", "Ĳ" => "ij", "ĳ" => "ij", "Ĵ" => "j", "ĵ" => "j", "Ķ" => "k",
        "ķ" => "k", "ĸ" => "k", "Ĺ" => "l", "ĺ" => "l", "Ļ" => "l", "ļ" => "l",
        "Ľ" => "l", "ľ" => "l", "Ŀ" => "l", "ŀ" => "l", "Ł" => "l", "ł" => "l",
        "Ń" => "n", "ń" => "n", "ñ" => "n", "Ņ" => "n", "ņ" => "n", "Ň" => "n",
        "ň" => "n", "ŉ" => "n", "Ŋ" => "n", "ŋ" => "n", "Ō" => "o", "ō" => "o",
        "Ŏ" => "o", "ŏ" => "o", "Ő" => "o", "ő" => "o", "Œ" => "oe", "œ" => "oe",
        "Ŕ" => "r", "ŕ" => "r", "Ŗ" => "r", "ŗ" => "r", "Ř" => "r", "ř" => "r",
        "Ś" => "s", "ś" => "s", "Ŝ" => "s", "ŝ" => "s", "Ş" => "s", "ş" => "s",
        "Š" => "s", "š" => "s", "Ţ" => "t", "ţ" => "t", "Ť" => "t", "ť" => "t",
        "Ŧ" => "t", "ŧ" => "t", "Ũ" => "u", "ũ" => "u", "Ū" => "u", "ū" => "u",
        "Ŭ" => "u", "ŭ" => "u", "Ů" => "u", "ů" => "u", "Ű" => "u", "ű" => "u",
        "Ų" => "u", "ų" => "u", "Ŵ" => "w", "ŵ" => "w", "Ŷ" => "y", "ŷ" => "y",
        "Ÿ" => "y", "Ź" => "z", "ź" => "z", "Ż" => "z", "ż" => "z", "Ž" => "z",
        "ž" => "z", "ſ" => "z", "Ə" => "e", "ƒ" => "f", "Ơ" => "o", "ơ" => "o",
        "Ư" => "u", "ư" => "u", "Ǎ" => "a", "ǎ" => "a", "Ǐ" => "i", "ǐ" => "i",
        "Ǒ" => "o", "ǒ" => "o", "Ǔ" => "u", "ǔ" => "u", "Ǖ" => "u", "ǖ" => "u",
        "Ǘ" => "u", "ǘ" => "u", "Ǚ" => "u", "ǚ" => "u", "Ǜ" => "u", "ǜ" => "u",
        "Ǻ" => "a", "ǻ" => "a", "Ǽ" => "ae", "ǽ" => "ae", "Ǿ" => "o", "ǿ" => "o",
        "ə" => "e", "Ё" => "jo", "Є" => "e", "І" => "i", "Ї" => "i", "А" => "a",
        "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ж" => "zh",
        "З" => "z", "И" => "i", "Й" => "j", "К" => "k", "Л" => "l", "М" => "m",
        "Н" => "n", "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t",
        "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "c", "Ч" => "ch", "Ш" => "sh",
        "Щ" => "sch", "Ъ" => "-", "Ы" => "y", "Ь" => "-", "Э" => "je", "Ю" => "ju",
        "Я" => "ja", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
        "е" => "e", "ж" => "zh", "з" => "z", "и" => "i", "й" => "j", "к" => "k",
        "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
        "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "c",
        "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "-", "ы" => "y", "ь" => "-",
        "э" => "je", "ю" => "ju", "я" => "ja", "ё" => "jo", "є" => "e", "і" => "i",
        "ї" => "i", "Ґ" => "g", "ґ" => "g", "א" => "a", "ב" => "b", "ג" => "g",
        "ד" => "d", "ה" => "h", "ו" => "v", "ז" => "z", "ח" => "h", "ט" => "t",
        "י" => "i", "ך" => "k", "כ" => "k", "ל" => "l", "ם" => "m", "מ" => "m",
        "ן" => "n", "נ" => "n", "ס" => "s", "ע" => "e", "ף" => "p", "פ" => "p",
        "ץ" => "C", "צ" => "c", "ק" => "q", "ר" => "r", "ש" => "w", "ת" => "t",
        "™" => "tm"
    ];
    function __construct()
    {
        parent::__construct(
            self::url,
            self::dsName,
            self::waitTimeBetweenQueries
        );
    }
    // ----------------------------------------------------------------------
    // @see aExtDatasource::initialize()
    // ----------------------------------------------------------------------
    protected function initialize()
    {
        try {
            $this->_dsHandle = curl_init();
        } catch ( Exception $fault ) {
            $this->_dsHandle = null;
            throw new Exception("Error initializing curl client: " . $fault);
        }
    }
    // ----------------------------------------------------------------------
    // Return an array containing the list of functions available from this
    // web service.
    // @returns An array containing the list of functions available
    //   from this web service.
    // ----------------------------------------------------------------------
    public function getFunctionList()
    {
        return $this->_dsHandle->__getFunctions();
    }
    // ----------------------------------------------------------------------
    // Query the database for a citation record and parse it out.
    // @param $params An ExtDatasourceParameters object containing the record id
    //   to be used in the query.
    // @throws Exception If a record id was not provided
    // @throws Exception If the web service call failed
    // @throws Exception If structure of the query result is not valid.
    // @returns an associative array containing the citation information as
    //   follows:
    //   array("title"       => article title,
    //         "author_list" => array(author1, author2, ...),
    //         "journal_info" => array("name"   => journal name,
    //                                 "year"   => publication year,
    //                                 "month"  => publication month
    //                                 "volume" => journal volumne,
    //                                 "issue"  => journal issue,
    //                                 "pages"  => journal pages)
    //         );
    // ----------------------------------------------------------------------
    public function query(ExtDatasourceParameters $params)
    {
        $this->_queryResult = null;
        if ( ! isset($params->recordId) ) {
            throw new Exception("Record id not set");
        }
        if ( ! is_numeric($params->recordId) ) {
            throw new Exception("Invalid PUBMED id: \"" . $params->recordId . "\"");
        }
        $citation = new Citation;
        // ----------------------------------------------------------------------
        // Set up the query parameters for the NCBI request.
        // Parameters are described on the EFetch literature database page at
        // http://www.ncbi.nlm.nih.gov/books/NBK25499/#chapter4.EFetch
        // ----------------------------------------------------------------------
        $queryParameters = array(
            "db"      => self::db,
            "tool"    => self::tool,
            "email"   => self::email,
            "rettype" => self::recordType,
            "retmode" => self::retrievalMode,
            "id"      => $params->recordId
        );
        $query_url = self::url . "?" . http_build_query($queryParameters, "", "&");
        curl_setopt($this->_dsHandle, CURLOPT_URL, $query_url);
        curl_setopt($this->_dsHandle, CURLOPT_HEADER, false);
        curl_setopt($this->_dsHandle, CURLOPT_RETURNTRANSFER, true);
        $this->waitBeforeQuery();
        $this->setLastQueryTime();
        try {
            // raw string
            $rawString = strtr(curl_exec($this->_dsHandle), $this->_convertTable);
            // convert string to XML
            $result = new SimpleXMLElement($rawString, LIBXML_NOCDATA);
        } catch ( Exception $fault ) {
            throw new Exception($this->setErrorString("Error querying record \"" .
              $params->recordId . "\"; " . $fault->getMessage()));
        }
        if ( ! @is_object($result->PubmedArticle->MedlineCitation->Article) ) {
            throw new Exception(self::dsName . ": Invalid response, Medline Article not present in result");
        } elseif ( ! @is_object($result->PubmedArticle->MedlineCitation->Article->Journal) ) {
            throw new Exception(self::dsName . ": Could not access article journal in result");
        } elseif ( ! @isset($result->PubmedArticle->MedlineCitation->Article->ArticleTitle) ) {
            throw new Exception(self::dsName . ": Could not access article title in result");
        }
        $citationObj = $result->PubmedArticle->MedlineCitation->Article;
        $journalObj = $citationObj->Journal;
        $articleTitle = (string)$citationObj->ArticleTitle;
        $paginationObj = $citationObj->Pagination;
        $citation->title = $articleTitle;
        if ( @is_object($citationObj->AuthorList) ) {
            $authorListObj = $citationObj->AuthorList;
            $authorList = ( (count($authorListObj->children()) > 1)
                ? $authorListObj->children()
                : $authorListObj->Author );
            foreach ( $authorList as $author ) {
                $name = $author->LastName . ( isset($author->Initials)
                    ? " " . $author->Initials
                    : "" );
                $citation->authorList[] = htmlentities(utf8_decode($name));
            }
        }
        $journalTitle = ( isset($journalObj->Title) ? (string) $journalObj->Title : null );
        $journalIssueObj = $journalObj->JournalIssue;
        $volume = ( isset($journalIssueObj->Volume) ? (string)$journalIssueObj->Volume : null );
        $issue = ( isset($journalIssueObj->Issue) ? (string)$journalIssueObj->Issue : null );
        $month = $year = null;
        if ( isset($journalIssueObj->PubDate->Year) ) {
            $year = (string)$journalIssueObj->PubDate->Year;
            $month = ( isset($journalIssueObj->PubDate->Month) ? (string)$journalIssueObj->PubDate->Month : null );
        } else {
            $year = (string)$journalIssueObj->PubDate->MedlineDate;
        }
        $pages = null;
        if ( isset($paginationObj->MedlinePgn) ) {
            $pages = (string)$paginationObj->MedlinePgn;
        } else {
            $pages = (string)$paginationObj->startPage . ( isset($paginationObj->EndPage)
                ? "-" . (string)$paginationObj->EndPage
                : "" );
        }
        $citation->journalName = htmlentities(utf8_decode($journalTitle));
        $citation->journalYear = $year;
        $citation->journalMonth = $month;
        $citation->journalVolume = $volume;
        $citation->journalIssue = htmlentities(utf8_decode($issue));
        $citation->journalPages = $pages;
        $this->_queryResult = $citation;

        return true;
    }
    // ----------------------------------------------------------------------
    // @see iExtDatasource::getResult()
    // ----------------------------------------------------------------------
    public function getResult()
    {
        return $this->_queryResult;
    }
}
