# Laravel Module Generator Package# command-module-generator



**Smart Laravel Artisan module generator package** - Generates complete modules (Model, Controller, Service, Repository, Policy, Observer, Views, Routes) with full automation and best practices.### 📦 باكج توليد موديلات لارافيل ذكي وقابل للتخصيص الكامل



[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)---

[![Laravel](https://img.shields.io/badge/Laravel-11%2B%20%7C%2012%2B-red)](https://laravel.com)

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)## الفكرة باختصار

باستخدام أمر واحد فقط تقدر تولد:

---- Model

- Controller (API أو Web)

## 🌟 Features- Service

- Repository & Interface

- **Full Module Generation**: Create complete modules with Model, Controller, Service, Interface, Repository (optional), Policy, Observer, Views, and Resources- Request(s) (ملف أو ملفين)

- **API & Web Support**: Automatically distinguishes between API and Web modules- Resource

- **Smart Route Registration**: Auto-adds `Route::resource` with proper imports to routes files- Policy

- **Auto Binding**: Automatically binds interfaces to services/repositories in ServiceProvider- Observer

- **Model Attributes**: Uses modern PHP attributes for Policy and Observer registration- Views Blade (للوِب)

- **View Customization**: Support for custom view paths via `--path`مع ربط كل شيء ببعضه تلقائيًا + حذف كل ذلك بنفس السهولة.

- **Safe Overwriting**: Protects existing files unless `--force` is used

- **Complete Cleanup**: Delete command removes all traces (routes, views, bindings, attributes)---



---## التركيب السريع



## 📦 Installation1. أضف البكچ داخل مجلد packages في مشروعك

2. ثبتها يدوياً في composer لو لزم الأمر

Install the package via composer:3. فعّل الـ ServiceProvider داخل `config/app.php` او سيعمل AutoDiscovery:

```php

```bash'providers' => [

composer require eng-mohamedemad-dev/command-module-generator    ...

```    CommandModuleGenerator\CommandModuleGeneratorServiceProvider::class,

],

The package will auto-register its service provider.```

4. انشر ملف الكونفج لتخصيص الخيارات:

---```bash

php artisan vendor:publish --tag=command-module-generator-config

## 🚀 Usage```

5. (اختياري) انشر stubs لتعديل قوالبك:

### Generate a Module```bash

php artisan vendor:publish --tag=command-module-generator-stubs

#### Web Module```

```bash

php artisan make:module Car --type=web---

php artisan make:module Brand --type=web --path=admin/brand

```## أهم الميزات والخيارات الافتراضية

- تحكم في كل ما يتم توليده من خلال ملف `config/module-generator.php`:

#### API Module  - `default_type`: web/api

```bash  - `default_views_path`: مسار الواجهات للويب (افتراضي resources/views/{module})

php artisan make:module Invoice --type=api  - `make_repo`: توليد ريبوزيتوري؟

php artisan make:module Product --type=api --repo  - `make_resource`, `make_policy`, `make_observer`: فعّل/عطّل أي نوع

```  - `requests_mode`: split (يولّد ملفين) أو single (ملف واحد)

  - أسماء ملفات الريكويستات (store/update)

### Delete a Module

---

```bash

php artisan delete:module Car --type=web## أمثلة أوامر عملية

php artisan delete:module Invoice --type=api```bash

```php artisan make:module Car               # توليد موديل/خدمة/كنترولر... افتراضي web

php artisan make:module Invoice --type=api --repo   # توليد كل شيء كموديول API+ توليد repository

---php artisan make:module Ticket --path=admin/tickets # وضع الواجهات بمجلد معين

php artisan make:module Post --no-policy   # يعطّل توليد البوليصي فقط

## ⚙️ Optionsphp artisan make:module City --requests-mode=single # يولد ملف request واحد فقط



| Option | Description |php artisan delete:module Car     # يحذف كل ملفات موديول Car (كل ما تم توليده)

|--------|-------------|```

| `--type=web\|api` | Select between web or API module (default: `api`) |

| `--repo` | Generate Repository + RepositoryInterface and bind to service |- أي خيار لم ترسله بالأمر، سيعمل بالقيمة الموجودة في الكونفج افتراضيًا.

| `--path=custom` | Custom view folder path (for web modules) |

| `--force` | Allow overwriting existing service, repository, and interface files |---



---## تخصيص قوالب (stubs) التوليد

- تستطيع نشر stubs عشان تعدل أي قالب توليد:

## 📁 Generated Structure```bash

php artisan vendor:publish --tag=command-module-generator-stubs

When you run `php artisan make:module Car --type=api --repo`, the package generates:```

- غير في مجلد `stubs/command-module-generator` بحرية… كل توليد لاحق سيأخذ تعديلاتك!

```

app/---

├── Console/

├── Http/## حذف ما تم توليده (delete:module)

│   ├── Controllers/- يحذف جميع الملفات والعلاقات وكود الربط من AppServiceProvider.

│   │   └── Api/- يعطيك نظافة كاملة للموديول في خطوة واحدة (لا تحتاج تنظيف يدوي).

│   │       └── CarController.php        # API Controller with CRUD

│   ├── Requests/---

│   │   └── CarRequest.php               # Form Request validation

│   └── Resources/## أسئلة متكررة ❓

│       └── CarResource.php              # API Resource transformer

├── Interfaces/**س: لو غيرت قوالب stubs هل لازم أحذف البكج إذا حدثتها؟**

│   └── CarRepositoryInterface.php       # Repository contractج: أبدًا. قوالبك الشخصية لا تتأثر بتحديث البكج.

├── Models/

│   └── Car.php                          # Eloquent Model (with Policy/Observer attributes)**س: لو عدلت الكونفج فقط، هل تتغير القيم وقت أمر التوليد؟**

├── Observers/ج: نعم، أي توليد لاحق يأخذ القيم الجديدة فورًا.

│   └── CarObserver.php                  # Model Observer

├── Policies/**س: كيف أتأكد أن كل شيء يعمل؟**

│   └── CarPolicy.php                    # Authorization Policyج: جرب أوامر make:module مع اسم جديد + delete:module للاسم نفسه… راقب مجلدات app وresources/views وAppServiceProvider.

├── Repositories/

│   └── CarRepository.php                # Repository implementation---

└── Services/

    └── CarService.php                   # Business logic layer## Roadmap

- دعم مزيد من أنواع السوق/القوالب

database/migrations/- أوامر publish إضافية

└── xxxx_xx_xx_create_cars_table.php     # Migration file- تحسين فحص الأخطاء وتعريب الرسائل

- إضافة اختبارات واستعمالات جاهزة للمجتمع

routes/

└── api.php                              # Auto-registered Route::resource---

```

## Summary (EN)

### For Web Modules (--type=web)A smart highly-configurable module generator for Laravel. Generate, clean up, and customize all module layers with one command. Full flexibility for your workflow!


Additional files:
```
resources/views/
└── car/
    ├── index.blade.php
    ├── create.blade.php
    ├── edit.blade.php
    └── show.blade.php

routes/
└── web.php                              # Auto-registered Route::resource
```

---

## 🔧 How It Works

### Service Layer Pattern
The package implements the Service Layer pattern for clean separation of concerns:

```php
// Controller → Service → Model/Repository
class CarController extends Controller
{
    public function __construct(private CarService $service) {}

    public function index(Request $request)
    {
        $data = $this->service->list(15);
        return CarResource::collection($data);
    }
}
```

### Auto Binding
Services and repositories are automatically bound in `AppServiceProvider`:

```php
public function register(): void
{
    $this->app->bind(\App\Interfaces\CarInterface::class, \App\Services\CarService::class);
    $this->app->bind(\App\Interfaces\CarRepositoryInterface::class, \App\Repositories\CarRepository::class);
}
```

### Modern Attributes
Uses PHP 8 attributes for Policy and Observer registration:

```php
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[UsePolicy(CarPolicy::class)]
#[ObservedBy([CarObserver::class])]
class Car extends Model
{
    //
}
```

---

## 🎯 Best Practices

This package follows Laravel best practices:

- ✅ **Route Model Binding** for automatic model resolution
- ✅ **Form Request Validation** for clean controller code
- ✅ **Resource Pattern** for API transformations
- ✅ **Service Layer** for business logic separation
- ✅ **Repository Pattern** (optional) for data access abstraction
- ✅ **Policy** for authorization
- ✅ **Observer** for model events
- ✅ **tap() Helper** for fluent updates

---

## 📝 Examples

### Example 1: Simple API Module
```bash
php artisan make:module Product --type=api
```

Generates a complete API module with:
- ProductController (API CRUD)
- ProductService
- ProductInterface
- ProductRequest
- ProductResource
- ProductPolicy
- ProductObserver
- Product Model with attributes
- Migration
- Auto-registered API routes

### Example 2: Web Module with Custom Path
```bash
php artisan make:module Category --type=web --path=admin/categories
```

Generates web module with views in `resources/views/admin/categories/`

### Example 3: API Module with Repository
```bash
php artisan make:module Order --type=api --repo
```

Generates API module + Repository pattern implementation

---

## 🗑️ Delete Command

The delete command removes all generated files and cleans up:
- ✅ Model, Controller, Service, Repository, Interface
- ✅ Policy, Observer, Request, Resource
- ✅ Migration files
- ✅ Views (for web modules)
- ✅ Route registrations
- ✅ Service bindings in AppServiceProvider
- ✅ Model attributes (Policy/Observer imports and decorators)

```bash
php artisan delete:module Product --type=api
```

---

## 🌍 Internationalization

This package supports both English and Arabic:

### Arabic Commands
```bash
# توليد موديول
php artisan make:module منتج --type=api

# حذف موديول  
php artisan delete:module منتج --type=api
```

---

## 🔮 Roadmap

- [ ] Configuration file for customizing generated code
- [ ] Stub file publishing for custom templates
- [ ] GUI for module generation
- [ ] Seeders auto-generation
- [ ] ACL/Permissions integration
- [ ] Multi-language support in generated code
- [ ] Test file generation

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 👨‍💻 Author

**Mohamed Emad**  
Email: eng.mohamedemad.dev@gmail.com  
GitHub: [@eng-mohamedemad-dev](https://github.com/eng-mohamedemad-dev)

---

## ⭐ Support

If you find this package helpful, please give it a ⭐ on [GitHub](https://github.com/eng-mohamedemad-dev/command-module-generator)!

---

**Made with ❤️ for the Laravel Community**
