<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserPost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username' => 'required|string|unique:users|min:4|max:20',
            'password' => 'required|string|min:6|max:30',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => '用户名必填',
            'username.string'   => '用户名格式不正确',
            'username.unique'   => '用户名已经存在',
            'username.min'      => '用户名最短4个字符',
            'username.max'      => '用户名最长20个字符',
            'password.required' => '密码必填',
            'password.string'   => '密码格式不正确',
            'password.min'      => '密码最短6个字符',
            'password.max'      => '密码最长30个字符',
        ];
    }
}
