<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Plugins\FollowUp\Http\Requests\StoreFollowUpClientRequest;
use Plugins\FollowUp\Http\Requests\UpdateFollowUpClientRequest;
use Plugins\FollowUp\Models\FollowUpClient;

class FollowUpClientController extends Controller
{
    /**
     * Register a client and, optionally, its first project.
     */
    public function store(StoreFollowUpClientRequest $request): RedirectResponse
    {
        $clientId = $request->validated('client_id');
        $projectName = trim((string) $request->validated('project_name'));
        $isNewClient = blank($clientId) || $clientId === 'new';

        if ($isNewClient) {
            $client = FollowUpClient::query()->firstOrCreate([
                'name' => trim($request->validated('client_name')),
            ]);

            if ($projectName === '') {
                if (! $client->wasRecentlyCreated) {
                    throw ValidationException::withMessages([
                        'client_name' => 'Este cliente já está cadastrado. Selecione-o na lista para adicionar um projeto.',
                    ]);
                }

                return redirect()
                    ->route('admin.follow-ups.settings')
                    ->with('status', 'Cliente cadastrado com sucesso.');
            }
        } else {
            $client = FollowUpClient::query()->findOrFail($clientId);

            if ($projectName === '') {
                throw ValidationException::withMessages([
                    'project_name' => 'Informe um projeto para o cliente selecionado.',
                ]);
            }
        }

        if ($client->projects()->where('name', $projectName)->exists()) {
            throw ValidationException::withMessages([
                'project_name' => 'Este cliente já possui um projeto com este nome.',
            ]);
        }

        $client->projects()->create(['name' => $projectName]);

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', $client->wasRecentlyCreated
                ? 'Cliente e projeto cadastrados com sucesso.'
                : 'Projeto cadastrado com sucesso.');
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
