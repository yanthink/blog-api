<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReplyRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required',
            'parent_id' => [
                Rule::exists('replys', 'id'),
            ],
        ];
    }

    public function attributes()
    {
        return [
            'content' => '内容',
        ];
    }
}
