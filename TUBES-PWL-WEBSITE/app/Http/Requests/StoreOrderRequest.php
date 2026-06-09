<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_availability_id' => 'required|integer|exists:ticket_availability,ticket_availability_id',
            'quantity'               => 'required|integer|min:1|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_availability_id.required' => 'Please select a ticket availability.',
            'ticket_availability_id.exists'   => 'The selected ticket availability does not exist.',
            'quantity.required'               => 'Please enter a quantity.',
            'quantity.min'                    => 'Quantity must be at least 1.',
            'quantity.max'                    => 'Quantity cannot exceed 50 tickets.',
        ];
    }
}
