<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('follow_up_assignees', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 255)->nullable();
            $table->timestamps();
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->foreignId('assignee_id')
                ->nullable()
                ->after('due_date')
                ->constrained('follow_up_assignees')
                ->nullOnDelete();
        });

        $this->migrateExistingAssignees();

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->dropConstrainedForeignId('assignee_id');
        });

        Schema::dropIfExists('follow_up_assignees');
    }

    /**
     * Create one custom assignee for each user currently assigned to a follow-up.
     */
    private function migrateExistingAssignees(): void
    {
        $users = DB::table('users')
            ->select(['id', 'name', 'surname', 'email'])
            ->get()
            ->keyBy('id');

        $assigneeIds = [];

        DB::table('follow_ups')
            ->select(['id', 'assigned_to'])
            ->whereNotNull('assigned_to')
            ->orderBy('id')
            ->get()
            ->each(function (object $followUp) use ($users, &$assigneeIds): void {
                $user = $users->get($followUp->assigned_to);

                if ($user === null) {
                    return;
                }

                if (! isset($assigneeIds[$user->id])) {
                    $assigneeIds[$user->id] = DB::table('follow_up_assignees')->insertGetId([
                        'name' => trim($user->name.' '.($user->surname ?? '')),
                        'email' => $user->email,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('follow_ups')
                    ->where('id', $followUp->id)
                    ->update(['assignee_id' => $assigneeIds[$user->id]]);
            });
    }
};
