<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormResponseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The form statuses that receive answers.
     *
     * @var list<string>
     */
    private const FILLED_STATUSES = ['Em preenchimento', 'Preenchido', 'Aprovado', 'Reprovado'];

    /**
     * The known text answers, keyed by field label, used to keep the seed realistic.
     *
     * @var array<string, list<string>>
     */
    private const TEXT_ANSWERS = [
        'Área registrada' => ['Galpão A', 'Galpão B', 'Torre central', 'Abrigo de rua', 'Casa de energia'],
    ];

    /**
     * Fill the seeded forms with answers, photos and evaluation status.
     */
    public function run(): void
    {
        Form::query()
            ->whereIn('status', self::FILLED_STATUSES)
            ->orderBy('id')
            ->get()
            ->each(fn (Form $form) => $this->fill($form));
    }

    /**
     * Fill a single form, answering only the first section while it is in progress.
     */
    private function fill(Form $form): void
    {
        $inProgress = $form->status === 'Em preenchimento';

        $steps = collect($form->steps ?? [])
            ->values()
            ->map(function (array $step, int $index) use ($form, $inProgress) {
                if ($inProgress && $index > 0) {
                    return $step;
                }

                $step['fields'] = collect($step['fields'] ?? [])
                    ->values()
                    ->map(fn (array $field) => $this->answer($form, $field))
                    ->all();

                return $step;
            })
            ->all();

        $form->update(['steps' => $steps]);
    }

    /**
     * Build the answer for a single field based on its type.
     *
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    private function answer(Form $form, array $field): array
    {
        $type = $field['type'] ?? 'text';

        if ($type === 'image') {
            $field['images'] = $this->imageUrls($form, $field);
        } else {
            $field['value'] = $this->value($field);
        }

        $field['status'] = $this->fieldStatus($form->status, $type);

        return $field;
    }

    /**
     * Resolve the evaluation status of a field from the form status.
     */
    private function fieldStatus(string $formStatus, string $type): string
    {
        return match ($formStatus) {
            'Em preenchimento' => 'pendente',
            'Reprovado' => 'reprovado',
            'Aprovado' => 'aprovado',
            default => $type === 'image' ? 'enviado' : 'aprovado',
        };
    }

    /**
     * Build the answer value for a field based on its type.
     *
     * @param  array<string, mixed>  $field
     */
    private function value(array $field): string
    {
        $label = $field['label'] ?? '';
        $options = $field['options'] ?? ['Sim', 'Não'];

        return match ($field['type'] ?? 'text') {
            'number' => (string) fake()->numberBetween(1000, 9999),
            'date' => now()->subDays(fake()->numberBetween(1, 120))->toDateString(),
            'time' => fake()->time('H:i'),
            'radio', 'dropdown' => (string) fake()->randomElement($options),
            'checkbox' => implode(', ', (array) fake()->randomElements($options, min(2, count($options)))),
            'scale' => (string) fake()->numberBetween((int) ($field['scaleMin'] ?? 1), (int) ($field['scaleMax'] ?? 5)),
            'textarea' => fake('pt_BR')->sentence(fake()->numberBetween(10, 20)),
            default => (string) fake()->randomElement(self::TEXT_ANSWERS[$label] ?? [fake()->words(3, true)]),
        };
    }

    /**
     * Build lorem ipsum photo URLs (Lorem Picsum) for an image field.
     *
     * @param  array<string, mixed>  $field
     * @return list<string>
     */
    private function imageUrls(Form $form, array $field): array
    {
        $count = fake()->numberBetween(1, 2);

        return collect(range(1, $count))
            ->map(function (int $index) use ($form, $field) {
                $seed = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $form->code.'-'.($field['id'] ?? 'img').'-'.$index));

                return "https://picsum.photos/seed/{$seed}/640/480";
            })
            ->all();
    }
}
