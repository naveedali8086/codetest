<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->can('create', Job::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_language_id' => 'required',
            'immediate' => 'required|in:yes,no',
            'duration' => 'required_if:immediate,yes',
            'customer_phone_type' => 'required|in:yes,no',
            'customer_physical_type' => 'required|in:yes,no',
            'due_time' => 'required_if:immediate:no|date_format:Y-m-d|before_or_equal:today',
            'job_for' => 'required|in:male,female',
            // obviously there must be tens of other validations
        ];
    }

    public function after()
    {
        return [
            function (Validator $validator) {
            if ($validator->passes()) {
                // any necessary data modification can be done here
                // to make the controller/repository light weight
            }
            }
        ];
    }
}
