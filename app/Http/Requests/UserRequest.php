<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
                'regex:/^([\x{4E00}-\x{9FA5}\x{f900}-\x{fa2d}]|[a-zA-Z])([\x{4E00}-\x{9FA5}\x{f900}-\x{fa2d}]|[a-zA-Z0-9_-]){1,20}$/u',
                Rule::unique('users')->where(function (Builder $builder) {
                    if ($this->route()->hasParameter('user')) {
                        $user = $this->route('user');
                        if ($user instanceof User) {
                            $user = $user->id;
                        }
                        $builder->where('id', '<>', $user);
                    }
                }),
            ],
            'email' => [
                'email',
                Rule::unique('users')->where(function (Builder $builder) {
                    if ($this->route()->hasParameter('user')) {
                        $user = $this->route('user');
                        if ($user instanceof User) {
                            $user = $user->id;
                        }
                        $builder->where('id', '<>', $user);
                    }
                }),
            ],
            'password' => [
                $this->route()->hasParameter('user') ? '' : 'required',
                'regex:/^(?![^a-zA-Z]+$)(?!\D+$).{8,20}$/'
            ],
        ];
    }

    public function attributes()
    {
        return [
            'name' => '用户名称',
            'email' => '邮箱号码',
            'password' => '用户密码',
        ];
    }
}
