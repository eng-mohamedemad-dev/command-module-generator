<?php

namespace CommandModuleGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class DeleteModuleCommand extends Command
{
    protected $signature = 'delete:module {name : اسم الموديول}
        {--type= : نوع الحذف (web/api)}
        {--path= : مسار views}
    ';
    protected $description = 'حذف جميع ملفات وعلاقات الموديول والربط الداخلي حسب الكونفج والأوبشنز';

    public function handle()
    {
        $config = config('module-generator');
        $name = $this->argument('name');
        $type = $this->option('type') ?? $config['default_type'];
        $viewsPath = $this->option('path') ?? $config['default_views_path'];
        $fs = new Filesystem();
        // Controllers
        $controllerName = $name . 'Controller.php';
        $cPath = app_path('Http/Controllers/'.($type==='api'?'Api/':'').$controllerName);
        if ($fs->exists($cPath)) $fs->delete($cPath);
        // Model
        $modelPath = app_path('Models/'.$name.'.php');
        if ($fs->exists($modelPath)) $fs->delete($modelPath);
        // Resource
        $resourceName = $name.'Resource.php';
        $resourcePath = app_path('Http/Resources/'.$resourceName);
        if ($fs->exists($resourcePath)) $fs->delete($resourcePath);
        // Policy
        $policyName = $name.'Policy.php';
        $policyPath = app_path('Policies/'.$policyName);
        if ($fs->exists($policyPath)) $fs->delete($policyPath);
        // Observer
        $observerName = $name.'Observer.php';
        $observerPath = app_path('Observers/'.$observerName);
        if ($fs->exists($observerPath)) $fs->delete($observerPath);
        // Views
        if ($type==='web') {
            $viewsFolder = $viewsPath !== '' ? $viewsPath : strtolower($name);
            $viewsBasePath = resource_path('views/'.$viewsFolder);
            if ($fs->isDirectory($viewsBasePath)) $fs->deleteDirectory($viewsBasePath);
        }
        // Service & Interface
        $serviceName = $name.'Service.php';
        $interfaceName = $name.'Interface.php';
        $servicePath = app_path('Services/'.$serviceName);
        $interfacePath = app_path('Interfaces/'.$interfaceName);
        if ($fs->exists($servicePath)) $fs->delete($servicePath);
        if ($fs->exists($interfacePath)) $fs->delete($interfacePath);
        // Repository & RepositoryInterface
        $repoName = $name.'Repository.php';
        $repoIName = $name.'RepositoryInterface.php';
        $repoPath = app_path('Repositories/'.$repoName);
        $repoIPath = app_path('Interfaces/'.$repoIName);
        if ($fs->exists($repoPath)) $fs->delete($repoPath);
        if ($fs->exists($repoIPath)) $fs->delete($repoIPath);
        // Requests (split/single)
        $requestsMode = $config['requests_mode'];
        $reqBase = app_path('Http/Requests/');
        if ($requestsMode==='split') {
            $subDir = $name;
            $storeFile = $name.$config['store_request_name'].'.php';
            $updateFile = $name.$config['update_request_name'].'.php';
            $reqDir = $reqBase.$subDir;
            if ($fs->exists($reqDir.'/'.$storeFile)) $fs->delete($reqDir.'/'.$storeFile);
            if ($fs->exists($reqDir.'/'.$updateFile)) $fs->delete($reqDir.'/'.$updateFile);
            if ($fs->isDirectory($reqDir) && count($fs->files($reqDir))===0) $fs->deleteDirectory($reqDir);
        } else {
            $singleFile = $name.'Request.php';
            if ($fs->exists($reqBase.$singleFile)) $fs->delete($reqBase.$singleFile);
        }
        // حذف الربط من AppServiceProvider
        $providerPath = app_path('Providers/AppServiceProvider.php');
        if ($fs->exists($providerPath)) {
            $providerContent = file_get_contents($providerPath);
            $patterns = [
                "app()->bind(\\App\\Interfaces\\$interfaceName::class, \\App\\Services\\$serviceName::class);",
                "app()->bind(\\App\\Interfaces\\$repoIName::class, \\App\\Repositories\\$repoName::class);"
            ];
            foreach($patterns as $pat) {
                $providerContent = str_replace($pat."\n", '', $providerContent);
            }
            file_put_contents($providerPath, $providerContent);
        }
        $this->info("[command-module-generator] تم حذف $name بكل الملحقات type=$type / views_path=$viewsPath");
    }
}
