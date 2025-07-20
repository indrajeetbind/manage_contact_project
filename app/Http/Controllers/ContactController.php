<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\ContactCustomField;
use App\Models\ContactCustomFieldValue;
use Illuminate\Support\Facades\DB;
use App\Models\MergedContact;
class ContactController extends Controller
{
    public function index()
    {
        $custom_fields=ContactCustomField::all();
        return view('contacts.index', compact('custom_fields'));
    }

    public function fetch()
    {
        $contacts = Contact::latest()->get();
        return response()->json(['contacts' => $contacts]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'gender' => 'nullable|in:male,female',
            'profile_image'     => 'nullable|file|mimes:jpg,jpeg,png',
            'additional_file'   => 'nullable|file|mimes:pdf,doc,docx',
        ]);
        
        // dd($request->all());
        DB::beginTransaction();
        try {
            $contact = new Contact();
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->phone = $request->phone;
            $contact->gender = $request->gender;

            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $filename = 'profile_' . now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('profile_images', $filename,'public');
                $contact->profile_image = $filename;
            }

            if ($request->hasFile('additional_file')) {
                $file = $request->file('additional_file');
                $filename = 'doc_' . now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('additional_files', $filename,'public');
                $contact->additional_file = $filename;
            }

            $contact->save();

            // Save custom fields
            if ($request->custom_fields) {
                foreach ($request->custom_fields as $key => $field) {
                    ContactCustomFieldValue::create([
                        'contact_id'      => $contact->id,
                        'custom_field_id' => $key, // optional, if not using predefined field types
                        'value'           => json_encode([
                            'label' => $field['label'],
                            'value' => $field['value']
                        ]),
                    ]);
                }
            }

            DB::commit();

            return response()->json(['status_code'=>201,'message' => 'Contact created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status_code'=>500,'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        Contact::destroy($id);
        ContactCustomFieldValue::where('contact_id', $id)->delete();
        return response()->json(['message' => 'Contact deleted successfully']);
    }

    // 4. AJAX Filtering Example
    public function filter(Request $request)
    {
        // dd($request->all());
        $query = Contact::query();

        if (isset($request['name']) && $request['name'] != '') {
            $query->where('name', 'like', "%{$request->name}%");
        }
        if (isset($request['email']) && $request['email'] != '') {
            $query->where('email', 'like', "%{$request->email}%");
        }
        if (isset($request['phone']) && $request['phone'] != '') {
            $query->where('phone', 'like', "%{$request->phone}%");
        }
        if (isset($request['gender']) && $request['gender'] != '') {
            $query->where('gender', $request->gender);
        }

        $contacts = $query->with('customFieldValues.field')->get();

        return response()->json(['contacts' => $contacts]);
    }

    public function edit($id)
    {
        $contact = Contact::with('customFieldValues.field')->where('id', $id)->first();
        return response()->json(['contact' => $contact]);
    }

    public function update(Request $request, $id)
    {
        // dd("update",$request->all());
        $request->validate([
            'name'  => 'required',
            'email' => 'required|email',
            'gender' => 'nullable|in:male,female',
            'profile_image'     => 'nullable|file|mimes:jpg,jpeg,png',
            'additional_file'   => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        DB::beginTransaction();
        try {
            $contact = Contact::findOrFail($id);
            $contact->name = $request->name;
            $contact->email = $request->email;
            $contact->phone = $request->phone;
            $contact->gender = $request->gender;

            if ($request->hasFile('profile_image')) {
                $file = $request->file('profile_image');
                $filename = 'profile_' . now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->storeAs('public/profile_images', $filename);
                $contact->profile_image = $filename;
            }

            if ($request->hasFile('additional_file')) {
                $file = $request->file('additional_file');
                $filename = 'doc_' . now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/additional_files', $filename);
                $contact->additional_file = $filename;
            }

            $contact->save();

            // Update custom fields
            if ($request->custom_fields) {
                foreach ($request->custom_fields as $key => $field) {
                    ContactCustomFieldValue::updateOrCreate(
                        ['contact_id' => $contact->id, 'custom_field_id' => $key],
                        ['value' => json_encode(['label' => $field['label'], 'value' => $field['value']])]
                    );
                }
            }

            DB::commit();

            return response()->json(['status_code'=>200,'message' => 'Contact updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status_code'=>500,'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Merge contacts
    public function mergeContacts(Request $request)
    {
        // dd($request->all());
        $masterId = $request->master_contact_id;
        $to_contact_id = $request->to_contact_id;
        $contact_id = $request->contact_id; // contact_id is merged into to_contact_id

        // $master = Contact::findOrFail($masterId);
        
        // Save merge record in merged_contacts table
        MergedContact::create([
            'master_contact_id' => $masterId,
            'to_contact_id' => $to_contact_id,
            'contact_id' => $contact_id,
            'merged_at' => now(),
        ]);
        
        // Mark secondary contact as merged (optional status field)
        $secondary = Contact::where('id',$contact_id)->update(['status' => 'merged']);
        $secondary = Contact::where('id',$to_contact_id)->update(['status' => 'master']);

        return response()->json(['status_code'=>200,'message' => 'Contacts merged successfully']);
    }

    public function mergedContacts($id)
    {
        $mergedContacts = MergedContact::with(['mergedContact', 'destinationContact', 'masterContact'])->where('contact_id', $id)->orWhere('to_contact_id', $id)->get();
        return response()->json(['merged_contacts' => $mergedContacts]);
    }

}
