<?php

return [
    // النوع الافتراضي لو لم يتم إرساله في الأمر ('web' أو 'api')
    'default_type' => 'web',

    // المسار الأساسي للواجهات web views (فارغ = resources/views/{module})
    'default_views_path' => '',

    // توليد repository؟ (true/false)
    'make_repo' => false,

    // تفعيل توليد ملف الـ API Resource؟
    'make_resource' => true,
    // تفعيل توليد ملف Policy؟
    'make_policy' => true,
    // تفعيل توليد Observer؟
    'make_observer' => true,

    // وضع توليد الريكوستات:
    // 'split'  = يولد ملفين (StoreRequest & UpdateRequest) داخل مجلد {Module} (افتراضي)
    // 'single' = يولد ملف واحد فقط باسم {Module}Request ويكون خارج المجلد
    'requests_mode' => 'split',

    // أسماء الطلبات عند توليد ملفين
    'store_request_name' => 'StoreRequest', // مثال CarStoreRequest
    'update_request_name' => 'UpdateRequest', // مثال CarUpdateRequest
];
