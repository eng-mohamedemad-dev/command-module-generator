<?php

namespace CommandModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name} {--type=api} {--repo} {--path=} {--force}';
    protected $description = 'Create a new module (web or api) with Model, Controller, Migration, Service, Requests, Policy, and optional Resource/Repository; binds and routes. Use --path for web views location, --force to overwrite files.';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $type = strtolower((string) $this->option('type')) === 'web' ? 'web' : 'api';
        $snakePlural = Str::snake(Str::plural($name));

        // 1. Model
        $this->call('make:model', [
            'name' => "{$name}"
        ]);
        // Apply model attributes for Policy and Observer
        $this->applyModelAttributes($name);

        // 2. Migration
        $this->call('make:migration', [
            'name' => "create_" . Str::snake(Str::plural($name)) . "_table"
        ]);

        // Observer
        $this->call('make:observer', [
            'name' => "{$name}Observer",
            '--model' => "App\\Models\\{$name}",
        ]);

        // Policy (always create)
        $this->call('make:policy', [
            'name' => "{$name}Policy",
            '--model' => "App\\Models\\{$name}",
        ]);

        // 3. Requests - Create custom Request that extends BaseRequest (BEFORE Controller)
        $this->createRequest($name);

        // 4. Controller
        if ($type === 'api') {
        $this->call('make:controller', [
            'name' => "Api/{$name}Controller",
            '--api' => true,
        ]);
        } else {
            $this->call('make:controller', [
                'name' => "{$name}Controller",
                '--resource' => true,
            ]);
        }

        // 5. Resource (api only)
        if ($type === 'api') {
        $this->call('make:resource', [
            'name' => "{$name}Resource"
        ]);
        }

        // Repository (optional)
        if ($this->option('repo')) {
            $repoInterfacePath = app_path("Interfaces/{$name}RepositoryInterface.php");
            if (!File::exists($repoInterfacePath)) {
                File::ensureDirectoryExists(app_path('Interfaces'));
                File::put($repoInterfacePath, $this->buildRepoInterfaceContent($name));
                $this->info("Interface [App/Interfaces/{$name}RepositoryInterface.php] created successfully.");
            }

            $repoPath = app_path("Repositories/{$name}Repository.php");
            if (!File::exists($repoPath)) {
                File::ensureDirectoryExists(app_path('Repositories'));
                File::put($repoPath, $this->buildRepoContent($name));
                $this->info("Repository [App/Repositories/{$name}Repository.php] created successfully.");
            }
        }

        // Service (write CRUD implementation)
        $servicePath = app_path("Services/{$name}Service.php");
        File::ensureDirectoryExists(app_path('Services'));
        if (!File::exists($servicePath) || $this->option('force')) {
            File::put($servicePath, $this->renderTemplate($this->getServiceTemplate($this->option('repo')), [
                '{{Name}}' => $name,
                '{{NameLower}}' => Str::snake($name),
            ]));
            $this->info("Service [App/Services/{$name}Service.php] written successfully.");
        } else {
            $this->line("Service exists, skipping (use --force to overwrite): App/Services/{$name}Service.php");
        }

        // 7. Service Interface (only when no repo)
        if (!$this->option('repo')) {
            $interfacePath = app_path("Interfaces/{$name}Interface.php");
            if (!File::exists($interfacePath) || $this->option('force')) {
                if (!File::exists($interfacePath)) {
                    File::ensureDirectoryExists(app_path('Interfaces'));
                }
                File::put($interfacePath, $this->renderTemplate($this->getServiceInterfaceTemplate(), [
                    '{{Name}}' => $name,
                    '{{NameLower}}' => Str::snake($name),
                ]));
                $this->info("Interface [App/Interfaces/{$name}Interface.php] written successfully.");
            } else {
                $this->line("Interface exists, skipping (use --force to overwrite): App/Interfaces/{$name}Interface.php");
            }
        }

        // Bind repository if requested
        if ($this->option('repo')) {
            $this->bindRepositoryInterface($name);
        }

        // Write Controller CRUD code (service + request)
        $viewPath = $type === 'web' ? ($this->option('path') ?: null) : null;
        $this->writeControllerCrud($name, $type, $viewPath);

        // Bind service to interface only when no repo
        if (!$this->option('repo')) {
            $this->bindServiceInterface($name);
        }

        // Register observer
        $this->registerObserver($name);

        // Register policy mapping
        $this->registerPolicy($name);

        // Write Route::resource and create views for web
        if ($type === 'api') {
            $this->appendApiRoute($name, $snakePlural);
        } else {
            $this->appendWebRoute($name, $snakePlural);
            $viewPath = $this->option('path') ?: null;
            $this->createWebViews($name, $viewPath);
        }

        $this->info("✅ {$type} module {$name} created successfully!");
    }

    protected function renderTemplate(string $template, array $vars): string
    {
        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    protected function getRepoTemplate(): string
    {
        return <<<'PHP'
<?php

namespace App\Repositories;

use App\Interfaces\{{Name}}RepositoryInterface;
use App\Models\{{Name}};

class {{Name}}Repository implements {{Name}}RepositoryInterface
{
    public function list(int $perPage = 15)
    {
        return {{Name}}::paginate($perPage);
    }

    public function find({{Name}} ${{NameLower}})
    {
        return ${{NameLower}};
    }

    public function create(array $data)
    {
        return {{Name}}::create($data);
    }

    public function update({{Name}} ${{NameLower}}, array $data)
    {
        return tap(${{NameLower}})->update($data);
    }

    public function delete({{Name}} ${{NameLower}})
    {
        return (bool) ${{NameLower}}->delete();
    }
}
PHP;
    }

    protected function getServiceTemplate(bool $useRepo): string
    {
        if ($useRepo) {
            return <<<'PHP'
<?php

namespace App\Services;

use App\Models\{{Name}};
use App\Interfaces\{{Name}}RepositoryInterface;

class {{Name}}Service
{
    public function __construct(private {{Name}}RepositoryInterface $repository) {}

    public function list(int $perPage = 15)
    {
        return $this->repository->list($perPage);
    }

    public function find({{Name}} ${{NameLower}})
    {
        return ${{NameLower}};
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update({{Name}} ${{NameLower}}, array $data)
    {
        return $this->repository->update(${{NameLower}}, $data);
    }

    public function delete({{Name}} ${{NameLower}})
    {
        return $this->repository->delete(${{NameLower}});
    }
}
PHP;
        }

        return <<<'PHP'
<?php

namespace App\Services;

use App\Interfaces\{{Name}}Interface;
use App\Models\{{Name}};

class {{Name}}Service implements {{Name}}Interface
{
    public function list(int $perPage = 15)
    {
        return {{Name}}::paginate($perPage);
    }

    public function find({{Name}} ${{NameLower}})
    {
        return ${{NameLower}};
    }

    public function create(array $data)
    {
        return {{Name}}::create($data);
    }

    public function update({{Name}} ${{NameLower}}, array $data)
    {
        return tap(${{NameLower}})->update($data);
    }

    public function delete({{Name}} ${{NameLower}})
    {
        return (bool) ${{NameLower}}->delete();
    }
}
PHP;
    }

    protected function getServiceInterfaceTemplate(): string
    {
        return <<<'PHP'
<?php

namespace App\Interfaces;

use App\Models\{{Name}};

interface {{Name}}Interface
{
    public function list(int $perPage = 15);
    public function find({{Name}} ${{NameLower}});
    public function create(array $data);
    public function update({{Name}} ${{NameLower}}, array $data);
    public function delete({{Name}} ${{NameLower}});
}
PHP;
    }

    protected function buildRepoInterfaceContent(string $name): string
    {
        $tpl = <<<'PHP'
<?php

namespace App\Interfaces;

use App\Models\{{Name}};

interface {{Name}}RepositoryInterface
{
    public function list(int $perPage = 15);
    public function find({{Name}} ${{NameLower}});
    public function create(array $data);
    public function update({{Name}} ${{NameLower}}, array $data);
    public function delete({{Name}} ${{NameLower}});
}
PHP;
        return $this->renderTemplate($tpl, ['{{Name}}' => $name, '{{NameLower}}' => Str::snake($name)]);
    }

    protected function buildRepoContent(string $name): string
    {
        return $this->renderTemplate($this->getRepoTemplate(), ['{{Name}}' => $name, '{{NameLower}}' => Str::snake($name)]);
    }

    protected function buildServiceContent(string $name, bool $useRepo): string
    {
        return $this->renderTemplate($this->getServiceTemplate($useRepo), [
            '{{Name}}' => $name,
            '{{NameLower}}' => Str::snake($name),
        ]);
    }

    protected function buildServiceInterfaceContent(string $name): string
    {
        return $this->renderTemplate($this->getServiceInterfaceTemplate(), [
            '{{Name}}' => $name,
            '{{NameLower}}' => Str::snake($name),
        ]);
    }

    protected function getControllerTemplate(bool $isApi): string
    {
        if ($isApi) {
            return <<<'PHP'
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\{{Name}}Request;
use App\Http\Resources\{{Name}}Resource;
use App\Models\{{Name}};
use App\Services\{{Name}}Service;
use Illuminate\Http\Request;

class {{Name}}Controller extends Controller
{
    public function __construct(private {{Name}}Service $service) {}

    public function index(Request $request)
    {
        $data = $this->service->list(15);
        return {{Name}}Resource::collection($data);
    }

    public function show({{Name}} ${{NameLower}})
    {
        return new {{Name}}Resource(${{NameLower}});
    }

    public function store({{Name}}Request $request)
    {
        $model = $this->service->create($request->validated());
        return new {{Name}}Resource($model);
    }

    public function update({{Name}}Request $request, {{Name}} ${{NameLower}})
    {
        $model = $this->service->update(${{NameLower}}, $request->validated());
        return new {{Name}}Resource($model);
    }

    public function destroy({{Name}} ${{NameLower}})
    {
        $this->service->delete(${{NameLower}});
        return response()->json(null, 204);
    }
}
PHP;
        }

        return <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\{{Name}}Request;
use App\Models\{{Name}};
use App\Services\{{Name}}Service;
use Illuminate\Http\Request;

class {{Name}}Controller extends Controller
{
    public function __construct(private {{Name}}Service $service) {}

    public function index(Request $request)
    {
        $data = $this->service->list(15);
        return view('{{viewPath}}.index', compact('data'));
    }

    public function create()
    {
        return view('{{viewPath}}.create');
    }

    public function store({{Name}}Request $request)
    {
        $model = $this->service->create($request->validated());
        return redirect()->route('{{snakePlural}}.index')->with('success', '{{Name}} created');
    }

    public function show({{Name}} ${{NameLower}})
    {
        return view('{{viewPath}}.show', compact('{{NameLower}}'));
    }

    public function edit({{Name}} ${{NameLower}})
    {
        return view('{{viewPath}}.edit', compact('{{NameLower}}'));
    }

    public function update({{Name}}Request $request, {{Name}} ${{NameLower}})
    {
        $model = $this->service->update(${{NameLower}}, $request->validated());
        return redirect()->route('{{snakePlural}}.index')->with('success', '{{Name}} updated');
    }

    public function destroy({{Name}} ${{NameLower}})
    {
        $this->service->delete(${{NameLower}});
        return redirect()->route('{{snakePlural}}.index')->with('success', '{{Name}} deleted');
    }
}
PHP;
    }

    protected function bindServiceInterface(string $name): void
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if (!File::exists($providerPath)) {
            $this->warn('AppServiceProvider.php not found. Skipping binding.');
            return;
        }

        $content = File::get($providerPath);
        $bindingLine = "$" . "this->app->bind(\\App\\Interfaces\\{$name}Interface::class, \\App\\Services\\{$name}Service::class);";

        if (strpos($content, $bindingLine) !== false) {
            $this->line("Binding already exists for {$name}.");
            return;
        }

        $updated = preg_replace_callback(
            '/public function register\(\): void\s*\{([\s\S]*?)\}/',
            function ($m) use ($bindingLine) {
                $inside = rtrim($m[1]);
                if ($inside !== '') {
                    $inside .= "\n"; // only one newline between statements
                }
                return "public function register(): void{" . "\n" . $inside . "        " . $bindingLine . "\n    }";
            },
            $content,
            1,
            $count
        );

        if ($count === 0) {
            $this->warn('Could not inject binding into register(). Please add manually.');
            return;
        }

        File::put($providerPath, $this->normalizeProviderRegister($updated));
        $this->info("Bound {$name}Interface to {$name}Service in AppServiceProvider.");
    }

    protected function appendWebRoute(string $name, string $snakePlural): void
    {
        $routesPath = base_path('routes/web.php');
        if (!File::exists($routesPath)) {
            File::put($routesPath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n");
        }

        $controllerFqcn = "App\\Http\\Controllers\\{$name}Controller";
        $controllerImport = "use " . str_replace('\\\\', '\\', $controllerFqcn) . ";";
        $controllerShort = "{$name}Controller";
        $routeLine = "Route::resource('{$snakePlural}', {$controllerShort}::class);";
        $content = File::get($routesPath);
        if (strpos($content, 'use Illuminate\\Support\\Facades\\Route;') === false) {
            $content = preg_replace('/<\\?php\\s*/', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n", $content, 1);
        }
        if (strpos($content, $controllerImport) === false) {
            $content = preg_replace('/use Illuminate\\\\Support\\\\Facades\\\\Route;\s*/', "$0\n{$controllerImport}\n", $content, 1);
        }
        if (strpos($content, $routeLine) === false) {
            $content .= "\n{$routeLine}\n";
        }
        File::put($routesPath, $this->normalizeRouteFileContent($content));
        $this->info("Added web route: {$routeLine}");
    }

    protected function appendApiRoute(string $name, string $snakePlural): void
    {
        $routesPath = base_path('routes/api.php');
        if (!File::exists($routesPath)) {
            File::put($routesPath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n");
        }

        $controllerFqcn = "App\\Http\\Controllers\\Api\\{$name}Controller";
        $controllerImport = "use " . str_replace('\\\\', '\\', $controllerFqcn) . ";";
        $controllerShort = "{$name}Controller";
        $routeLine = "Route::resource('{$snakePlural}', {$controllerShort}::class);";
        $content = File::get($routesPath);
        if (strpos($content, 'use Illuminate\\Support\\Facades\\Route;') === false) {
            $content = preg_replace('/<\\?php\\s*/', "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n", $content, 1);
        }
        if (strpos($content, $controllerImport) === false) {
            $content = preg_replace('/use Illuminate\\\\Support\\\\Facades\\\\Route;\s*/', "$0\n{$controllerImport}\n", $content, 1);
        }
        if (strpos($content, $routeLine) === false) {
            $content .= "\n{$routeLine}\n";
        }
        File::put($routesPath, $this->normalizeRouteFileContent($content));
        $this->info("Added api route: {$routeLine}");
    }

    protected function createWebViews(string $name, ?string $path = null): void
    {
        $viewDirName = Str::snake($name);
        $basePath = $path ? trim($path, '/') . '/' : '';
        $viewsDir = resource_path('views/' . $basePath . $viewDirName);
        File::ensureDirectoryExists($viewsDir);
        $views = ['index', 'create', 'edit', 'show'];
        foreach ($views as $view) {
            $viewPath = $viewsDir . '/' . $view . '.blade.php';
            if (!File::exists($viewPath)) {
                File::put($viewPath, "<h1>{$name} {$view}</h1>\n");
                $relativePath = $basePath . $viewDirName;
                $this->info("View created: resources/views/{$relativePath}/{$view}.blade.php");
            }
        }
    }

    protected function registerObserver(string $name): void {}

    protected function ensureAuthServiceProviderExists(): void
    {
        // always ensure for policy mapping
        $path = app_path('Providers/AuthServiceProvider.php');
        if (File::exists($path)) {
            return;
        }
        File::ensureDirectoryExists(app_path('Providers'));
        $providerContent = <<<'PHP'
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
PHP;
        File::put($path, $providerContent);

        // register in bootstrap/providers.php if missing
        $bootstrap = base_path('bootstrap/providers.php');
        if (File::exists($bootstrap)) {
            $providers = File::get($bootstrap);
            if (strpos($providers, 'App\\Providers\\AuthServiceProvider::class') === false) {
                $providers = preg_replace('/return \[\s*/', "return [\n    App\\Providers\\AuthServiceProvider::class,\n", $providers, 1);
                File::put($bootstrap, $providers);
            }
        }
    }

    protected function registerPolicy(string $name): void {}

    protected function bindRepositoryInterface(string $name): void
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if (!File::exists($providerPath)) {
            return;
        }
        $content = File::get($providerPath);
        $bindingLine = "$" . "this->app->bind(\\App\\Interfaces\\{$name}RepositoryInterface::class, \\App\\Repositories\\{$name}Repository::class);";
        if (strpos($content, $bindingLine) !== false) {
            return;
        }
        $updated = preg_replace_callback(
            '/public function register\(\): void\s*\{([\s\S]*?)\}/',
            function ($m) use ($bindingLine) {
                $inside = rtrim($m[1]);
                if ($inside !== '') {
                    $inside .= "\n";
                }
                return "public function register(): void{" . "\n" . $inside . "        " . $bindingLine . "\n    }";
            },
            $content,
            1,
            $count
        );
        if ($count > 0) {
            File::put($providerPath, $this->normalizeProviderRegister($updated));
        }
    }

    protected function writeControllerCrud(string $name, string $type, ?string $viewPath = null): void
    {
        $isApi = $type === 'api';
        $controllerPath = $isApi ? app_path("Http/Controllers/Api/{$name}Controller.php") : app_path("Http/Controllers/{$name}Controller.php");
        if (!File::exists($controllerPath)) {
            return;
        }
        $snakePlural = Str::snake(Str::plural($name));
        $nameLower = Str::snake($name);
        $viewBasePath = $viewPath ? trim($viewPath, '/') . '.' . $nameLower : $nameLower;
        $content = $this->renderTemplate($this->getControllerTemplate($isApi), [
            '{{Name}}' => $name,
            '{{NameLower}}' => $nameLower,
            '{{snakePlural}}' => $snakePlural,
            '{{viewPath}}' => $viewBasePath,
        ]);
        File::put($controllerPath, $content);
    }

    protected function applyModelAttributes(string $name): void
    {
        $modelPath = app_path("Models/{$name}.php");
        if (!File::exists($modelPath)) {
            return;
        }
        $content = File::get($modelPath);

        // Ensure imports
        $imports = [
            "use App\\Policies\\{$name}Policy;",
            "use App\\Observers\\{$name}Observer;",
            "use Illuminate\\Database\\Eloquent\\Attributes\\UsePolicy;",
            "use Illuminate\\Database\\Eloquent\\Attributes\\ObservedBy;",
        ];
        foreach ($imports as $import) {
            if (strpos($content, $import) === false) {
                $content = preg_replace('/namespace\\s+App\\\\Models;\s*/', "namespace App\\Models;\n\n{$import}\n", $content, 1);
            }
        }

        $attributesBlock = "#[UsePolicy({$name}Policy::class)]\n#[ObservedBy([{$name}Observer::class])]\n";
        if (strpos($content, 'UsePolicy(') === false && strpos($content, 'ObservedBy(') === false) {
            $content = preg_replace('/(class\s+' . $name . '\s+extends\s+[^\{]+\{)/', $attributesBlock . "$1", $content, 1);
        }

        File::put($modelPath, $content);
        $this->info("Applied model attributes for Policy and Observer to App/Models/{$name}.php");
    }

    protected function normalizeRouteFileContent(string $content): string
    {
        // Collapse 3+ consecutive newlines into just 2
        $content = preg_replace("/\n{3,}/", "\n\n", $content);
        // Ensure a single blank line between imports and the rest
        return $content;
    }

    protected function normalizeProviderRegister(string $content): string
    {
        // Only target the register() body, collapse 2+ blank lines to a single blank line
        return preg_replace_callback(
            '/public function register\(\): void\s*\{([\s\S]*?)\}/',
            function ($m) {
                $body = $m[1];
                // Collapse 2+ newlines
                $body = preg_replace("/\n{2,}/", "\n", $body);
                return "public function register(): void{" . $body . "}";
            },
            $content,
            1
        );
    }

    /**
     * Create Request file from stub that extends BaseRequest
     */
    protected function createRequest(string $name): void
    {
        $requestPath = app_path("Http/Requests/{$name}Request.php");
        
        if (File::exists($requestPath) && !$this->option('force')) {
            $this->line("Request exists, skipping: {$name}Request");
            return;
        }

        $stubPath = __DIR__ . '/../../stubs/Request.single.stub';
        
        if (!File::exists($stubPath)) {
            $this->error("Request stub not found at: {$stubPath}");
            return;
        }

        $stub = File::get($stubPath);
        
        $content = str_replace(
            ['DummyNamespace', 'DummyClass'],
            ['App\Http\Requests', "{$name}Request"],
            $stub
        );

        File::ensureDirectoryExists(app_path('Http/Requests'));
        File::put($requestPath, $content);
        
        $this->info("Request [app/Http/Requests/{$name}Request.php] created successfully.");
    }
}

