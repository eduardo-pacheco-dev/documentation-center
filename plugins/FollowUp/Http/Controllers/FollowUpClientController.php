<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\FollowUp\Http\Requests\StoreFollowUpClientRequest;
use Plugins\FollowUp\Http\Requests\UpdateFollowUpClientRequest;
use Plugins\FollowUp\Models\FollowUpClient;

class FollowUpClientController extends Controller
{
    /**
     * Register a client.
     */
    public function store(StoreFollowUpClientRequest $request): RedirectResponse
    {
        FollowUpClient::query()->create([
            'name' => $request->validated('client_name'),
        ]);

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Cliente cadastrado com sucesso.');
    }

    /**
     * Rename the given client.
     */
    public function update(UpdateFollowUpClientRequest $request, FollowUpClient $client): RedirectResponse
    {
        $client->update([
            'name' => $request->validated('client_name'),
        ]);

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Cliente atualizado com sucesso.');
    }

    /**
     * Remove the given client and its projects.
     */
    public function destroy(FollowUpClient $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Cliente removido com sucesso.');
    }
}
