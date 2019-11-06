<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|alpha_dash|between:2,128',
            'display_name' => 'required|between:2,128',
        ];
    }

    public function attributes()
    {
        return [
            'name' => '角色标识',
            'display_name' => '角色名称',
        ];
    }
}
