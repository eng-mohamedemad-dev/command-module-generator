# خطوات نشر الباكدج (Package Publishing Guide)

## 🚀 نشر الباكدج على GitHub

### الخطوة 1: إنشاء Repository جديد على GitHub
1. اذهب إلى https://github.com/new
2. اسم الـ Repo: `command-module-generator`
3. الوصف: `Smart Laravel artisan module generator package`
4. اجعله Public
5. **لا تضف** README أو LICENSE أو .gitignore (موجودين بالفعل)
6. اضغط "Create repository"

### الخطوة 2: ربط المشروع المحلي بـ GitHub
```bash
cd /home/mohamed/Downloads/project/Senior/first_topic/packages/command-module-generator

# إضافة GitHub كـ remote
git remote add origin https://github.com/eng-mohamedemad-dev/command-module-generator.git

# رفع الكود
git push -u origin main
```

---

## 📦 نشر الباكدج على Packagist

### الخطوة 1: التسجيل على Packagist
1. اذهب إلى https://packagist.org/
2. سجل حساب جديد (أو سجل دخول)

### الخطوة 2: إضافة الباكدج
1. اضغط "Submit" من القائمة العلوية
2. في خانة "Repository URL" ضع:
   ```
   https://github.com/eng-mohamedemad-dev/command-module-generator
   ```
3. اضغط "Check"
4. إذا كان كل شيء صحيح، اضغط "Submit"

### الخطوة 3: إعداد Auto-Update (اختياري)
لكي يتحدث الباكدج تلقائياً عند كل push:
1. من صفحة الباكدج على Packagist، انسخ الـ webhook URL
2. اذهب إلى Settings → Webhooks في GitHub repo
3. أضف webhook جديد بالـ URL المنسوخ

---

## 🏷️ إنشاء Release (نسخة)

عندما تريد نشر نسخة جديدة:

```bash
# إنشاء tag
git tag -a v1.0.0 -m "Release version 1.0.0 - Initial release"

# رفع الـ tag
git push origin v1.0.0
```

سيظهر تلقائياً على Packagist كنسخة `1.0.0`

---

## 📥 تثبيت الباكدج

بعد النشر، يمكن لأي شخص تثبيت الباكدج بـ:

```bash
composer require eng-mohamedemad-dev/command-module-generator
```

---

## 🔄 تحديث الباكدج

عندما تريد نشر تحديثات:

```bash
# عمل التعديلات
# ثم commit
git add .
git commit -m "وصف التعديل"
git push

# إنشاء version جديد (اختياري)
git tag -a v1.1.0 -m "Release version 1.1.0 - Added new features"
git push origin v1.1.0
```

---

## ✅ Checklist قبل النشر

- [x] composer.json صحيح ويحتوي على كل المعلومات
- [x] README.md شامل ويحتوي على أمثلة
- [x] LICENSE موجود
- [x] .gitignore موجود
- [x] كل الكود يعمل بدون أخطاء
- [x] الـ namespace صحيح (`CommandModuleGenerator\`)
- [x] Service Provider مسجل في composer.json
- [x] كل ملفات stubs موجودة
- [ ] اختبار الباكدج في مشروع نظيف

---

## 🧪 اختبار الباكدج محلياً قبل النشر

### الطريقة 1: استخدام composer local repository

في مشروع Laravel جديد، عدل `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/home/mohamed/Downloads/project/Senior/first_topic/packages/command-module-generator"
        }
    ],
    "require": {
        "eng-mohamedemad-dev/command-module-generator": "@dev"
    }
}
```

ثم:
```bash
composer update
php artisan list  # تأكد أن الأوامر ظاهرة
php artisan make:module TestModule --type=api
```

### الطريقة 2: تثبيت مباشر

```bash
cd /path/to/new/laravel/project
composer require eng-mohamedemad-dev/command-module-generator:@dev
```

---

## 📊 إحصائيات الباكدج

بعد النشر يمكنك متابعة:
- عدد التحميلات على https://packagist.org/packages/eng-mohamedemad-dev/command-module-generator
- Stars و Issues على GitHub

---

## 🌟 تحسينات مستقبلية

- [ ] إضافة Tests (PHPUnit/Pest)
- [ ] CI/CD مع GitHub Actions
- [ ] تغطية الكود (Code Coverage)
- [ ] نشر على Laravel News
- [ ] إضافة badges للـ README

---

## 📞 دعم

إذا واجهت مشاكل:
1. تأكد من صحة composer.json
2. تأكد من رفع كل الملفات على GitHub
3. تحقق من logs على Packagist
4. راجع [Packagist Docs](https://packagist.org/about)
