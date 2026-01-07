<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPost extends FormRequest
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
        $id = $this->route('user');
        return [
            'username' => 'required|string|unique:users,username,' . $id . '|min:4|max:20',
        ];
    }

    public function messages()
    {
        return [
            'username.required' => '用户名必填',
            'username.string'   => '用户名格式不正确',
            'username.unique'   => '用户名已经存在',
            'username.min'      => '用户名最短4个字符',
            'username.max'      => '用户名最长20个字符'
        ];
    }
}
