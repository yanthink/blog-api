<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required',
            'status' => 'required|in:0,1',
            'content' => 'required',
            'tags' => 'required|array',
        ];
    }

    public function attributes()
    {
        return [
            'title' => '标题',
            'status' => '状态',
            'content' => '内容',
            'tags' => '标签',
        ];
    }
}
