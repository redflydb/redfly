<?php
namespace CCR\REDfly\Service\Dispatcher;

// Standard PHP Libraries (SPL)
use JsonSerializable, ReflectionClass;
// Third-party libraries
use League\JsonGuard\Validator;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Exception\InvalidMessageException;
use CCR\REDfly\Service\Message\{CommandInterface, QueryInterface, QueryResult};
/**
 * Dispatcher decorator that validates a command or query before dispatching it.
 * The command or query is validated against an JSON schema (for more details,
 * see http://json-schema.org) that describes the structure. All commands and
 * queries implement CommandInterface and QueryInterface respectively, both of
 * which extends JsonSerializable.
 * An example of a serialized simple command could look like:
 * {
 *     "id": 2
 * }
 * which would validate against the simple schema:
 * {
 *     "$schema": "http://json-schema.org/draft-06/schema#",
 *     "type": "object",
 *     "properties": {
 *         "id": {
 *             "type": "integer",
 *             "minimum": 0
 *     },
 *     "required": ["id"]
 * }
 * If the command or query is valid, the dispatcher proceeds and dispatches the
 * command or query; otherwise an InvalidMessageException will be thrown.
 */
class ValidatingDispatcher implements DispatcherInterface
{
    /** @var DispatcherInterface $decorated Decorated dispatcher. */
    private $decorated;
    public function __construct(DispatcherInterface $decorated)
    {
        $this->decorated = $decorated;
    }
    public function send(CommandInterface $command): void
    {
        if ( $this->isValid($command) === false ) {
            throw new InvalidMessageException(get_class($command));
        }
        $this->decorated->send($command);
    }
    public function request(QueryInterface $query): QueryResult
    {
        if ( $this->isValid($query) === false ) {
            throw new InvalidMessageException(get_class($query));
        }

        return $this->decorated->request($query);
    }
    private function isValid(JsonSerializable $message): bool
    {
        $reflection = new ReflectionClass(get_class($message));
        $file = str_replace(".php", ".json", $reflection->getFileName());
        if ( file_exists($file) ) {
            $data = json_decode(json_encode($message));
            $schema = json_decode(file_get_contents($file));
            $validator = new Validator($data, $schema);

            return $validator->passes();
        }

        return true;
    }
}
