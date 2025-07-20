<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContactCustomField;

class CustomFieldController extends Controller
{
    public function index()
    {
        $fields = ContactCustomField::all();

        return response()->json(['custom_fields' => $fields]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|in:text,number,email,date,select'
        ]);

        // Create the custom field
        $customField = new ContactCustomField();
        $customField->label = $request->label;
        $customField->type = $request->type;
        $customField->save();

        return response()->json(['message' => 'Custom field created successfully.']);
    }

}
