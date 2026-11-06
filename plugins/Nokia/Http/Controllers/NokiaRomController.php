<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Plugins\Nokia\Http\Requests\StoreNokiaRomRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaRomRequest;
use Plugins\Nokia\Models\NokiaRom;

class NokiaRomController extends Controller
{
    /**
     * Show the plugin settings page with the ROM section.
     */
    public function index(): View
    {
        return view('nokia::settings.index', [
            'roms' => NokiaRom::query()
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created ROM.
     */
    public function store(StoreNokiaRomRequest $request): RedirectResponse
    {
        NokiaRom::query()->create($request->validated());

        return redirect()
            ->route('admin.nokia.settings.index')
            ->with('status', 'ROM cadastrado com sucesso.');
    }

    /**
     * Show the form to edit the given ROM.
     */
    public function edit(NokiaRom $rom): View
    {
        return view('nokia::settings.edit', [
            'rom' => $rom,
        ]);
    }

    /**
     * Update the given ROM.
     */
    public function update(UpdateNokiaRomRequest $request, NokiaRom $rom): RedirectResponse
    {
        $rom->update($request->validated());

        return redirect()
            ->route('admin.nokia.settings.index')
            ->with('status', 'ROM atualizado com sucesso.');
    }

    /**
     * Remove the given ROM.
     */
    public function destroy(NokiaRom $rom): RedirectResponse
    {
        $rom->delete();

        return redirect()
            ->route('admin.nokia.settings.index')
            ->with('status', 'ROM excluído com sucesso.');
    }
}
