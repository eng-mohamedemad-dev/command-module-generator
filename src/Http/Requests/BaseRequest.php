<?php

namespace CommandModuleGenerator\Http\Requests;

use CommandModuleGenerator\Traits\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BaseRequest extends FormRequest
{
    use ApiResponse;

    /**
     * Handle failed validation - custom unified json response
     * عند فشل التحقق يرجع رد موحّد (status, message, errors...)
     */
    protected function failedValidation(Validator $validator)
    {
        $record = static::validation($validator->errors());
        throw new ValidationException($validator, $record);
    }

    /**
     * Allow all requests by default (override if needed).
     * السماح للجميع افتراضيًا (يمكن تعديله حسب الحاجة)
     */
    public function authorize()
    {
        return true;
    }
}
