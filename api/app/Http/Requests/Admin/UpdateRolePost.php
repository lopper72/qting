<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePost extends FormRequest
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
        $id = $this->route('role');
        return [
            'name' => 'required|string|unique:roles,name,' . $id . '|min:4|max:20',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '角色名称必填',
            'name.string'   => '角色名称格式不正确',
            'name.unique'   => '角色名称已存在',
            'name.min'      => '角色名称最短4个字符',
            'name.max'      => '角色名称最长20个字符',
        ];
    }
}
