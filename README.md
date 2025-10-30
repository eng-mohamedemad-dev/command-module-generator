# command-module-generator

### 📦 باكج توليد موديلات لارافيل ذكي وقابل للتخصيص الكامل

---

## الفكرة باختصار
باستخدام أمر واحد فقط تقدر تولد:
- Model
- Controller (API أو Web)
- Service
- Repository & Interface
- Request(s) (ملف أو ملفين)
- Resource
- Policy
- Observer
- Views Blade (للوِب)
مع ربط كل شيء ببعضه تلقائيًا + حذف كل ذلك بنفس السهولة.

---

## التركيب السريع

1. أضف البكچ داخل مجلد packages في مشروعك
2. ثبتها يدوياً في composer لو لزم الأمر
3. فعّل الـ ServiceProvider داخل `config/app.php` او سيعمل AutoDiscovery:
```php
'providers' => [
    ...
    CommandModuleGenerator\CommandModuleGeneratorServiceProvider::class,
],
```
4. انشر ملف الكونفج لتخصيص الخيارات:
```bash
php artisan vendor:publish --tag=command-module-generator-config
```
5. (اختياري) انشر stubs لتعديل قوالبك:
```bash
php artisan vendor:publish --tag=command-module-generator-stubs
```

---

## أهم الميزات والخيارات الافتراضية
- تحكم في كل ما يتم توليده من خلال ملف `config/module-generator.php`:
  - `default_type`: web/api
  - `default_views_path`: مسار الواجهات للويب (افتراضي resources/views/{module})
  - `make_repo`: توليد ريبوزيتوري؟
  - `make_resource`, `make_policy`, `make_observer`: فعّل/عطّل أي نوع
  - `requests_mode`: split (يولّد ملفين) أو single (ملف واحد)
  - أسماء ملفات الريكويستات (store/update)

---

## أمثلة أوامر عملية
```bash
php artisan make:module Car               # توليد موديل/خدمة/كنترولر... افتراضي web
php artisan make:module Invoice --type=api --repo   # توليد كل شيء كموديول API+ توليد repository
php artisan make:module Ticket --path=admin/tickets # وضع الواجهات بمجلد معين
php artisan make:module Post --no-policy   # يعطّل توليد البوليصي فقط
php artisan make:module City --requests-mode=single # يولد ملف request واحد فقط

php artisan delete:module Car     # يحذف كل ملفات موديول Car (كل ما تم توليده)
```

- أي خيار لم ترسله بالأمر، سيعمل بالقيمة الموجودة في الكونفج افتراضيًا.

---

## تخصيص قوالب (stubs) التوليد
- تستطيع نشر stubs عشان تعدل أي قالب توليد:
```bash
php artisan vendor:publish --tag=command-module-generator-stubs
```
- غير في مجلد `stubs/command-module-generator` بحرية… كل توليد لاحق سيأخذ تعديلاتك!

---

## حذف ما تم توليده (delete:module)
- يحذف جميع الملفات والعلاقات وكود الربط من AppServiceProvider.
- يعطيك نظافة كاملة للموديول في خطوة واحدة (لا تحتاج تنظيف يدوي).

---

## أسئلة متكررة ❓

**س: لو غيرت قوالب stubs هل لازم أحذف البكج إذا حدثتها؟**
ج: أبدًا. قوالبك الشخصية لا تتأثر بتحديث البكج.

**س: لو عدلت الكونفج فقط، هل تتغير القيم وقت أمر التوليد؟**
ج: نعم، أي توليد لاحق يأخذ القيم الجديدة فورًا.

**س: كيف أتأكد أن كل شيء يعمل؟**
ج: جرب أوامر make:module مع اسم جديد + delete:module للاسم نفسه… راقب مجلدات app وresources/views وAppServiceProvider.

---

## Roadmap
- دعم مزيد من أنواع السوق/القوالب
- أوامر publish إضافية
- تحسين فحص الأخطاء وتعريب الرسائل
- إضافة اختبارات واستعمالات جاهزة للمجتمع

---

## Summary (EN)
A smart highly-configurable module generator for Laravel. Generate, clean up, and customize all module layers with one command. Full flexibility for your workflow!
