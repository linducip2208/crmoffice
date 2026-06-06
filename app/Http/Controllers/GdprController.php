<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GdprController extends Controller
{
    public function exportData(Request $request): JsonResponse
    {
        /** @var Contact $contact */
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;

        $data = [
            'exported_at' => now()->toIso8601String(),
            'contact' => $contact->only([
                'first_name', 'last_name', 'email', 'phone', 'position',
                'is_primary', 'locale', 'created_at', 'updated_at',
            ]),
            'client' => $client->only([
                'company_name', 'industry', 'website', 'phone',
                'billing_address', 'billing_city', 'billing_state', 'billing_country', 'billing_postal',
                'shipping_address', 'shipping_city', 'shipping_state', 'shipping_country', 'shipping_postal',
                'tax_id', 'status', 'created_at',
            ]),
            'invoices' => $client->invoices()->with('items')->get()->map->toArray(),
            'tickets' => $client->tickets()->with(['replies', 'status', 'priority'])->get()->map->toArray(),
            'projects' => $client->projects()->with(['milestones', 'members'])->get()->map->toArray(),
        ];

        $filename = 'gdpr-export-' . $contact->id . '-' . now()->format('Ymd-His') . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function requestDeletion(Request $request): JsonResponse
    {
        /** @var Contact $contact */
        $contact = Auth::guard('portal')->user();
        $client = $contact->client;

        if ($client->deleted_at) {
            return response()->json(['message' => 'Permintaan penghapusan sudah diproses sebelumnya.'], 409);
        }

        $client->update(['status' => 'pending_deletion']);
        $contact->update(['deletion_requested_at' => now()]);

        return response()->json([
            'message' => 'Permintaan penghapusan data telah dicatat. Anda akan menerima email konfirmasi. Data akan dihapus permanen dalam 30 hari.',
        ]);
    }
}
