<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::where('user_id', auth('api')->id());

        // if ($request->name) {
        //     $query->where('name', 'like', '%' . $request->name . '%');
        // }
        if ($request->address) {
            $query->where('address', $request->address);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'nullable|email',
            'address' => 'required',
        ]);

        $data['user_id'] = auth('api')->id();

        return response()->json(Contact::create($data));
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::where('user_id', auth('api')->id())->findOrFail($id);
        $contact->update($request->all());

        return response()->json($contact);
    }

    public function destroy($id)
    {
        Contact::where('user_id', auth('api')->id())->findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    }
    public function show($id)
{
    $contact = Contact::where('user_id', auth('api')->id())
        ->where('id', $id)
        ->first();

    if (! $contact) {
        return response()->json([
            'message' => 'Contact tidak ditemukan'
        ], 404);
    }

    return response()->json([
        'message' => 'Detail contact',
        'data' => $contact
    ]);
}
public function showByAddress($address)
{
    $contact = Contact::where('user_id', auth('api')->id())
        ->where('address', $address)
        ->first();

    if (! $contact) {
        return response()->json([
            'message' => 'Contact dengan alamat tersebut tidak ditemukan'
        ], 404);
    }

    return response()->json([
        'message' => 'Detail contact berdasarkan alamat',
        'data' => $contact
    ]);
}

}