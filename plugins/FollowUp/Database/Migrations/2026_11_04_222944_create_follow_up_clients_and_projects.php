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
        Schema::create('follow_up_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->timestamps();
        });

        Schema::create('follow_up_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('follow_up_clients')->cascadeOnDelete();
            $table->string('name', 150);
            $table->timestamps();

            $table->unique(['client_id', 'name']);
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->foreignId('client_id')
                ->nullable()
                ->after('description')
                ->constrained('follow_up_clients')
                ->nullOnDelete();

            $table->foreignId('project_id')
                ->nullable()
                ->after('client_id')
                ->constrained('follow_up_projects')
                ->nullOnDelete();
        });

        $this->backfillRelations();

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropColumn(['client', 'project']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->string('client', 150)->nullable()->after('description');
            $table->string('project', 150)->nullable()->after('client');
        });

        DB::table('follow_ups')
            ->join('follow_up_clients', 'follow_up_clients.id', '=', 'follow_ups.client_id')
            ->select('follow_ups.id', 'follow_up_clients.name as client_name')
            ->orderBy('follow_ups.id')
            ->get()
            ->each(fn (object $row) => DB::table('follow_ups')
                ->where('id', $row->id)
                ->update(['client' => $row->client_name]));

        DB::table('follow_ups')
            ->join('follow_up_projects', 'follow_up_projects.id', '=', 'follow_ups.project_id')
            ->select('follow_ups.id', 'follow_up_projects.name as project_name')
            ->orderBy('follow_ups.id')
            ->get()
            ->each(fn (object $row) => DB::table('follow_ups')
                ->where('id', $row->id)
                ->update(['project' => $row->project_name]));

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::dropIfExists('follow_up_projects');
        Schema::dropIfExists('follow_up_clients');
    }

    /**
     * Move the previously typed client and project names into the new tables.
     */
    private function backfillRelations(): void
    {
        DB::table('follow_ups')
            ->select('id', 'client', 'project')
            ->orderBy('id')
            ->get()
            ->each(function (object $followUp): void {
                $clientName = trim((string) $followUp->client);
                $projectName = trim((string) $followUp->project);

                if ($clientName === '') {
                    return;
                }

                $clientId = DB::table('follow_up_clients')->where('name', $clientName)->value('id')
                    ?? DB::table('follow_up_clients')->insertGetId([
                        'name' => $clientName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $projectId = null;

                if ($projectName !== '') {
                    $projectId = DB::table('follow_up_projects')
                        ->where('client_id', $clientId)
                        ->where('name', $projectName)
                        ->value('id')
                        ?? DB::table('follow_up_projects')->insertGetId([
                            'client_id' => $clientId,
                            'name' => $projectName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                DB::table('follow_ups')->where('id', $followUp->id)->update([
                    'client_id' => $clientId,
                    'project_id' => $projectId,
                ]);
            });
    }
};
