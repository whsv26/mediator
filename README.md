## Installation
```console
$ composer require whsv26/mediator
```

## Bundle configuration
```php
// config/packages/mediator.php

return static function (MediatorConfig $config) {
    $config->bus()
        ->query('query.bus') // query bus service id
        ->command('command.bus') // command bus service id
        ->event('event.bus'); // event bus service id
};
```

### Enable psalm plugin (optional)
To check command and query return type compatibility with corresponding handler return type

```console
$ vendor/bin/psalm-plugin enable Whsv26\\Mediator\\Psalm\\Plugin
```

## Commands
```php
/**
 * @implements CommandInterface<UserId>
 */
class CreateUserCommand implements CommandInterface
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) { }
}

class CreateUserCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly HasherInterface $hasher,
        private readonly ClockInterface $clock,
        private readonly MediatorInterface $mediator,
    ) { }

    public function __invoke(CreateUserCommand $command): UserId
    {
        $user = new User(
            UserId::next(),
            new Email($command->email),
            new PlainPassword($command->password),
            $this->hasher,
            $this->clock
        );

        $this->users->save($user);

        // Publish domain events to subscribers 
        $this->mediator->publish($user->pullDomainEvents());

        return $user->getId();
    }
}

class CreateUserAction
{
    public function __construct(
        private readonly MediatorInterface $mediator
    ) { }

    #[Route(path: '/users', name: self::class, methods: ['POST'])]
    public function __invoke(CreateUserCommand $createUser): string
    {
        // $createUser deserialized from request body
        // via custom controller argument value resolver
    
        return $this->mediator
            ->sendCommand($createUser)
            ->value;
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

class FindUserQueryHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly UserRepository $users,
    ) { }

    /**
     * @param FindUserQuery $query
     * @return Option<User>
     */
    public function __invoke(FindUserQuery $query): Option
    {
        return Option::fromNullable($query->id)
            ->map(fn(string $id) => new UserId($id))
            ->flatMap(fn(UserId $id) => $this->users->findById($id))
            ->orElse(fn() => Option::fromNullable($query->email)
                ->map(fn(string $email) => new Email($email))
                ->flatMap(fn(Email $email) => $this->users->findByEmail($email))
            );
    }
}
```
