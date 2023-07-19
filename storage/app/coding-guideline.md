# Laravel & PHP conventions

This guideline provides a set of conventions about how to write Laravel & PHP at [Accredify](https://accredify.io/) so that the team can have the same standard and reference it when we write or review code. It does not include coding style conventions that can be automatically enforced by code styling tools (e.g. Laravel Pint).

---

# Follow Laravel standards

First and foremost, Laravel provides the most value when you **write things the way Laravel intended you to write**. If there's a documented way to achieve something, follow it. Whenever you do something differently, make sure you have a justification for why you didn’t follow the defaults.

# Static typing

Whenever possible type-hint class properties, method arguments, and return values.

This will greatly help static analysis tools (and humans) understand the code, and IDEs to provide auto-completion.

### Strict types

We do use `declare(strict_types=1);` by default. It will issue an error when an unexpected type of parameter is passed to a function. It helps us to catch some tricky bugs.

### Class properties

```php
class SomeClass
{
    // Good
    private int $variable;

    // Bad
    private $anotherVariable;
}
```

### Method arguments and return value

```php
// Good
public function getDocumentById(int $id): Document;

// Bad
public function getDocumentById($id);
```

### Nullable vs union types

Whenever possible use the short nullable notation of a type, instead of using a union of the type with `null`.

```php
// Good
private ?string $variable;

// Bad
private string | null $variable;
```

### Do not return an array, return a DTO

An associative array is unstructured and hard to know the shape of the object. You would have to inspect the array to check what properties the array has. To avoid this, instead of returning an associative array, return a [DTO](https://stitcher.io/blog/laravel-beyond-crud-02-working-with-data) (data transfer object) so that the caller of the method can easily understand the shape of the returned object with static types.

```php
// Good
use Accredify\Analytics\Domain\DataTransferObjects\RecommendedCourse;

public function getRecommendedCourse(int $userId): RecommendedCourse
{
    //

    return new RecommendedCourse(
        $course->id,
        $course->name,
        ...
    );
}

// Bad
public function getRecommendedCourse(int $userId): array
{
    //

    return [
        'course_id' => $course->id,
        'course_name' => $course->name,
        ...
    ];
}
```

# Comments

Comments should be avoided as much as possible by writing expressive code. If you do need to use a comment, the comment should tell the reader WHY not WHAT.

```php
// Good. It tells the reason why this code is needed.
// Due to max file name restrictions on modern OS, there needs to be a check for files with longer
// than 255 characters file names.
if (strlen($fileName) > 255) {
    $fileName = substr($fileName, 0, 255);
}

// Bad. It's obvious from the code itself.
// Set the content-type header
$headers = ['Content-Type' => 'application/json'];
```

Also, comments can be written on facts that cannot be derived **“quickly”** from the code itself.

```php
// Retrieve "ethereum" from "net=ethereum"
$net = 'net=ethereum';
$net = explode('=', $net)[1];
```

# PHPDoc

Don't use PHPDoc for methods that can be fully type hinted (unless you need a description).

```php
// Good
public function getDocumentById(int $id): Document
{
    //
}

// Bad. The description, argument, and return type are redundant.
/**
 * Get document by id.
 *
 * @param int $id
 * @return Document
 */
public function getDocumentById(int $id): Document
{
    //
}
```

Only add PHPDoc in the following situations.

- The description provides more context than the method signature itself.
- The method throws an exception.
- The argument or return type is type-hinted with [generics](https://phpstan.org/blog/generics-in-php-using-phpdocs) that PHPStan provides.
    
    Good example: reveal what your arrays and collections contain:
    

```php
/**
 * Get the top courses for a given issuer in terms of the number of documents issued.
 * 
 * @return Collection<CourseIssuance>
 * @throws AnalyticsApiConnectionException
 */
public function topCourses(int $issuerId, int $limit): Collection
{
    //
}

/**
 * @var array<string, string>
 */
protected $casts = [
    'share_status' => 'boolean',
    'status' => DocumentStatus::class,
];
```

# Naming

### **Do not abbreviate**

```php
// Good
foreach ($recipients as $recipient) {
    //
}

// Bad
foreach ($recipients as $r) {
    //
}

// Good
private Connection $connection;

// Bad
private Connection $con;
```

### Variables and methods should use camelCase

```php
// Good
public function generateBadge(string $badgeName): string;

// Bad
public function generate_badge(string $badge_name): string;
```

### Config name should use snake_case

```php
return [
    // Good
    'client_id' => env('SINGPASS_CLIENT_ID'),

    // Bad
    'clientId' => env('SINGPASS_CLIENT_ID'),
		'client-id' => env('SINGPASS_CLIENT_ID'),
];
```

### Database columns should use snake_case

```php
// Good
Schema::create('table_name', function (Blueprint $table) {
    $table->integer('user_id');
});

// Bad
Schema::create('table_name', function (Blueprint $table) {
    $table->integer('userId');
});
```

### Route middleware should use snake_case

```php
// Good
protected $routeMiddleware = [
    // ...
    'role_or_permission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
];

// Bad
protected $routeMiddleware = [
    // ...
    'roleOrPermission' => \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
];
```

Separate contexts by dots.

```php
protected $routeMiddleware = [
    // ...
    'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
];
```

### Enum should use PascalCase

```php
// Good
enum DocumentStatus {
    case Creating;
		case GeneratingFiles;
		case PendingConfirmation;
}

// Bad
enum DocumentStatus {
    case CREATING;
		case GENERATING_FILES;
		case PENDING_CONFIRMATION;
}
```

### Singular resource name

Controller, eloquent model, and JSON resource must use the singular resource name.

```php
// Good
class DocumentController extends Controller {}

// Bad
class DocumentsController extends Controller {}

// Good
class Document extends Model {}

// Bad
class Documents extends Model {}

// Good
class Document extends JsonResource {}

// Bad
class Documents extends JsonResource {}
```

### JSON resource attributes should use snake_case

```php
// Good
class Document extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'document_name' => $this->name,
            'issuer_name' => $this->issuer_name ?? null,
        ];
    }
}

// Bad
class Document extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'documentName' => $this->name,
            'issuerName' => $this->issuer_name ?? null,
        ];
    }
}
```

### Artisan command name should use kebab-case

Laravel’s default artisan commands are named with kebab-case as well.

```php
// Good
protected $signature = 'make:something-cool';

// Bad
protected $signature = 'make:somethingCool';
protected $signature = 'make:something_cool';
```

### An event name should tell what has happened

An event’s name should tell what has happened or is happening as clearly as possible.

Avoid using abstract names.

```php
// Good
class IssuingDocument {}
class DocumentIssued {}

// Bad
class DocumentErrorEvent {}
```

### An event listener's name should be action-oriented

Since an event listener is typically responsible for handling a certain action in response to an event, its name should reflect what it's expected to do upon the occurrence of the event.

```php
// Good
class SendShippingNotification {
    public function handle(OrderShipped $event): void
		{
				//
		}
}

// Bad
class OrderShippedListener {
    public function handle(OrderShipped $event): void
		{
				//
		}
}
```

# Constants

No magic numbers or magic values, use constants.

```php
// Good
public const MAX_LOGIN_ATTEMPT = 5;

public function remainingAttempts(Request $request): int
{
    return $this->limiter->remaining($this->throttleKey($request), self::MAX_LOGIN_ATTEMPT);
}

// Bad
public function remainingAttempts(Request $request): int
{
    return $this->limiter->remaining($this->throttleKey($request), 5); // What does 5 mean?
}
```

Use enums to define a set of constants.

# Constructor property promotion

Use [constructor property promotion](https://stitcher.io/blog/constructor-promotion-in-php-8) if all properties can be promoted.

```php
// Good
class MyClass {
    public function __construct(
        protected string $firstArgument,
        protected string $secondArgument,
    ) {}
}

// Bad
class MyClass {
    protected string $secondArgument;

    public function __construct(protected string $firstArgument, string $secondArgument)
    {
        $this->secondArgument = $secondArgument;
    }
}
```

# ****Type-casting****

When converting the type of variable, use type-casting instead of dedicated methods. Reason: [better performance](http://tonyshowoff.com/articles/casting-int-faster-than-intval-in-php/).

```php
// GOOD
$score = (int) '7';
$hasMadeAnyProgress = (bool) $this->score;

// BAD
$score = intval('7');
$hasMadeAnyProgress = boolval($this->score);
```

# Strings

When possible prefer string interpolation above `sprintf` and the `.` operator.

```php
// Good
$greeting = "Hi, I am {$name}.";

// Bad
$greeting = 'Hi, I am ' . $name . '.';
$greeting = sprintf('Hi, I am %s.', $name);
```

# Routing

### Use snake_case for route path

```php
// Good
Route::get('verifiable_pdfs', [VerifiablePdfController::class, 'index']);

// Bad
Route::get('verifiable-pdfs', [VerifiablePdfController::class, 'index']);
Route::get('verifiablePdfs', [VerifiablePdfController::class, 'index']);
```

### A route path should not start with `/`

A route URL should not start with `/` unless the URL would be an empty string.

```php
// Good
Route::get('/', [HomeController::class, 'index']);
Route::get('documents', [DocumentController::class, 'index']);

// Bad
Route::get('', [HomeController::class, 'index']);
Route::get('/documents', [DocumentController::class, 'index']);
```

### Use a noun for route path

Use a noun with a plural form instead of a verb. Action should be described by the HTTP method.

**Reason:** RESTful URIs should refer to a resource that is a thing (noun) instead of referring to an action (verb). Nouns have properties, whereas verbs do not. The REST architectural principle uses HTTP verbs to interact with resources.

```php
// Good
Route::post('document_files', [DocumentFileController::class, 'store']);
Route::post('documents/files', [DocumentFileController::class, 'store']);

// Bad
Route::post('documents/upload_file', [DocumentFileController::class, 'store']);
```

### Route parameters should use camelCase

```php
// Good
Route::get('issuers/{issuerId}/courses', [IssuerCourseController::class, 'index']);

// Bad
Route::get('issuers/{issuer_id}/courses', [IssuerCourseController::class, 'index']);
```

### Use query parameters for optional parameters

Use query parameters for optional parameters. Query parameters are used to sort/filter the resources.

Use path variables for mandatory parameters. Path variables are used to identify a specific resource.

```
// Good
https://example.com/posts?status=published
https://example.com/posts/1

// Bad
https://example.com/posts/status/published
https://example.com/posts?id=1
```

### Route model binding

Whenever possible use [route model binding](https://laravel.com/docs/9.x/routing#route-model-binding) to retrieve a model. It will automatically return a 404 response when the model is not found.

```php
// Good
Route::get('documents/{document}', [DocumentController::class, 'show']);

// Bad
Route::get('documents/{documentId}', [DocumentController::class, 'show']);
```

# Controller

### Default controller actions

Try to keep controllers simple and stick to the [default CRUD actions](https://laravel.com/docs/9.x/controllers#actions-handled-by-resource-controller) (`index`, `create`, `store`, `show`, `edit`, `update` and `destroy`).

Extract a new controller if you need other actions.

```php
// Good
class ProductCategoryController extends Controller
{
    public function index()
    {
        //
    }
}

// Bad
class ProductController extends Controller
{
    public function categories()
    {
        //
    }
}
```

[https://youtu.be/MF0jFKvS4SI?t=334](https://youtu.be/MF0jFKvS4SI?t=334)

[https://twitter.com/taylorotwell/status/1651593413140287488?s=20](https://twitter.com/taylorotwell/status/1651593413140287488?s=20)

### Single Action Controllers

If a controller action is particularly complex and not RESTful, you might find it convenient to dedicate an entire controller class to that single action. To accomplish this, you may define a single `__invoke` method within the controller.

```php
class ProvisionServer extends Controller
{
    /**
     * Provision a new web server.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        // ...
    }
}
```

### Use JSON resources for JSON transformation

Whenever possible use [JSON resource](https://laravel.com/docs/9.x/eloquent-resources) to transform an object into a JSON response.

```php
// Good
class DocumentController extends Controller
{
    public function show(Document $document): JsonResource
    {
				//

        return new DocumentResource($document);
    }
}

// Bad
class DocumentController extends Controller
{
    public function show(Document $document): JsonResponse
    {
        //

        return new JsonResponse([
            'data' => [
                'id' => $document->id,
                'document_name' => $document->name,
                'status' => $document->status?->slug(),
                //
            ]
        ]);
    }
}
```

### Use ResourceCollection only when the collection has additional attributes

Use the singular `JsonResource` with the `collection` method whenever possible. Create a dedicated `ResourceCollection` only when the collection of resources has additional attributes that are not part of the single resource.

```php
// This is preferred whenever possible
class DocumentController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $documents = Document::paginate(10);

        return DocumentResource::collection($docuemnts);
    }
}

// Do this only when the collection of resources has additional attributes
class DocumentController extends Controller
{
    public function index(Request $request): DocumentCollection
    {
        $documents = Document::paginate(10);

        return new DocumentCollection($docuemnts);
    }
}
```

### Use constants for HTTP status code

Do not hard-code the HTTP status code.

```php
// Good
return new JsonResponse(null, Response::HTTP_NO_CONTENT);

// Bad
return new JsonResponse(null, 204);
```

# Validation

### Form request validation

Use a [form request class](https://laravel.com/docs/9.x/validation#form-request-validation) to validate requests instead of validating in the controller in order to separate concerns and keep the controller clean.

```php
public function store(StorePostRequest $request)
{
    // The incoming request is valid...
 
    // Retrieve the validated input data...
    $validated = $request->validated();
}
```

### Custom validation rules

Create a [custom validation rule class](https://laravel.com/docs/9.x/validation#using-rule-objects) if the validation rule is reused more than once or the validation logic is too complex for a closure-based rule.

If you only need the functionality of a custom rule once throughout the application, you may use a closure instead of a rule object if the validation logic is simple enough.

```php
public function rules()
{
    return [
        'title' => [
            'required',
            'max:255',
            function ($attribute, $value, $fail) {
                if ($value === 'foo') {
                    $fail('The '.$attribute.' is invalid.');
                }
            },
        ],
    ];
}
```

### Use array notation to define multiple rules

Avoid using `|` as separator for validation rules, always use array notation. Using an array notation will make it easier to apply custom rule classes to a field.

```php
// Good
public function rules()
{
    return [
        'issuer_id' => ['bail', 'required', 'integer'],
    ];
}

// Bad
public function rules()
{
    return [
        'issuer_id' => 'bail|required|integer',
    ];
}
```

# Authorization

Use [policy](https://laravel.com/docs/9.x/authorization#creating-policies) to authorize user actions against a particular model or resource.

```php
class PostPolicy
{
    /**
     * Determine if the given post can be updated by the user.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
```

Attach the policy to your route using the `can` method.

```php
Route::put('/posts/{post}', [PostContoller::class, 'update'])->can('update', 'post');
```

You may use the form request’s [authorize()](https://laravel.com/docs/9.x/validation#authorizing-form-requests) method to authorize user action that is not against a particular model or resource. It is meant for one-off authorizations that are not reused in any other parts of the application. Use [middleware](https://laravel.com/docs/9.x/middleware) if the authorization logic will be reused for multiple routes.

# Accessors & Mutators

Define model accessors and mutators with a single method. Do not use the old way where accessors and mutators are defined as two separate methods.

```php
// Good
class User extends Model
{
		/**
     * @return Attribute<string, string>
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => strtolower($value),
        );
    }
}

// Bad
class User extends Model
{
    public function getFirstNameAttribute(string $value): string
    {
        return ucfirst($value);
    }

    public function setFirstNameAttribute(string $value): void
    {
        $this->attributes['first_name'] = strtolower($value);
    }
}
```

# Action and Service

Extract complex business logic from controller to action or service classes to separate concerns and avoid fat controllers.

### Action

Use an [action class](https://stitcher.io/blog/laravel-beyond-crud-03-actions) when the class is responsible for only one action. An action class only has one public method `__invoke()`.

```php
class VerifyDocument
{
    public function __invoke(string $json): Verification
    {
        //
    }
}

// The action class can be invoked like a function
class ExampleController extends Controller
{
    public function store(Request $request, VerifyDocument $verifyDocument): JsonResponse
    {
        $result = $verifyDocument($request->file('file')->get());

        //
    }
}
```

### Service

Use a service class when the class will have a collection of **related** methods.

```php
class PaymentService
{
    public function pay(int $amount, string $currency): void
    {
        //
    }

		public function getTransaction(string $transactionId): Transaction
    {
        //
    }

    public function refund(string $transactionId): void
    {
        //
    }

    // and more methods
}
```

# Migration

### Anonymous class

Laravel generates migrations as an [anonymous class](https://laravel-news.com/laravel-anonymous-migrations) by default if you generate a migration with `php artisan make:migration` command. Do not create a migration with a class name to avoid class name collisions.

```php
// Good
return new class extends Migration
{
		//
};

// Bad
class CreateUsersTable extends Migration
{
		//
}
```

### One table per migration

Create/alter only one table in one migration file. In case we want to rollback a change to one table, it would rollback multiple tables if the migration file touches multiple tables.

# References

This page is inspired by the following guidelines and books.

- [Laravel conventions - Interaction Design Foundation](https://handbook.interaction-design.org/development/library/back-end/conventions--laravel.html)
- [PHP conventions - Interaction Design Foundation](https://handbook.interaction-design.org/development/library/back-end/conventions--php.html)
- [Clean Code PHP - Interaction Design Foundation](https://handbook.interaction-design.org/development/library/back-end/clean-code-php.html)
- [Laravel & PHP Artisanal baked code - Spatie](https://spatie.be/guidelines/laravel-php)
- [The Art of Readable Code](https://www.oreilly.com/library/view/the-art-of/9781449318482/)