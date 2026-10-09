<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClientContactRequest;
use App\Models\Client;
use App\Models\ClientContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ClientContactController extends Controller
{
    /**
     * Add a contact to the client.
     */
    public function store(ClientContactRequest $request, Client $client): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($client, $validated): void {
            $isPrimary = (bool) ($validated['is_primary'] ?? false);

            if ($isPrimary) {
                $client->contacts()->update(['is_primary' => false]);
            }

            $client->contacts()->create([
                'name' => $validated['name'],
                'position' => $validated['position'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'is_primary' => $isPrimary || $client->contacts()->count() === 0,
            ]);
        });

        return back()->with('status', 'Contato adicionado com sucesso.');
    }

    /**
     * Update a contact of the client.
     */
    public function update(ClientContactRequest $request, Client $client, ClientContact $contact): RedirectResponse
    {
        abort_unless($contact->client_id === $client->getKey(), 404);

        $validated = $request->validated();

        DB::transaction(function () use ($client, $contact, $validated): void {
            $isPrimary = (bool) ($validated['is_primary'] ?? false);

            if ($isPrimary) {
                $client->contacts()->whereKeyNot($contact->getKey())->update(['is_primary' => false]);
            }

            $contact->update([
                'name' => $validated['name'],
                'position' => $validated['position'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'is_primary' => $isPrimary,
            ]);
        });

        return back()->with('status', 'Contato atualizado com sucesso.');
    }

    /**
     * Remove a contact from the client.
     */
    public function destroy(Client $client, ClientContact $contact): RedirectResponse
    {
        $this->authorize('update', $client);

        abort_unless($contact->client_id === $client->getKey(), 404);

        $contact->delete();

        return back()->with('status', 'Contato removido com sucesso.');
    }
}
