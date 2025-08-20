<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateReportRequest extends FormRequest
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
            'reportname' => 'required',
            'type' => 'required',
            'side' => 'required|different:top',
            'top' => 'required|different:side',
            'value' => 'required',
            'operation' => 'required',
        ];
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
            'reportname.required' => 'The report name field is required.',
            'top.different' => 'This input must be different each other.',
            'side.different' => 'This input must be different each other.',
            'value.different' => 'This input must be different each other.',
        ];
    }
}
