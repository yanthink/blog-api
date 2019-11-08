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
            'state' => 'required|in:0,1',
            'content.markdown' => 'required',
            'tags' => 'required|array',
        ];
    }

    public function attributes()
    {
        return [
            'title' => '标题',
            'state' => '状态',
            'content.markdown' => '内容',
            'tags' => '标签',
        ];
    }
}
