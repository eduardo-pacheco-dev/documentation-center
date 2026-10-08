<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MemberRequest;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    /**
     * Give a user access to the project.
     */
    public function store(MemberRequest $request, Project $project): RedirectResponse
    {
        $user = User::where('email', $request->validated('email'))->firstOrFail();

        $project->members()->updateOrCreate(
            ['user_id' => $user->getKey()],
            ['role' => $request->validated('role') ?? 'viewer'],
        );

        return back()->with('status', 'Membro adicionado ao projeto.');
    }

    /**
     * Change the role of a project member.
     */
    public function update(Request $request, Project $project, ProjectMember $member): RedirectResponse
    {
        $this->authorize('manageMembers', $project);

        abort_unless($member->project_id === $project->getKey(), 404);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:editor,viewer'],
        ]);

        $member->update(['role' => $validated['role']]);

        return back()->with('status', 'Papel do membro atualizado.');
    }

    /**
     * Revoke the access of a member to the project.
     */
    public function destroy(Request $request, Project $project, ProjectMember $member): RedirectResponse
    {
        $this->authorize('manageMembers', $project);

        abort_unless($member->project_id === $project->getKey(), 404);

        $member->delete();

        return back()->with('status', 'Membro removido do projeto.');
    }
}
