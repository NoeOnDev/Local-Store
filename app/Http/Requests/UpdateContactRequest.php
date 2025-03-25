<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:contacts,email,' . $this->route('id') . ',id,user_id,' . $this->user()->id,
            'phone_code' => 'sometimes|required|string|max:10',
            'phone_number' => 'sometimes|required|string|max:20',
            'state' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'notes' => 'nullable|string',
        ];
    }
}
