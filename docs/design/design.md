REDfly Design Rationale Document
--------------------------------

This document documents rationales behind various design decisions made during REDfly development.

# Table Of Contents

- [Table Of Contents](#table-of-contents)
- [Overall Design <a name="overall-design"></a>](#overall-design-)
  - [Web Application <a name="web-application"></a>](#web-application-)
    - [Command <a name="command"></a>](#command-)
      - [Decorator <a name="command-decorator"></a>](#decorator-)
      - [Service <a name="command-service"></a>](#service-)
      - [Example <a name="command-example"></a>](#example-)
    - [Query <a name="query"></a>](#query-)
      - [Decorator <a name="query-decorator"></a>](#decorator--1)
      - [Service <a name="query-service"></a>](#service--1)
      - [Example <a name="query-example"></a>](#example--1)
  - [Database <a name="database"></a>](#database-)
    - [Migration <a name="database-migration"></a>](#migration-)
    - [Storage <a name="database-storage"></a>](#storage-)

# Overall Design <a name="overall-design"></a>

## Web Application <a name="web-application"></a>

The architecture follows the principles of command-query responsibility separation (CQRS). The idea is simple: commands alter the application's state, while queries retrieve the application's state.

Note to the reader: to dispel any misconceptions, the commands spoken about here are _not_ the commands of the Command design pattern. They do share many similar responsibilities, but they are different concepts, with different aims.

Note to the reader: beware what you may find online regarding CQRS -- CQRS is part of a much-larger architectural pattern, which includes event sourcing (ES) and domain-driven design (DDD). The REDfly application is not complex enough to justify those features, so don't worry about understanding them.

Commands:
* Alter the application's state -- e.g. insert a record into the database.
* Never return a value.

Queries:
* Retrieve the application's state -- e.g. fetch a record from the database.
* Never alters the application's state.
* Always return a value.

![Overall Architecture](images/cqrs.png?raw=true)

A birds-eye view diagram of the overall architecture.

### Command <a name="command"></a>

Commands consist of the command itself and its handler. The command is a simple data transfer object (DTO) and has absolutely no logic beyond input validation (which should be done in the constructor), no methods beyond getters, and only contains data which act as parameters to be acted on. The names of the commands should reflect what it does -- `SaveUserCredentials`, `UpdateAppointment` and so on and implement the `Command` marker interface (rationale detailed below). The names should be clear enough that a programmer should have a very good idea what the command does without needing to look at any code.

Every command has a corresponding handler that handles that command. The handlers implement the `CommandHandler` interface and should be named after the command it handles, suffixed with `Handler` -- `SaveUserCredentialsHandler`, `UpdateAppointmentHandler` and so on.

The `CommandHandler` interface contains a single method

```
function handle(Command $command) : void
```

which accepts and handles a `Command` (hence the marker interface). The command and its handler are the points of entry into the application. No logic relating to the command should leak outside of its handler -- with the exception of creational logic; to facilitate loose coupling and testability, objects that the handler needs to do its job (e.g. a `PDO` object) should be instantiated and injected into the handler -- a.k.a. dependency injection.

Also note the return type -- `void`. If you find yourself trying to figure out how to return something from a `CommandHandler`, stop and re-think your approach -- it is likely that you actually want to create a query instead -- or perhaps you are actually looking at two tasks instead of one; in that case. separate the task into a command and a query, and have the caller call one after the other if necessary.

Commands should be singular -- there should not be known tasks exposed to the caller that requires two or more commands to accomplish. Instead, create a new command (with an appropriate name for the task) and compose several Commands within that Command instead. The rationale for this leads directly to...

#### Decorator <a name="command-decorator"></a>

The `\Command` namespace contains the `\Decorator` namespace, which contains command decorators (as in the Decorator design pattern). Those decorators also implement the `CommandHandler` interface, and brings one of the nicest benefits of the CQRS architecture: fine-grained middleware and aspect-oriented programming. The decorators include cross-cutting concerns. Examples include but is not limited to wrapping a command within a SQL transaction (`TransactionalCommandHandler`), ensuring that only one instance of that Command can run at a time (`SynchronizedCommandHandler`), making sure only authenticated users can execute a Command (`AuthenticatedCommandHandler`) and logging (`LoggingCommandHandler`).

As those decorators implement the `CommandHandler` interface, they can easily be composed (as in the Composite design pattern).

#### Service <a name="command-service"></a>

Within each namespace (they should be organized according to groups of features), are the `Command`s and the `CommandHandler`s for each specific endpoint for that feature, along with the `\Service` namespace. This is where the heavy-lifting should happen. The handlers should always be lightweight classes responsible for manipulating the database and little else; the classes in the `\Service` namespace are responsible for the application-side business logic. Here, there are no set architectural guidance -- anything goes. The only requirement is that each `\Service` namespace should be a bounded context (no dependencies should exist between services in other bounded contexts). This allows for the logic for a command to be, e.g., rewritten without affecting any other commands. This also allows for much easier debugging -- if an endpoint breaks, the programmer immediately knows where to look, and any fixes will not affect (i.e. break) any other endpoints.

There can, of course, also be top-level services which reside outside of any namespace within the `\Command` namespace; those services should address generic concerns -- services that would be useful to more than one command, such as encoding tasks used throuhgout the application.

In other words, the shorter the fully-qualified namespace is, the more general the service should be.

#### Example <a name="command-example"></a>

The following example is a simple command that updates an user's credentials stored in the database. The command is called `SaveUserCredentials`, which is consumed by the handler `SaveUserCredentialsHandler`. Assume that there is a `user` table in the database with two columns, `username` and `password`.

```
<?php
namespace CCR\REDfly\Command\User;

use CCR\REDfly\Command\Command;

class SaveUserCredentials implements Command
{
    private $username;
    private $password;

    public function __construct(string $username, string $password) {
        $this->username = $username;
        $this->password = $password;
    }

    public function getUsername() : string {
        return $this->username;
    }

    public function getPassword() : string {
        return $this->password;
    }
}
```

```
<?php
namespace CCR\REDfly\Command\User;

use DomainException,
    PDO,
    CCR\REDfly\Command\CommandHandler;

class SaveUserCredentialsHandler implements CommandHandler
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function handle(Command $command) : void
    {
        if ( !($command instanceof SaveUserCredentials) ) {
            $message = "The command cannot be handled by this handler.";
            throw new DomainException($message);
        }

        $sql = "UPDATE user SET password = ? WHERE username = ?;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$command->getPassword(), $command->getUsername()]);
    }
}
```

![Command Architecture](images/command-uml.png?raw=true)

The class diagram of the command architecture

### Query <a name="query"></a>

Queries also consist of the query and its handler. In fact, the query mirror the command in many ways. The names of queries should also reflect what it does -- `GetUser`, `GetAppointmentByUser`, and so on. The `Query` interface is also a marker interface, consumed by the `QueryHandler` interface, which also has only one method

```
function handle(Query $query) : string
```

which accepts and handles a `Query`. However, there is a major difference: the method does not return `void`; instead, it returns a string containing the result of the query.

Queries should never alter the application's state -- their behavior should always be read-only. If you find yourself altering the application's state within a query, that means you are likely combining a command and a query. Change the approach so that there is a separate command and query.

Similarly to the command handlers, the query handlers should be singular and self-contained, containing all logic needed to retrieve the application's state. And like the command handlers, all creational logic should take place outside of the query handler for the same rationales.

#### Decorator <a name="query-decorator"></a>

Like the decorators for commands, the decorators for queries implement the `QueryHandler` interface, enabling the programmer to develop approaches to cross-cutting concerns and handler composition. See the Command section's Decorator subsection above for more details -- the same principles apply here.

#### Service <a name="query-service"></a>

Like for the commands, the services should do the heavy-lifting required to perform any business logic on the data before returning it to the caller -- encoding is probably the most obvious example of a query service. The principles are the same as the command services, so read the Service subsection of the Command section above for a more detailed discussion.

#### Example <a name="query-example"></a>

The following example is a simple query that gets an user's information stored in the database. The query is called `GetUser`, which is consumed by the handler `GetUserHandler`. Assume that there is a `user` table (or view!) in the database with useful information about that user.

```
<?php
namespace CCR\REDfly\Query\User;

use CCR\REDfly\Query\Query;

class GetUser implements Query
{
    private $username;

    public function __construct(string $username) {
        $this->username = $username;
    }

    public function getUsername() : string {
        return $this->username;
    }
}
```

```
<?php
namespace CCR\REDfly\Query\User;

use DomainException,
    PDO,
    CCR\REDfly\Query\QueryHandler;

class GetUserHandler implements QueryHandler
{
    private $db;
    private $encoder;
    private $response;

    public function __construct(PDO $db, Encoder $encoder, Response $response)
    {
        $this->db = $db;
        $this->encoder = encoder;
        $this->response = $response;
    }

    public function handle(Query $query) : string
    {
        if ( !($query instanceof GetUser) ) {
            $message = "The query cannot be handled by this handler.";
            throw new DomainException($message);
        }

        $sql = "SELECT * FROM user WHERE username = ?;";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$query->getUsername()]);

        return $this->encoder->encode($stmt);
    }
}
```

![Query Architecture](images/query-uml.png?raw=true)

The class diagram of the query architecture

## Database <a name="database"></a>

### Migration <a name="database-migration"></a>

As we use raw SQL directly, we do not follow an elaborate migration scheme. The simple migration files, which can be found in `db/migrations` provides migrations between REDfly versions. The files must always be named `migration_v<older_version>_to_v<newer_version>.sql`. For example, a migration file for updating the database from REDfly 9.4.0 to REDfly 9.4.1 would be named `migration_v9.4.0_to_v9.4.1.sql`.

The developer must take care to create a migration file for migrating to the latest production version to the latest development version, and maintain it as needed when making changes or updates to the database schema.

### Storage <a name="database-storage"></a>

This section focuses on the data in the REDfly database and how they are stored.

1. *Sequence Coordinates*: as discussed in [this blog post](http://genome.ucsc.edu/blog/the-ucsc-genome-browser-coordinate-counting-systems/), coordinates could be stored in half-open (0-index for the start coordinate and 1-index for the end coordinate) or fully-closed (1-index coordinates for both start and end coordinates) systems. We use the fully-closed system in the REDfly database. This is important to note because we also interact with many external data sources and file formats, and developers will need to beware of which system that source or format uses, and ensure that this is compensated for when importing or exporting data between REDfly and the external source.
