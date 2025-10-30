#!/bin/bash

# بعد إنشاء الـ repository على GitHub، شغل هذا الملف

cd /home/mohamed/Downloads/project/Senior/first_topic/packages/command-module-generator

# التأكد من الـ remote
git remote -v

# رفع الكود
git push -u origin main

echo "✅ تم رفع الكود بنجاح!"
echo "الآن ارجع لـ Packagist واضغط Update"
