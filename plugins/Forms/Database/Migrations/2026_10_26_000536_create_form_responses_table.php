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
        Schema::create('form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 40)->default('Preenchido');
            $table->json('steps')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['form_id', 'submitted_at']);
        });

        $this->backfillResponses();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_responses');
    }

    /**
     * Move the answers already stored on forms into response records.
     */
    private function backfillResponses(): void
    {
        DB::table('forms')
            ->select('id', 'steps', 'status', 'created_at', 'updated_at')
            ->orderBy('id')
            ->get()
            ->each(function (object $form): void {
                $steps = json_decode((string) $form->steps, true);

                if (! is_array($steps) || ! $this->hasAnswers($steps)) {
                    return;
                }

                DB::table('form_responses')->insert([
                    'form_id' => $form->id,
                    'user_id' => null,
                    'status' => in_array($form->status, ['Pendente', 'Em preenchimento'], true)
                        ? 'Preenchido'
                        : $form->status,
                    'steps' => $form->steps,
                    'submitted_at' => $form->updated_at ?? now(),
                    'created_at' => $form->created_at ?? now(),
                    'updated_at' => $form->updated_at ?? now(),
                ]);
            });
    }

    /**
     * Determine whether the stored steps contain any answered field.
     *
     * @param  array<int, array<string, mixed>>  $steps
     */
    private function hasAnswers(array $steps): bool
    {
        foreach ($steps as $step) {
            foreach ($step['fields'] ?? [] as $field) {
                if (
                    filled($field['value'] ?? null)
                    || ! empty($field['images'] ?? [])
                    || ! empty($field['files'] ?? [])
                ) {
                    return true;
                }
            }
        }

        return false;
    }
};
