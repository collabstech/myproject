<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileSaveRequest extends FormRequest
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
        $user = \Auth::user();
        $rules = [
            'name' => 'required',
            'avatar' => 'image|max:2048',
        ];
        if ($this->password) {
            $rules['password'] = 'required|min:6';
        }
        if ($user->email != $this->email) {
            $rules['email'] = 'required|email|unique:users,email';
        } else {
            $rules['email'] = 'required|email';
        }
        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            session()->flash('code', 422);
            session()->flash('message', $validator->errors()->first());
        });
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
