<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissionRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'between:2,128',
                'regex:/^[\w-\.]+$/'
            ],
            'display_name' => 'required|between:4,128',
        ];
    }

    public function attributes()
    {
        return [
            'name' => '权限标识',
            'display_name' => '权限名称',
        ];
    }

    public function messages()
    {
        return [
            'name.regex' => '权限标识 格式允许字母、数字、点（.）、破折号（-）以及底线（_）',
        ];
    }
}
