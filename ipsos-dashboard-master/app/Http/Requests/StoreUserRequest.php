<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        $user = \App\User::where('uuid', $this->uuid)->first();
        $rules = [
            'name' => 'required',
            'role' => 'required',
            'avatar' => 'image|max:2048',
            'company_id' => 'required',
        ];
        if (isset($user) && $user->email != $this->email) {
            $rules['email'] = 'required|email|unique:users,email';
        } else if (!isset($user)) {
            $rules['email'] = 'required|email|unique:users,email';            
        } else {
            $rules['email'] = 'required|email';
        }
        
        if ($this->password) {
            $rules['password'] = 'required|min:6';
        }
        
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'avatar.image' => 'The file type must be an image.',
            'avatar.max' => 'Maximum file size is 2 MB.',
        ];
    }
}
