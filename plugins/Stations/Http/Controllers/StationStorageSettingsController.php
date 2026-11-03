<?php

namespace Plugins\Stations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\Stations\Http\Requests\UpdateStationStorageSettingsRequest;
use Plugins\Stations\Models\StationStorageSetting;

class StationStorageSettingsController extends Controller
{
    /**
     * Update the station storage settings.
     */
    public function update(UpdateStationStorageSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        foreach (['aws_secret', 'azure_account_key', 'cloudflare_secret_access_key'] as $secret) {
            if (blank($data[$secret] ?? null)) {
                unset($data[$secret]);
            }
        }

        StationStorageSetting::current()->update($data);

        return redirect()
            ->to(route('settings').'#stations-storage')
            ->with('status', 'Configurações de armazenamento salvas com sucesso.');
    }
}
