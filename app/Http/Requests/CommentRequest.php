<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => 'required|max:1024',
        ];
    }

    public function attributes()
    {
        return [
            'content' => '内容',
        ];
    }
}
