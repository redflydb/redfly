<?php
namespace CCR\REDfly\Middleware;

// Standard PHP Libraries (SPL)
use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Message\ErrorResult;
/**
 * Middleware for sending error messages in the response for debugging
 * purposes.
 */
class DebugMiddleware
{
    /**
     * Tries to run the next middleware. If an error or an exception is thrown,
     * it is caught, and a response containing the error message is returned.
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
        try {
            return $next(
                $request,
                $response
            );
        } catch ( Throwable $e ) {
            $result = new ErrorResult($e->getMessage());
            $response->getBody()->write(json_encode($result));
            return $response->withStatus(500);
        }
    }
}
