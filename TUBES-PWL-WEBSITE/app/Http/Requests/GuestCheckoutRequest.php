<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuestCheckoutRequest extends FormRequest
{
    /**
     * Use a dedicated error bag so guest checkout errors do not mix with login errors.
     */
    protected $errorBag = 'guestCheckout';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email'         => trim((string) $this->input('email')),
            'confirm_email' => trim((string) $this->input('confirm_email')),
            'first_name'    => trim((string) $this->input('first_name')),
            'last_name'     => trim((string) $this->input('last_name')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email'         => ['required', 'email'],
            'confirm_email' => ['required', 'same:email'],
            'first_name'    => ['required', 'string', 'max:100'],
            'last_name'     => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required'         => 'Email is required',
            'email.email'            => 'Please enter a valid email address',
            'confirm_email.required' => 'Confirm email is required',
            'confirm_email.same'     => 'Confirm email must match email',
            'first_name.required'    => 'First name is required',
            'first_name.max'         => 'First name must not exceed 100 characters',
            'last_name.required'     => 'Last name is required',
            'last_name.max'          => 'Last name must not exceed 100 characters',
        ];
    }
}
