<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentFieldController extends Controller
{
    public function index()
    {
        $fields = Auth::user()->appointmentFields()
            ->orderBy('order')
            ->get();

        return response()->json(['fields' => $fields]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:text,select,boolean,date,number',
            'required' => 'required|boolean',
            'options' => 'required_if:type,select|array',
            'order' => 'integer'
        ]);

        $field = Auth::user()->appointmentFields()->create([
            'name' => $request->name,
            'type' => $request->type,
            'required' => $request->required,
            'options' => $request->options,
            'order' => $request->order ?? 0,
            'active' => true
        ]);

        return response()->json(['field' => $field], 201);
    }

    public function update(Request $request, $id)
    {
        $field = Auth::user()->appointmentFields()->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:text,select,boolean,date,number',
            'required' => 'sometimes|required|boolean',
            'options' => 'required_if:type,select|array',
            'order' => 'sometimes|integer',
            'active' => 'sometimes|boolean'
        ]);

        $field->update($request->all());

        return response()->json(['field' => $field]);
    }

    public function destroy($id)
    {
        $field = Auth::user()->appointmentFields()->findOrFail($id);
        $field->delete();

        return response()->json(['message' => 'Field deleted successfully']);
    }
}
