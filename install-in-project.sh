#!/bin/bash

# سكريبت لتثبيت الباكدج في مشروع Laravel جديد
# الاستخدام: ./install-in-project.sh /path/to/laravel/project

if [ -z "$1" ]; then
    echo "❌ يرجى تحديد مسار مشروع Laravel"
    echo "الاستخدام: ./install-in-project.sh /path/to/laravel/project"
    exit 1
fi

PROJECT_PATH=$1
PACKAGE_PATH="/home/mohamed/Downloads/project/Senior/first_topic/packages/command-module-generator"

if [ ! -d "$PROJECT_PATH" ]; then
    echo "❌ المشروع غير موجود: $PROJECT_PATH"
    exit 1
fi

if [ ! -f "$PROJECT_PATH/composer.json" ]; then
    echo "❌ هذا ليس مشروع Laravel (composer.json غير موجود)"
    exit 1
fi

echo "📦 جاري تثبيت command-module-generator في المشروع..."
echo "المشروع: $PROJECT_PATH"
echo ""

# الطريقة 1: محاولة من Packagist
echo "🔍 محاولة التثبيت من Packagist..."
cd "$PROJECT_PATH"
composer require eng-mohamedemad-dev/command-module-generator --no-interaction 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ تم التثبيت من Packagist بنجاح!"
    php artisan list | grep module
    exit 0
fi

# الطريقة 2: من GitHub
echo "⚠️  Packagist غير متاح، جاري المحاولة من GitHub..."
cd "$PROJECT_PATH"

# إضافة repository في composer.json
composer config repositories.command-module-generator vcs https://github.com/eng-mohamedemad-dev/command-module-generator.git
composer require eng-mohamedemad-dev/command-module-generator:dev-main --no-interaction 2>/dev/null

if [ $? -eq 0 ]; then
    echo "✅ تم التثبيت من GitHub بنجاح!"
    php artisan list | grep module
    exit 0
fi

# الطريقة 3: نسخ محلي
echo "⚠️  GitHub غير متاح، جاري النسخ المحلي..."
mkdir -p "$PROJECT_PATH/packages"
cp -r "$PACKAGE_PATH" "$PROJECT_PATH/packages/"

# تعديل composer.json
cd "$PROJECT_PATH"
composer config repositories.command-module-generator path "./packages/command-module-generator"
composer require eng-mohamedemad-dev/command-module-generator:@dev --no-interaction

if [ $? -eq 0 ]; then
    echo "✅ تم التثبيت محلياً بنجاح!"
    php artisan list | grep module
    exit 0
fi

echo "❌ فشل التثبيت!"
exit 1
