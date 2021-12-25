## Installation
```console
$ composer require whsv26/mediator
```

## Bundle configuration
```php
// config/packages/mediator.php

return static function (MediatorConfig $config) {
    $config->query()->middlewares([
        SlowLogQueryMiddleware::class
    ]);
    
    $config->command()->middlewares([
        TransactionalCommandMiddleware::class
    ]);
};
```

## Commands
```php
/**
 * @implements CommandInterface<Either<Rejection, Success>>
 */
class CreateUserCommand implements CommandInterface
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) { }
}

/**
 * NOTE: You need to register CreateUserCommandHandler as service
 * 
 * @implements CommandHandlerInterface<Either<Rejection, Success>, CreateUserCommand>
 */
class CreateUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepository $users
    ) { }

    /**
     * @param CreateUserCommand $command
     * @return Either<Rejection, Success>
     */
    public function handle($command): Either
    {
        $user = new User(
            Id::next(),
            new Email($command->email),
            new PlainPassword($command->password)
        );

        $this->users->save($user);

        return Either::right(new Success());
    }
}

class CreateUserAction
{
    public function __construct(
        private readonly MediatorInterface $mediator
    ) { }

    #[Route(path: '/users', name: self::class, methods: ['POST'])]
    public function __invoke(): Either
    {
        return $this->mediator->send(
            new CreateUserCommand(
                'whsv26@gmail.com', 
                'plain-password'
            )
        );
    }
}
```

## Queries
```php
/**
 * @implements QueryInterface<Option<User>>
 */
class FindUserQuery implements QueryInterface
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $email = null,
    ) { }
}

/**
 * NOTE: You need to register FindUserQueryHandler as service
 * 
 * @implements QueryHandlerInterface<Option<User>, FindUserQuery>
 */
class FindUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly UserRepository $users,
    ) { }

    /**
     * @param FindUserQuery $query
     * @return Option<User>
     */
    public function handle($query): Option
    {
        return Option::fromNullable($query->id)
            ->map(fn(string $id) => new Id($id))
            ->flatMap(fn(Id $id) => $this->users->findById($id))
            ->orElse(fn() => Option::fromNullable($query->email)
                ->map(fn(string $email) => new Email($email))
                ->flatMap(fn(Email $email) => $this->users->findByEmail($email))
            );
    }
}
```

## Middlewares
1) Implement ```CommandMiddlewareInterface``` or ```QueryMiddlewareInterface```
2) Register middleware class as service
3) Enable middleware services in bundle config

```php

/**
 * Example command middleware
 * 
 * NOTE: You need to register TransactionalCommandMiddleware as service
 */
class TransactionalCommandMiddleware implements CommandMiddlewareInterface
{
    public function __construct(
        private Connection $connection
    ) { }

    /**
     * @template TResponse
     * @template TCommand of CommandInterface<TResponse>
     *
     * @param TCommand $command
     * @param Closure(TCommand): TResponse $next
     *
     * @return TResponse
     */
    public function handle(CommandInterface $command, Closure $next): mixed
    {
        $this->connection->beginTransaction();

        try {
            $res = $next($command);
            $this->connection->commit();
            
            return $res;
        } catch (Throwable $e) {
            $this->connection->rollBack();
            
            throw $e;
        }
    }
}
```
