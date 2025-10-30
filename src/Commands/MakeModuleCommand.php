<?php

namespace CommandModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : اسم الموديول}
        {--type= : نوع التوليد web/api}
        {--repo : إنشاء repository}
        {--path= : مسار views للويب}
        {--force : السماح بالاستبدال}
        {--no-resource : تعطيل resource}
        {--no-policy : تعطيل policy}
        {--no-observer : تعطيل observer}
        {--requests-mode= : وضع الريكوستات split/single}
    ';
    protected $description = 'توليد جميع الملفات والعلاقات الخاصة بأي موديول مع الاعتماد على ملف الكونفج وقابلية تخصيص كاملة';

    public function handle()
    {
        $config = config('module-generator');
        $name = $this->argument('name');
        $type = $this->option('type') ?? $config['default_type'];
        $requestsMode = $this->option('requests-mode') ?? $config['requests_mode'];
        $force = $this->option('force') ?? false;
        $stubPath = base_path('packages/command-module-generator/stubs');
        $requestBasePath = app_path('Http/Requests/');
        $namespace = 'App\\Http\\Requests';
        // سوي اسماء الملفات والمسارات
        if ($requestsMode === 'split') {
            $storeName = $name . $config['store_request_name'];
            $updateName = $name . $config['update_request_name'];
            $subDir = $name;
            $dir = $requestBasePath . $subDir;
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            // Store
            $pathStore = "$dir/{$storeName}.php";
            $contents = str_replace([
                'DummyNamespace', 'DummyClass'
            ], ["$namespace\\$subDir", $storeName], file_get_contents("$stubPath/Request.store.stub"));
            if ($force || !file_exists($pathStore)) file_put_contents($pathStore, $contents);
            // Update
            $pathUpdate = "$dir/{$updateName}.php";
            $contents = str_replace([
                'DummyNamespace', 'DummyClass'
            ], ["$namespace\\$subDir", $updateName], file_get_contents("$stubPath/Request.update.stub"));
            if ($force || !file_exists($pathUpdate)) file_put_contents($pathUpdate, $contents);
            $this->info("[command-module-generator] تم إنشاء Request ملفين: $subDir/{$storeName}.php و $subDir/{$updateName}.php");
        } else {
            $singleName = $name . 'Request';
            $dir = $requestBasePath;
            $pathSingle = "$dir/{$singleName}.php";
            $contents = str_replace([
                'DummyNamespace', 'DummyClass'
            ], [$namespace, $singleName], file_get_contents("$stubPath/Request.single.stub"));
            if ($force || !file_exists($pathSingle)) file_put_contents($pathSingle, $contents);
            $this->info("[command-module-generator] تم إنشاء Request ملف واحد: {$singleName}.php");
        }
        // -------- SERVICE & INTERFACE --------
        $servicesPath = app_path('Services/');
        $interfacesPath = app_path('Interfaces/');
        if (!is_dir($servicesPath)) mkdir($servicesPath, 0777, true);
        if (!is_dir($interfacesPath)) mkdir($interfacesPath, 0777, true);
        $serviceName = $name . 'Service';
        $servicePath = $servicesPath . $serviceName . '.php';
        $interfaceName = $name . 'Interface';
        $interfacePath = $interfacesPath . $interfaceName . '.php';
        $stubPath = base_path('packages/command-module-generator/stubs');
        $namespaceService = 'App\\Services';
        $namespaceInterface = 'App\\Interfaces';
        $makeRepo = $this->option('repo') ?? $config['make_repo'];
        $force = $this->option('force') ?? false;

        // نولّد Interface فقط لو repo غير مفعّل
        if (!$makeRepo) {
            $interfaceContent = str_replace([
                'DummyNamespace', 'DummyClass'
            ], [$namespaceInterface, $interfaceName], file_get_contents("$stubPath/ServiceInterface.stub"));
            if ($force || !file_exists($interfacePath)) {
                file_put_contents($interfacePath, $interfaceContent);
                $this->info("[command-module-generator] تم إنشاء Interface: $interfaceName");
            }
        }

        // Service دائماً بالنيمسبيس وربطه بالإنترفيس (أو الريبو لو موجود)
        $implements = (!$makeRepo) ? $interfaceName : '';
        $serviceContent = str_replace([
            'DummyNamespace', 'DummyClass', 'DummyInterface', 'DummyModule'],
            [$namespaceService, $serviceName, $implements, $name],
            file_get_contents("$stubPath/Service.stub")
        );
        if ($force || !file_exists($servicePath)) {
            file_put_contents($servicePath, $serviceContent);
            $this->info("[command-module-generator] تم إنشاء Service: $serviceName");
        }

        // تحديث AppServiceProvider لربط الإنترفيس بالسيرفس تلقائيًا
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if (file_exists($providerPath)) {
            $providerContent = file_get_contents($providerPath);
            $binding = "        app()->bind(\\App\\Interfaces\\$interfaceName::class, \\App\\Services\\$serviceName::class);\n";
            if (!$makeRepo && strpos($providerContent, $binding) === false) {
                // أضف الربط قبل نهاية ميثود register
                $providerContent = preg_replace(
                    '/(public function register\(\)\s*\{)([^}]*)/s',
                    "$1$2$binding",
                    $providerContent
                );
                file_put_contents($providerPath, $providerContent);
                $this->info("[command-module-generator] تم تحديث AppServiceProvider لربط interface/service");
            }
        }
        // -------- REPO & INTERFACE --------
        $repoName = $name.'Repository';
        $repoPath = app_path('Repositories/'.$repoName.'.php');
        $repoInterfaceName = $name.'RepositoryInterface';
        $repoInterfacePath = app_path('Interfaces/'.$repoInterfaceName.'.php');
        $repoNS = 'App\\Repositories';
        $repoINamespace = 'App\\Interfaces';
        if ($makeRepo) {
            if (!is_dir(app_path('Repositories/'))) mkdir(app_path('Repositories/'), 0777, true);
            // RepositoryInterface
            $repoIContent = str_replace([
                'DummyNamespace', 'DummyClass'
            ], [$repoINamespace, $repoInterfaceName], file_get_contents("$stubPath/RepositoryInterface.stub"));
            if ($force || !file_exists($repoInterfacePath)) {
                file_put_contents($repoInterfacePath, $repoIContent);
                $this->info("[command-module-generator] RepositoryInterface تم إنشاؤها: $repoInterfaceName");
            }
            // Repository
            $repoContent = str_replace([
                'DummyNamespace','DummyClass','DummyInterface'],
                [$repoNS, $repoName, $repoInterfaceName],
                file_get_contents("$stubPath/Repository.stub"));
            if ($force || !file_exists($repoPath)) {
                file_put_contents($repoPath, $repoContent);
                $this->info("[command-module-generator] Repository تم إنشاؤها: $repoName");
            }
            // ربط الrepository-interface بالـ repository ف AppServiceProvider
            $providerPath = app_path('Providers/AppServiceProvider.php');
            if (file_exists($providerPath)) {
                $providerContent = file_get_contents($providerPath);
                $binding = "        app()->bind(\\App\\Interfaces\\$repoInterfaceName::class, \\App\\Repositories\\$repoName::class);\n";
                if (strpos($providerContent, $binding) === false) {
                    $providerContent = preg_replace('/(public function register\(\)\s*\{)([^}]*)/s', "$1$2$binding", $providerContent);
                    file_put_contents($providerPath, $providerContent);
                    $this->info("[command-module-generator] تم تحديث AppServiceProvider لbind repository/interface");
                }
            }
        }
        // -------- MODEL --------
        $modelsPath = app_path('Models/');
        $modelName = $name;
        $modelPath = $modelsPath . $modelName . '.php';
        $modelStub = file_get_contents("$stubPath/Model.stub");
        $modelStub = str_replace([
            'DummyNamespace', 'DummyClass'
        ], [
            'App\\Models', $modelName
        ], $modelStub);
        if ($force || !file_exists($modelPath)) {
            file_put_contents($modelPath, $modelStub);
            $this->info("[command-module-generator] تم إنشاء Model: $modelName");
        }

        // -------- RESOURCE (api فقط أو لو مفعّل في الكونفج) --------
        $resourceActive = !($this->option('no-resource') ?? false) && ($config['make_resource'] || $type === 'api');
        if ($resourceActive) {
            $resourceName = $name . 'Resource';
            $resourcePath = app_path('Http/Resources/' . $resourceName . '.php');
            if (!is_dir(app_path('Http/Resources/'))) mkdir(app_path('Http/Resources/'), 0777, true);
            $resourceStub = file_get_contents("$stubPath/Resource.stub");
            $resourceStub = str_replace([
                'DummyNamespace', 'DummyClass'], ['App\\Http\\Resources', $resourceName], $resourceStub);
            if ($force || !file_exists($resourcePath)) {
                file_put_contents($resourcePath, $resourceStub);
                $this->info("[command-module-generator] تم إنشاء Resource: $resourceName");
            }
        }
        // -------- POLICY --------
        $policyActive = !($this->option('no-policy') ?? false) && $config['make_policy'];
        if ($policyActive) {
            $policyName = $name . 'Policy';
            $policyPath = app_path('Policies/' . $policyName . '.php');
            if (!is_dir(app_path('Policies/'))) mkdir(app_path('Policies/'), 0777, true);
            $policyStub = file_get_contents("$stubPath/Policy.stub");
            $policyStub = str_replace([
                'DummyNamespace', 'DummyClass', 'DummyModel'], [
                    'App\\Policies', $policyName, $name
            ], $policyStub);
            if ($force || !file_exists($policyPath)) {
                file_put_contents($policyPath, $policyStub);
                $this->info("[command-module-generator] تم إنشاء Policy: $policyName");
            }
        }
        // -------- OBSERVER --------
        $observerActive = !($this->option('no-observer') ?? false) && $config['make_observer'];
        if ($observerActive) {
            $observerName = $name . 'Observer';
            $observerPath = app_path('Observers/' . $observerName . '.php');
            if (!is_dir(app_path('Observers/'))) mkdir(app_path('Observers/'), 0777, true);
            $observerStub = file_get_contents("$stubPath/Observer.stub");
            $observerStub = str_replace([
                'DummyNamespace', 'DummyClass', 'DummyModel'], [
                    'App\\Observers', $observerName, $name
            ], $observerStub);
            if ($force || !file_exists($observerPath)) {
                file_put_contents($observerPath, $observerStub);
                $this->info("[command-module-generator] تم إنشاء Observer: $observerName");
            }
        }
        // -------- CONTROLLER --------
        $controllersPath = app_path('Http/Controllers/');
        $controllerName = $name . 'Controller';
        $namespace = 'App\\Http\\Controllers';
        $stubFile = $type === 'api' ? 'Controller.api.stub' : 'Controller.web.stub';
        $stubFullPath = "$stubPath/$stubFile";
        $controllerPath = $controllersPath . ($type === 'api' ? 'Api/' : '') . $controllerName . '.php';
        if (!is_dir(dirname($controllerPath))) mkdir(dirname($controllerPath), 0777, true);
        // بناء الاستبدالات الاساسية
        $replace = [
            // كنترولر
            'DummyNamespace' => ($type === 'api' ? $namespace . '\\Api' : $namespace),
            'DummyClass' => $controllerName,
            // Service
            'DummyServiceNs' => 'App\\Services',
            'DummyService' => $name.'Service',
            // Resource (API فقط)
            'DummyResourceNs' => 'App\\Http\\Resources',
            'DummyResource' => $name.'Resource',
            // Requests
            'DummyRequestStoreNs' => 'App\\Http\\Requests'.($type==='api' ? '\\'.$name:''),
            'DummyRequestUpdateNs' => 'App\\Http\\Requests'.($type==='api' ? '\\'.$name:''),
            'DummyStoreRequest' => ($config['requests_mode']==='split' ? $name.$config['store_request_name'] : $name.'Request'),
            'DummyUpdateRequest' => ($config['requests_mode']==='split' ? $name.$config['update_request_name'] : $name.'Request'),
            // Model
            'DummyModelNs' => 'App\\Models',
            'DummyModel' => $name,
            // Blade (web فقط)
            'DummyViewsFolder' => strtolower($name),
            'DummyRouteName' => strtolower($name),
        ];
        $contents = file_get_contents($stubFullPath);
        foreach ($replace as $search => $with) {
            $contents = str_replace($search, $with, $contents);
        }
        if ($force || !file_exists($controllerPath)) {
            file_put_contents($controllerPath, $contents);
            $this->info("[command-module-generator] تم إنشاء Controller: $controllerName");
        }
        // -------- VIEWS (web فقط) --------
        if ($type === 'web') {
            $viewsFolderRaw = $this->option('path') ?? $config['default_views_path'] ?? '';
            $viewsFolder = $viewsFolderRaw !== '' ? $viewsFolderRaw : strtolower($name);
            $viewsBasePath = resource_path('views/' . $viewsFolder);
            if (!is_dir($viewsBasePath)) mkdir($viewsBasePath, 0777, true);
            $stubFiles = [
                'index' => 'View.index.stub',
                'create' => 'View.create.stub',
                'edit' => 'View.edit.stub',
                'show' => 'View.show.stub',
            ];
            foreach ($stubFiles as $short => $stubF) {
                $viewPath = "$viewsBasePath/$short.blade.php";
                $stub = file_get_contents("$stubPath/$stubF");
                $snake = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
                $stub = str_replace('{{ snake_case_model }}', $snake, $stub);
                if ($force || !file_exists($viewPath)) {
                    file_put_contents($viewPath, $stub);
                }
            }
            $this->info("[command-module-generator] تم إنشاء Blade views: index/create/edit/show");
        }
    }
}
