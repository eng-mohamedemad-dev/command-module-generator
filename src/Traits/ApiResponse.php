<?php

namespace CommandModuleGenerator\Traits;

/**
 * Trait ApiResponse
 * توحيد استجابة الـ API للنجاح والأخطاء بمختلف أنواعها
 * Laravel Unified API Response Trait (Success, Error, Validation, NotFound, Unauthorized, Paginate, etc)
 */
trait ApiResponse
{
    /**
     * Success response
     * استجابة نجاح
     */
    public static function success($message = 'نجحت العملية', $data = null, $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Error response
     * استجابة خطأ عام
     */
    public static function error($message = 'حدث خطأ ما', $data = null, $code = 422)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Validation error response
     * استجابة أخطاء تحقق (Validation)
     */
    public static function validation($errors, $message = 'بيانات غير صحيحة', $code = 422)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Not found response
     * استجابة للموارد غير موجودة
     */
    public static function notFound($message = 'العنصر غير موجود', $data = null, $code = 404)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Unauthorized response
     * استجابة عند عدم وجود صلاحية
     */
    public static function unauthorized($message = 'غير مصرح', $data = null, $code = 401)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Paginated collection response
     * استجابة مع بيانات مقسمة صفحات
     */
    public static function paginate($paginator, $message = 'تم بنجاح', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ], $code);
    }

    /**
     * No content response
     * استجابة فارغة 204
     */
    public static function noContent($message = 'لا يوجد محتوى', $code = 204)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => null,
        ], $code);
    }

    /**
     * Collection response (array, collection...) 
     * استجابة لقوائم البيانات
     */
    public static function collection($items, $message = 'تم بنجاح', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $items,
        ], $code);
    }

    /**
     * Cursor Pagination response
     * استجابة تقسيم بيانات بتقنية cursor
     */
    public static function cursorPaginate($paginator, $message = 'تم بنجاح', $code = 200)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'per_page' => $paginator->perPage(),
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'prev_cursor' => $paginator->previousCursor()?->encode(),
                'path' => $paginator->path(),
            ],
        ], $code);
    }
}
