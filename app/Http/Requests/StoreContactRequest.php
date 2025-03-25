<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:contacts,email,NULL,id,user_id,' . $this->user()->id,
            'phone_code' => 'required|string|max:10',
            'phone_number' => 'required|string|max:20',
            'state' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'birth_date' => 'nullable|date_format:Y-m-d',
            'notes' => 'nullable|string',
        ];
    }
}
