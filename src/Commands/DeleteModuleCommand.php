<?php

namespace CommandModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DeleteModuleCommand extends Command
{
    protected $signature = 'delete:module {name} {--type=api} {--path=}';
    protected $description = 'Delete a module (web or api) with its Model, Controller, Requests, Resource, Service, Interface, Migration, routes, bindings, and views (for web)';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $type = strtolower((string) $this->option('type')) === 'web' ? 'web' : 'api';
        $snakePlural = Str::snake(Str::plural($name));

        $paths = [
            app_path("Models/{$name}.php"),
            $type === 'api' ? app_path("Http/Controllers/Api/{$name}Controller.php") : app_path("Http/Controllers/{$name}Controller.php"),
            app_path("Http/Requests/{$name}Request.php"),
            $type === 'api' ? app_path("Http/Resources/{$name}Resource.php") : null,
            app_path("Repositories/{$name}Repository.php"),
            app_path("Interfaces/{$name}RepositoryInterface.php"),
            app_path("Observers/{$name}Observer.php"),
            app_path("Policies/{$name}Policy.php"),
            app_path("Services/{$name}Service.php"),
            app_path("Interfaces/{$name}Interface.php"),
        ];

        foreach ($paths as $path) {
            if ($path === null) {
                continue;
            }
            if (File::exists($path)) {
                File::delete($path);
                $this->info("Deleted: {$path}");
            }
        }

        // 🗑️ لو فولدر Requests/{name} فاضي نحذفه
        $requestDir = app_path("Http/Requests/{$name}");
        if (File::isDirectory($requestDir) && count(File::files($requestDir)) === 0) {
            File::deleteDirectory($requestDir);
            $this->info("Deleted empty folder: {$requestDir}");
        }

        // 🗑️ حذف الميجريشن
        $migrations = File::glob(database_path("migrations/*create_{$snakePlural}_table.php"));
        foreach ($migrations as $migration) {
            File::delete($migration);
            $this->info("Deleted migration: {$migration}");
        }

        // 🗑️ حذف تسجيل ال Observer في AppServiceProvider
        $this->removeObserverRegistration($name);

        // 🗑️ حذف تسجيل ال Policy في AuthServiceProvider
        $this->removePolicyRegistration($name);

        // 🗑️ حذف الروت
        if ($type === 'api') {
            // remove both FQCN and short forms
            $this->removeRouteLine(base_path('routes/api.php'), "Route::resource('{$snakePlural}', \\App\\Http\\Controllers\\Api\\{$name}Controller::class);");
            $this->removeRouteLine(base_path('routes/api.php'), "Route::resource('{$snakePlural}', {$name}Controller::class);");
            // cleanup controller import if unused
            $this->cleanupControllerImport(
                base_path('routes/api.php'),
                'use App\\Http\\Controllers\\Api\\' . $name . 'Controller;',
                $name . 'Controller'
            );
        } else {
            $this->removeRouteLine(base_path('routes/web.php'), "Route::resource('{$snakePlural}', \\App\\Http\\Controllers\\{$name}Controller::class);");
            $this->removeRouteLine(base_path('routes/web.php'), "Route::resource('{$snakePlural}', {$name}Controller::class);");
            // cleanup controller import if unused
            $this->cleanupControllerImport(
                base_path('routes/web.php'),
                'use App\\Http\\Controllers\\' . $name . 'Controller;',
                $name . 'Controller'
            );
        }

        // 🗑️ حذف البايندينج
        $this->removeBinding($name);
        $this->removeRepoBinding($name);
        $this->removeModelAttributes($name);

        // 🗑️ حذف ال views لو ويب
        if ($type === 'web') {
            $viewDirName = Str::snake($name);
            $basePath = $this->option('path') ? trim($this->option('path'), '/') . '/' : '';
            if ($basePath !== '') {
                $viewsDir = resource_path('views/' . $basePath . $viewDirName);
                if (File::isDirectory($viewsDir)) {
                    File::deleteDirectory($viewsDir);
                    $this->info("Deleted views directory: {$viewsDir}");
                }
            } else {
                // Auto-detect and delete any matching directories under resources/views/**/{viewDirName}
                $candidates = $this->findViewDirectories(resource_path('views'), $viewDirName);
                foreach ($candidates as $dir) {
                    File::deleteDirectory($dir);
                    $this->info("Deleted views directory: {$dir}");
                }
            }
        }

        $this->info("❌ {$type} module {$name} deleted successfully!");
    }

    protected function removeRouteLine(string $routesPath, string $routeLine): void
    {
        if (!File::exists($routesPath)) {
            return;
        }
        $content = File::get($routesPath);
        $new = str_replace("\n{$routeLine}\n", "\n", $content);
        $new = str_replace($routeLine . "\n", "", $new);
        $new = str_replace("\n" . $routeLine, "", $new);
        if ($new !== $content) {
            File::put($routesPath, $new);
            $this->info("Removed route: {$routeLine}");
        }
    }

    protected function removeBinding(string $name): void
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if (!File::exists($providerPath)) {
            return;
        }
        $content = File::get($providerPath);
        $bindingLine = "$" . "this->app->bind(\\App\\Interfaces\\{$name}Interface::class, \\App\\Services\\{$name}Service::class);";
        if (strpos($content, $bindingLine) !== false) {
            $updated = str_replace($bindingLine, '', $content);
            $updated = $this->normalizeProviderRegister($updated);
            File::put($providerPath, $updated);
            $this->info("Removed binding for {$name} in AppServiceProvider.");
        }
    }

    protected function removeObserverRegistration(string $name): void
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if (!File::exists($providerPath)) {
            return;
        }
        $content = File::get($providerPath);
        $observeLine = "\\App\\Models\\{$name}::observe(\\App\\Observers\\{$name}Observer::class);";
        if (strpos($content, $observeLine) !== false) {
            $updated = str_replace($observeLine, '', $content);
            File::put($providerPath, $updated);
            $this->info("Removed observer registration for {$name}.");
        }
    }

    protected function removePolicyRegistration(string $name): void
    {
        $path = app_path('Providers/AuthServiceProvider.php');
        if (!File::exists($path)) {
            return;
        }
        $content = File::get($path);
        $mapping = "\\App\\Models\\{$name}::class => \\App\\Policies\\{$name}Policy::class,";
        if (strpos($content, $mapping) !== false) {
            $updated = str_replace($mapping, '', $content);
            File::put($path, $updated);
            $this->info("Removed policy registration for {$name}.");
        }
    }

    protected function removeModelAttributes(string $name): void
    {
        $modelPath = app_path("Models/{$name}.php");
        if (!File::exists($modelPath)) {
            return;
        }
        $content = File::get($modelPath);
        $imports = [
            "use App\\Policies\\{$name}Policy;",
            "use App\\Observers\\{$name}Observer;",
            'use Illuminate\\Database\\Eloquent\\Attributes\\UsePolicy;',
            'use Illuminate\\Database\\Eloquent\\Attributes\\ObservedBy;',
        ];
        foreach ($imports as $import) {
            $content = str_replace("\n{$import}\n", "\n", $content);
            $content = str_replace($import . "\n", "", $content);
            $content = str_replace("\n" . $import, "", $content);
        }
        $content = str_replace("#[UsePolicy({$name}Policy::class)]\n", "", $content);
        $content = str_replace("#[ObservedBy([{$name}Observer::class])]\n", "", $content);
        File::put($modelPath, $content);
        $this->info("Removed model attributes for {$name}.");
    }

    protected function removeRepoBinding(string $name): void
    {
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if (!File::exists($providerPath)) {
            return;
        }
        $content = File::get($providerPath);
        $bindingLine = "$" . "this->app->bind(\\App\\Interfaces\\{$name}RepositoryInterface::class, \\App\\Repositories\\{$name}Repository::class);";
        if (strpos($content, $bindingLine) !== false) {
            $updated = str_replace($bindingLine, '', $content);
            $updated = $this->normalizeProviderRegister($updated);
            File::put($providerPath, $updated);
            $this->info("Removed repository binding for {$name} in AppServiceProvider.");
        }
    }

    protected function normalizeProviderRegister(string $content): string
    {
        return preg_replace_callback(
            '/public function register\(\): void\s*\{([\s\S]*?)\}/',
            function ($m) {
                $body = $m[1];
                $body = preg_replace("/\n{2,}/", "\n", $body);
                return "public function register(): void{" . $body . "}";
            },
            $content,
            1
        );
    }

    protected function cleanupControllerImport(string $routesPath, string $controllerImport, string $controllerShort): void
    {
        if (!File::exists($routesPath)) {
            return;
        }
        $content = File::get($routesPath);
        // If the short controller name is still referenced in any route, keep the import
        if (strpos($content, $controllerShort . '::class') !== false) {
            return;
        }
        // Remove the exact import line if present
        $patterns = [
            "\n" . $controllerImport . "\n",
            $controllerImport . "\n",
            "\n" . $controllerImport,
        ];
        $updated = $content;
        foreach ($patterns as $p) {
            $updated = str_replace($p, "\n", $updated);
        }
        if ($updated !== $content) {
            File::put($routesPath, $updated);
            $this->info("Removed unused import: {$controllerImport}");
        }
    }

    /**
     * Find directories named $target under $base (recursive).
     * @return array<int, string>
     */
    protected function findViewDirectories(string $base, string $target): array
    {
        $found = [];
        if (!File::isDirectory($base)) {
            return $found;
        }
        foreach (File::directories($base) as $dir) {
            if (basename($dir) === $target) {
                $found[] = $dir;
            }
            // Recurse
            $found = array_merge($found, $this->findViewDirectories($dir, $target));
        }
        return $found;
    }
}
