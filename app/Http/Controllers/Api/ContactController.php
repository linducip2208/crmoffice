<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contacts = QueryBuilder::for(Contact::class)
            ->allowedFilters([
                AllowedFilter::exact('client_id'),
                AllowedFilter::exact('is_primary'),
                AllowedFilter::partial('email'),
            ])
            ->allowedSorts(['first_name', 'last_name', 'created_at'])
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'first_name' => 'required|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'email' => 'nullable|email|unique:contacts,email',
            'phone' => 'nullable|string|max:40',
            'position' => 'nullable|string|max:120',
            'is_primary' => 'boolean',
            'portal_access' => 'boolean',
        ]);

        if (! empty($data['portal_access'])) {
            $data['password'] = Hash::make(str()->random(16));
        }

        $contact = Contact::create($data);

        return response()->json(['data' => $contact], 201);
    }

    public function update(Request $request, Contact $contact): JsonResponse
    {
        $data = $request->validate([
            'first_name' => 'sometimes|required|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'email' => 'nullable|email|unique:contacts,email,'.$contact->id,
            'phone' => 'nullable|string|max:40',
            'position' => 'nullable|string|max:120',
            'is_primary' => 'boolean',
            'portal_access' => 'boolean',
        ]);

        $contact->update($data);

        return response()->json(['data' => $contact]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
