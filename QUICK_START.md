# 📦 Laravel Module Generator - دليل الاستخدام السريع

## للمطورين الذين يريدون استخدام الباكدج

### ⚡ التثبيت السريع (من GitHub)

في مشروع Laravel الخاص بك:

```bash
# الطريقة 1: تثبيت مباشر من GitHub
composer config repositories.command-module-generator vcs https://github.com/eng-mohamedemad-dev/command-module-generator.git
composer require eng-mohamedemad-dev/command-module-generator:dev-main
```

أو

```bash
# الطريقة 2: من Packagist (بعد التحديث)
composer require eng-mohamedemad-dev/command-module-generator
```

---

## 🎯 الاستخدام

### إنشاء موديول API كامل:
```bash
php artisan make:module Product --type=api
```

**ينشئ:**
- ✅ Model مع Policy و Observer attributes
- ✅ Controller (API) مع CRUD كامل
- ✅ Service Layer للـ business logic
- ✅ Interface للـ Service
- ✅ Request للـ validation
- ✅ Resource للـ API responses
- ✅ Policy للـ authorization
- ✅ Observer للـ model events
- ✅ Migration
- ✅ Routes مسجلة تلقائياً في `routes/api.php`
- ✅ Service binding في `AppServiceProvider`

---

### إنشاء موديول Web:
```bash
php artisan make:module Category --type=web
```

**ينشئ نفس الملفات + Views:**
- ✅ `resources/views/category/index.blade.php`
- ✅ `resources/views/category/create.blade.php`
- ✅ `resources/views/category/edit.blade.php`
- ✅ `resources/views/category/show.blade.php`

---

### مع Repository Pattern:
```bash
php artisan make:module Order --type=api --repo
```

**ينشئ أيضاً:**
- ✅ Repository
- ✅ RepositoryInterface
- ✅ Auto-binding للـ Repository

---

### Views في مسار مخصص:
```bash
php artisan make:module Brand --type=web --path=admin/brands
```

Views ستكون في: `resources/views/admin/brands/`

---

### حذف موديول (ينظف كل شيء):
```bash
php artisan delete:module Product --type=api
```

**يحذف:**
- ✅ كل الملفات (Model, Controller, Service, etc.)
- ✅ Migration
- ✅ Routes من ملفات الـ routes
- ✅ Service bindings من AppServiceProvider
- ✅ Model attributes (Policy/Observer imports)
- ✅ Views (للـ web modules)

---

## 📋 كل الخيارات

```bash
php artisan make:module {name} [options]

Options:
  --type=api|web     نوع الموديول (default: api)
  --repo             إنشاء Repository Pattern
  --path=path        مسار مخصص للـ views (web only)
  --force            استبدال الملفات الموجودة
```

---

## 🎨 أمثلة واقعية

### مثال 1: نظام متجر إلكتروني
```bash
php artisan make:module Product --type=api --repo
php artisan make:module Category --type=api
php artisan make:module Order --type=api --repo
php artisan make:module Cart --type=api
```

### مثال 2: لوحة تحكم Admin
```bash
php artisan make:module User --type=web --path=admin/users
php artisan make:module Role --type=web --path=admin/roles
php artisan make:module Permission --type=web --path=admin/permissions
```

### مثال 3: Blog System
```bash
php artisan make:module Post --type=web --repo
php artisan make:module Comment --type=api
php artisan make:module Tag --type=api
```

---

## 🔧 متطلبات التشغيل

- PHP >= 8.2
- Laravel 11.x أو 12.x
- Composer

---

## 📖 التوثيق الكامل

- **GitHub:** https://github.com/eng-mohamedemad-dev/command-module-generator
- **Packagist:** https://packagist.org/packages/eng-mohamedemad-dev/command-module-generator

---

## ⭐ إذا أعجبك الباكدج

- اعمل Star على GitHub: https://github.com/eng-mohamedemad-dev/command-module-generator
- شاركه مع زملائك المطورين

---

## 🐛 مشاكل أو اقتراحات؟

افتح Issue على GitHub: https://github.com/eng-mohamedemad-dev/command-module-generator/issues

---

## 📝 License

MIT License - استخدمه بحرية في مشاريعك!

---

**Made with ❤️ for Laravel Community**
