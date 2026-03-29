<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $ticket = $this->route('ticket');

        return [
            'type' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('tickets', 'type')
                    ->where('event_id', $ticket->event_id)
                    ->ignore($ticket->id),
            ],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.unique' => 'Another ticket with this type already exists for this event.',
        ];
    }
}
