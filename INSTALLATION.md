# دليل استخدام الباكدج في مشاريع أخرى

## 🚀 الطريقة 1: من Packagist (موصى بها)

### التثبيت
```bash
composer require eng-mohamedemad-dev/command-module-generator
```

### الاستخدام
```bash
# إنشاء موديول API
php artisan make:module Product --type=api

# إنشاء موديول Web
php artisan make:module Category --type=web

# مع Repository Pattern
php artisan make:module Order --type=api --repo

# حذف موديول
php artisan delete:module Product --type=api
```

---

## 📥 الطريقة 2: من GitHub (إذا Packagist لم يعمل)

في ملف `composer.json` للمشروع الجديد، أضف:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/eng-mohamedemad-dev/command-module-generator.git"
        }
    ],
    "require": {
        "eng-mohamedemad-dev/command-module-generator": "dev-main"
    }
}
```

ثم شغل:
```bash
composer install
```

---

## 🔧 الطريقة 3: نسخ الباكدج محلياً

### 1. انسخ الباكدج للمشروع الجديد
```bash
# اذهب للمشروع الجديد
cd /path/to/new-laravel-project

# أنشئ مجلد packages
mkdir -p packages

# انسخ الباكدج
cp -r /home/mohamed/Downloads/project/Senior/first_topic/packages/command-module-generator ./packages/
```

### 2. عدل composer.json في المشروع الجديد
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./packages/command-module-generator",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "eng-mohamedemad-dev/command-module-generator": "@dev"
    }
}
```

### 3. ثبت الباكدج
```bash
composer update eng-mohamedemad-dev/command-module-generator
```

---

## 📝 مثال عملي كامل

### في مشروع Laravel جديد:

```bash
# 1. أنشئ مشروع Laravel جديد
composer create-project laravel/laravel my-new-project
cd my-new-project

# 2. ثبت الباكدج
composer require eng-mohamedemad-dev/command-module-generator

# 3. استخدم الأوامر
php artisan make:module Product --type=api
php artisan make:module Category --type=web --path=admin/categories
php artisan make:module Order --type=api --repo

# 4. تأكد من الملفات
php artisan list | grep module
```

---

## 🌐 استخدام عبر الإنترنت

عندما ينشر الباكدج على Packagist بنجاح، يمكن لأي مطور في العالم استخدامه:

```bash
composer require eng-mohamedemad-dev/command-module-generator
```

---

## 🔄 تحديث الباكدج

### إذا كان من Packagist:
```bash
composer update eng-mohamedemad-dev/command-module-generator
```

### إذا كان من GitHub:
```bash
composer update eng-mohamedemad-dev/command-module-generator
# سيحمل آخر نسخة من main branch
```

---

## 📋 متطلبات التشغيل

- PHP >= 8.2
- Laravel 11.x أو 12.x
- Composer

---

## ✅ التحقق من التثبيت

```bash
# 1. تأكد أن الأوامر ظاهرة
php artisan list | grep module

# 2. اعرض help
php artisan make:module --help

# 3. جرب إنشاء موديول تجريبي
php artisan make:module Test --type=api
php artisan delete:module Test --type=api
```

---

## 🎯 نصائح

1. **استخدم من Packagist** عندما يكون متاحاً (أسهل وأسرع)
2. **استخدم من GitHub** للحصول على آخر التحديثات
3. **انسخ محلياً** فقط للتطوير والتعديل على الباكدج نفسه

---

## 📞 في حالة المشاكل

### المشكلة: "Package not found"
**الحل:**
```bash
# جرب من GitHub
composer config repositories.command-module-generator vcs https://github.com/eng-mohamedemad-dev/command-module-generator.git
composer require eng-mohamedemad-dev/command-module-generator:dev-main
```

### المشكلة: "Class not found"
**الحل:**
```bash
# أعد بناء autoload
composer dump-autoload
php artisan clear-compiled
php artisan config:clear
```

---

## 🎉 أمثلة الاستخدام

```bash
# API Module بسيط
php artisan make:module Product --type=api

# Web Module مع views في مسار مخصص
php artisan make:module Brand --type=web --path=admin/brands

# API Module مع Repository Pattern
php artisan make:module Invoice --type=api --repo

# استبدال service موجود
php artisan make:module Product --type=api --force
```
