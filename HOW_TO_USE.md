# كيف تستخدم الباكدج؟

## 🚀 للمطورين: خطوتين فقط

### 1️⃣ التثبيت
```bash
composer config repositories.command-module-generator vcs https://github.com/eng-mohamedemad-dev/command-module-generator.git
composer require eng-mohamedemad-dev/command-module-generator:dev-main
```

### 2️⃣ الاستخدام
```bash
# إنشاء موديول API
php artisan make:module Product --type=api

# إنشاء موديول Web
php artisan make:module Category --type=web

# حذف موديول
php artisan delete:module Product --type=api
```

---

## ✨ هيعمل إيه؟

**أمر واحد ينشئ:**
- ✅ Model + Migration
- ✅ Controller (CRUD كامل)
- ✅ Service (Business Logic)
- ✅ Interface
- ✅ Request (Validation)
- ✅ Policy (Authorization)
- ✅ Observer (Events)
- ✅ Resource (API) أو Views (Web)
- ✅ Routes (مسجلة تلقائياً!)
- ✅ Auto Binding في ServiceProvider

**كل ده في 3 ثواني! ⚡**

---

## 📚 لمزيد من التفاصيل

- [QUICK_START.md](QUICK_START.md) - أمثلة وشرح كامل
- [INSTALLATION.md](INSTALLATION.md) - طرق التثبيت المختلفة
- [README.md](README.md) - التوثيق الشامل

---

## 💡 أمثلة سريعة

```bash
# متجر إلكتروني
php artisan make:module Product --type=api --repo
php artisan make:module Order --type=api --repo
php artisan make:module Cart --type=api

# لوحة تحكم
php artisan make:module User --type=web --path=admin/users
php artisan make:module Post --type=web --path=admin/posts
```

---

**المتطلبات:** PHP 8.2+ | Laravel 11+

**رابط GitHub:** https://github.com/eng-mohamedemad-dev/command-module-generator
