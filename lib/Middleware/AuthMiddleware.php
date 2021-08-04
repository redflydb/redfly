<?php
namespace CCR\REDfly\Middleware;

// Standard PHP Libraries (SPL)
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
// Third-party libraries
use Latitude\QueryBuilder\{Conditions, QueryFactory};
use ParagonIE\EasyDB\EasyDB;
/**
 * Middleware for authenticating incoming requests.
 */
class AuthMiddleware
{
    /**
     * @var EasyDB $db EasyDB instance for connecting to the database.
     */
    private $db;
    /**
     * @var QueryFactory $factory SQL builder factory.
     */
    private $factory;
    /**
     * @var string $realm Basic authentication realm name.
     */
    private $realm;
    public function __construct(
        EasyDB $db,
        QueryFactory $factory,
        string $realm
    ) {
        $this->db = $db;
        $this->factory = $factory;
        $this->realm = $realm;
    }
    /**
     * Authenticates the session against the database using HTTP basic
     * authentication.
     * @param ServerRequestInterface $request Incoming request.
     * @param ResponseInterface $response Outgoing response.
     * @param callable $next Next middleware.
     * @return ResponseInterface The outgoing response.
     */
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ): ResponseInterface {
        if ( $request->hasHeader("Authorization") ) {
            $auth = $this->parseAuthHeader($request->getHeaderLine("Authorization"));
            if ( $this->auth(
                $auth["username"],
                $auth["password"]
            ) ) {
                return $next(
                    $request
                        ->withAttribute(
                            "username",
                            $auth["username"]
                        )
                        ->withAttribute(
                            "password",
                            $auth["password"]
                        ),
                    $response
                );
            }
        }

        return $response
            ->withStatus(401)
            ->withHeader(
                "WWW-Authenticate",
                sprintf(
                    "Basic realm=\"%s\"",
                    $this->realm
                )
            );
    }
    private function parseAuthHeader(string $header): array
    {
        if ( strpos($header, "Basic") !== 0 ) {
            return [
                "username" => "",
                "password" => ""
            ];
        }
        $auth = explode(
            ":",
            base64_decode(substr($header, 6)),
            2
        );

        return [
            "username" => $auth[0],
            "password" => $auth[1] ?? ""
        ];
    }
    private function auth(
        string $username,
        string $password
    ): bool {
        $select = $this->factory->select("username")
            ->from("Users")
            ->where(Conditions::make("username = ?", $username)
                ->andWith("password = ?", $this->hash($password)));

        return $username === $this->db->cell($select->sql(), ...$select->params());
    }
    private function hash($password)
    {
        return "{SHA}" . base64_encode(sha1($password, true));
    }
}
