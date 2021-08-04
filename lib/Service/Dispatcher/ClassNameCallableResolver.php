<?php
namespace CCR\REDfly\Service\Dispatcher;

// Third-party libraries
use Psr\Container\ContainerInterface;
// REDfly libraries with namespaces
use CCR\REDfly\Service\Exception\HandlerNotFoundException;
/**
 * Callable resolver that uses a dependency injection container to resolve the
 * callable based on the class name of the message object.
 */
class ClassNameCallableResolver implements CallableResolverInterface
{
    /** @var ContainerInterface $container Dependency injection container. */
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function resolve($message): callable
    {
        $inflection = sprintf("%sHandler", get_class($message));
        if ( $this->container->has($inflection) ) {
            return $this->container->get($inflection);
        }

        throw new HandlerNotFoundException(get_class($message));
    }
}
