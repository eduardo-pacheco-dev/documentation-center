<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Plugins\FollowUp\Http\Requests\UpdateFollowUpProjectRequest;
use Plugins\FollowUp\Models\FollowUpProject;

class FollowUpProjectController extends Controller
{
    /**
     * Update the given project.
     */
    public function update(UpdateFollowUpProjectRequest $request, FollowUpProject $project): RedirectResponse
    {
        $project->update([
            'client_id' => $request->validated('client_id'),
            'name' => $request->validated('project_name'),
        ]);

        $project->followUps()->update(['client_id' => $project->client_id]);

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Projeto atualizado com sucesso.');
    }

    /**
     * Remove the given project.
     */
    public function destroy(FollowUpProject $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('admin.follow-ups.settings')
            ->with('status', 'Projeto removido com sucesso.');
    }
}
