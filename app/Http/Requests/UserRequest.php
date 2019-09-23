<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    protected $userNamePattern = '/^(?!_)(?!.*?_$)[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{1,10}$/u';

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'regex:'.$this->userNamePattern,
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
