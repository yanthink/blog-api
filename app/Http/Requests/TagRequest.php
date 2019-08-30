<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
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
                'between:2,10',
            ],
            'order' => 'required|integer|min:0|max:9999',
        ];
    }

    public function attributes()
    {
        return [
            'name' => '标签名称',
            'order' => '排序编号',
        ];
    }
}
