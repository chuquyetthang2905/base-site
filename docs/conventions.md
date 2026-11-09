# base-site conventions — Modular Action-Based Architecture

Flow: `FormRequest -> Controller -> DTO -> Action -> Model/Repository`

Reference implementation: `app/Domains/Auth` (+ `app/Http/*/Auth`). Copy its shape for every new module.

## Folder layout

```
app/Domains/<Module>/
  Actions/        single-action classes (execute())
  DTOs/           final readonly classes
  Enums/          backed enums
  Events/         past-tense events
  Exceptions/     <Module>Exception extends App\Support\Exceptions\DomainException
  Listeners/      implement ShouldQueue when slow/async (mail, AI, reports)
  Models/         Eloquent (Active Record)
  Repositories/   XxxRepositoryInterface + XxxRepository
app/Support/       DomainException, shared HTTP helpers (RefreshTokenCookie)
app/Http/
  Controllers/Api/V1/<Module>/   skinny controllers
  Requests/<Module>/             authorize + rules + toDto()
  Resources/<Module>/            JsonResources
app/Providers/DomainServiceProvider.php   bindings + Event::listen
routes/api_v1.php
```

## Templates (feature "Create task")

FormRequest
```php
final class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('create', Task::class) ?? false; }
    public function rules(): array { return ['title' => ['required', 'string', 'max:255']]; }
    public function toDto(): CreateTaskDTO { return CreateTaskDTO::fromValidated($this->validated(), (int) $this->user()->id); }
}
```

DTO
```php
final readonly class CreateTaskDTO
{
    public function __construct(public string $title, public int $userId) {}
    public static function fromValidated(array $v, int $userId): self { return new self($v['title'], $userId); }
}
```

Action
```php
final readonly class CreateTaskAction
{
    public function execute(CreateTaskDTO $dto): Task
    {
        $task = Task::query()->create(['title' => $dto->title, 'user_id' => $dto->userId]);
        TaskCreated::dispatch($task);
        return $task;
    }
}
```

Controller (method)
```php
public function store(CreateTaskRequest $request, CreateTaskAction $action): JsonResponse
{
    return $this->created(new TaskResource($action->execute($request->toDto())), 'Task created');
}
```

Event + queued Listener (registered in DomainServiceProvider::boot)
```php
final class SendTaskNotification implements ShouldQueue { public function handle(TaskCreated $e): void {...} }
Event::listen(TaskCreated::class, SendTaskNotification::class);
```

Repository (only for complex query / Redis cache)
```php
$this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);   // or $bindings array
```

## Rules recap
- Controller: no if/else, no DB, no `$request->validate()`, no try/catch.
- Never pass raw arrays into Actions — DTOs only.
- Business-rule violations: throw a `DomainException` subclass; it renders the standard error JSON itself.
- Cookies/HTTP concerns stay in `app/Http` (see `App\Support\Http\RefreshTokenCookie`).
- Tests: `tests/Feature/<Module>/`; see `tests/Feature/Auth/AuthFlowTest.php`.
