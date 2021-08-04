<?php
namespace CCR\REDfly\Service\External;

// Third-party libraries
use GuzzleHttp\ClientInterface;
use Latitude\QueryBuilder\{Conditions, QueryFactory};
use ParagonIE\EasyDB\EasyDB;
// REDfly libraries with namespaces
use CCR\REDfly\Service\External\Model\Citation;
/**
 * A simple class that allows for sending queries for Pubmed citations to the
 * NCBI Entrez ESummary endpoint.
 * @see https://www.ncbi.nlm.nih.gov/books/NBK25499/#chapter4.ESummary
 */
class EntrezDataSource
{
    /**
     * @const array ASCII_CONVERSION_TABLE Character conversion table for
     *     converting common non-ASCII characters to ASCII characters.
     */
    private const ASCII_CONVERSION_TABLE = [
        "&amp;" => "and", "@" => "at", "©" => "c", "®" => "r", "À" => "a",
        "Á" => "a", "Â" => "a", "Ä" => "a", "Å" => "a", "Æ" => "ae", "Ç" => "c",
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
    /**
     * @var EasyDB $db EasyDB instance for connecting to the database.
     */
    private $db;
    /**
     * @var QueryFactory $factory SQL builder factory.
     */
    private $factory;
    /**
     * @var ClientInterface $client The Guzzle client for connecting to the NCBI
     *     Pubmed endpoint.
     */
    private $client;
    /**
     * @var string $apiKey The API key to get the permission about 10 requests
     *     in an one-second window when using the Entrez services.
     */
    private $apiKey;
    /**
     * @var int $entrezRequestsNumber The Entrez requests number.
     */
    private $entrezRequestsNumber = 0;
    /**
     * @var int $entrezRequestsNumberLimit The Entrez requests number limit.
     * The Entrez service about checking Pubmed identifiers has a limit of requests,
     * 3 ones in an one-second window.
     * Once making 3 requests, we have to wait one second and then reset the next window.
     * If using an API key, then such a limit is increased into 10 in an one-second window.
     */
    private $entrezRequestsNumberLimit = 10;
    public function __construct(EasyDB $db, QueryFactory $factory, ClientInterface $client, string $apiKey)
    {
        $this->db = $db;
        $this->factory = $factory;
        $this->client = $client;
        $this->apiKey = $apiKey;
    }
    /**
     * Searches for a citation that matches the passed Pubmed ID. Checks the
     * citation cache first; if the citation is not cached, sends the query to
     * the NCBI Entrez ESummery endpoint and updates the cache with the new
     * citation, if any, before returning it.
     * @param int $pmid Pubmed ID to get the citation for.
     * @return ?Citation The returned citation, or null if there is no match.
     */
    public function query(int $pmid): ?Citation
    {
        $citation = $this->queryCache($pmid);
        if ( $citation === null ) {
            $citation = $this->queryEntrez($pmid);
            $this->entrezRequestsNumber++;
            if ( ($this->entrezRequestsNumber % $this->entrezRequestsNumberLimit) === 0 ) {
                sleep(1);
            }
            if ( $citation === null ) {
                return null;
            }
            $this->updateCache($citation);
        }

        return $citation;
    }
    /**
     * Queries the citation cache for the citation associated with a Pubmed
     * ID.
     * @param int $pmid Pubmed ID to get the citation for.
     * @return ?Citation The returned citation, or null if there is no match.
     */
    private function queryCache(int $pmid): ?Citation
    {
        $select = $this->factory->select()
            ->from("Citation")
            ->where(Conditions::make("external_id = ?", $pmid));
        $result = $this->db->row($select->sql(), ...$select->params());
        if ( isset($result["external_id"]) ) {
            if ( $result["author_list"] === null ) {
                $result["author_list"] = "";
            }
            if ( $result["title"] === null ) {
                $result["title"] = "";
            }
            if ( $result["journal_name"] === null ) {
                $result["journal_name"] = "";
            }
            if ( $result["year"] === null ) {
                $result["year"] = 0;
            }
            if ( !isset($result["month"]) ) {
                $result["month"] = "";
            } else {
                if ( $result["month"] === null ) {
                    $result["month"] = "";
                }
            }
            if ( $result["volume"] === null ) {
                $result["volume"] = "";
            }
            if ( $result["issue"] === null ) {
                $result["issue"] = "";
            }
            if ( $result["pages"] === null ) {
                $result["pages"] = "";
            }
    
            return new Citation(
                $result["external_id"],
                explode(", ", $result["author_list"]),
                $result["title"],
                $result["journal_name"],
                $result["year"],
                $result["month"],
                $result["volume"],
                $result["issue"],
                $result["pages"]
            );
        }

        return null;
    }
    /**
     * Queries the Entrez ESummary endpoint for the citation associated with a
     * Pubmed ID.
     * @param int $pmid Pubmed ID to get the citation for.
     * @return ?Citation The returned citation, or null if there is no match.
     */
    private function queryEntrez(int $pmid): ?Citation
    {
        $summary = json_decode(strtr($this->client->request("POST", "", [
            "query" => [
                "db"      => "pubmed",
                "retmode" => "json",
                "id"      => $pmid,
                "api_key" => $this->apiKey
            ]
        ])->getBody()->getContents(), self::ASCII_CONVERSION_TABLE), true);
        // No PMID match from NCBI
        if ( isset($summary["result"][$pmid]["error"]) ) {
            return null;
        }
        if ( isset($summary["result"][$pmid]) ) {
            $citation = $summary["result"][$pmid];
            // Paper publication
            $partsLine = explode(" ", $citation["pubdate"]);
            if ( (count($partsLine) === 2) ||
                 (count($partsLine) === 3) ) {
                $year = $partsLine[0];
                $month = $partsLine[1];
            } else {
                // Electronic publication
                $partsLine = explode(" ", $citation["epubdate"]);
                if ( (count($partsLine) === 2) ||
                     (count($partsLine) === 3) ) {
                    $year = $partsLine[0];
                    $month = $partsLine[1];
                } else {
                    return null;
                }
            }

            return new Citation(
                $citation["uid"],
                array_column($citation["authors"], "name"),
                $citation["title"],
                $citation["fulljournalname"],
                $year,
                $month,
                $citation["volume"],
                $citation["issue"],
                $citation["pages"]
            );
        }

        return null;
    }
    /**
     * Updates the cache with a new citation.
     * @param Citation $citation Citation to add to the cache.
     */
    private function updateCache(Citation $citation): void
    {
        $insert = $this->factory->insert("Citation", [
            "citation_type" => "PUBMED",
            "external_id"   => $citation->getPubmedId(),
            "title"         => $citation->getTitle(),
            "author_list"   => implode(", ", $citation->getAuthorList()),
            "contents"      => $citation->format(),
            "journal_name"  => $citation->getJournalName(),
            "year"          => $citation->getJournalYear(),
            "month"         => $citation->getJournalMonth(),
            "volume"        => $citation->getJournalVolume(),
            "issue"         => $citation->getJournalIssue(),
            "pages"         => $citation->getJournalPages()
        ]);
        $this->db->run($insert->sql(), ...$insert->params());
    }
}
