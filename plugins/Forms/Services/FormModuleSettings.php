<?php

namespace Plugins\Forms\Services;

use App\Models\User;
use Plugins\Forms\Models\FormModuleSetting;

class FormModuleSettings
{
    /**
     * The default form types available in the module.
     *
     * @var list<string>
     */
    public const DEFAULT_TYPES = [
        'Registro Fotográfico',
        'Checklist',
        'Vistoria',
        'Inspeção',
        'Laudo Técnico',
    ];

    /**
     * The default form statuses available in the module.
     *
     * @var list<string>
     */
    public const DEFAULT_STATUSES = [
        'Pendente',
        'Em preenchimento',
        'Preenchido',
        'Aprovado',
        'Reprovado',
    ];

    /**
     * The available list views and their labels.
     *
     * @var array<string, string>
     */
    public const VIEWS = [
        'table' => 'Tabela',
        'cards' => 'Cards',
        'compact' => 'Lista',
    ];

    /**
     * The available page sizes.
     *
     * @var list<int>
     */
    public const PER_PAGE_OPTIONS = [12, 24, 36];

    public const DEFAULT_VIEW = 'table';

    public const DEFAULT_PER_PAGE = 12;

    /**
     * @param  array<string, mixed>  $settings
     */
    public function __construct(private readonly array $settings = []) {}

    /**
     * Build the settings for the given user.
     */
    public static function for(?User $user): self
    {
        if ($user === null) {
            return new self;
        }

        $setting = FormModuleSetting::query()
            ->where('user_id', $user->getKey())
            ->first();

        return new self($setting?->only([
            'types',
            'statuses',
            'default_template_id',
            'default_view',
            'per_page',
        ]) ?? []);
    }

    /**
     * Persist the settings for the given user.
     *
     * @param  array<string, mixed>  $settings
     */
    public static function save(User $user, array $settings): void
    {
        FormModuleSetting::query()->updateOrCreate(
            ['user_id' => $user->getKey()],
            [
                'types' => $settings['types'] ?? null,
                'statuses' => $settings['statuses'] ?? null,
                'default_template_id' => $settings['default_template_id'] ?? null,
                'default_view' => $settings['default_view'] ?? self::DEFAULT_VIEW,
                'per_page' => (int) ($settings['per_page'] ?? self::DEFAULT_PER_PAGE),
            ],
        );
    }

    /**
     * The configured form types.
     *
     * @return list<string>
     */
    public function types(): array
    {
        return $this->list('types', self::DEFAULT_TYPES);
    }

    /**
     * The configured form statuses.
     *
     * @return list<string>
     */
    public function statuses(): array
    {
        return $this->list('statuses', self::DEFAULT_STATUSES);
    }

    /**
     * The template that should be selected by default on new forms.
     */
    public function defaultTemplateId(): ?int
    {
        $value = $this->settings['default_template_id'] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * The configured default list view.
     */
    public function defaultView(): string
    {
        $value = $this->settings['default_view'] ?? null;

        return is_string($value) && array_key_exists($value, self::VIEWS)
            ? $value
            : self::DEFAULT_VIEW;
    }

    /**
     * The configured default number of items per page.
     */
    public function perPage(): int
    {
        $value = $this->settings['per_page'] ?? null;

        return is_numeric($value) && in_array((int) $value, self::PER_PAGE_OPTIONS, true)
            ? (int) $value
            : self::DEFAULT_PER_PAGE;
    }

    /**
     * Normalize a list of lines submitted through a textarea.
     *
     * @return list<string>
     */
    public static function normalizeLines(mixed $value): array
    {
        if (is_array($value)) {
            $value = implode("\n", array_map(fn (mixed $line) => (string) $line, $value));
        }

        return collect(preg_split('/\r\n|\r|\n/', (string) $value) ?: [])
            ->map(fn (string $line) => trim($line))
            ->filter(fn (string $line) => $line !== '')
            ->values()
            ->all();
    }

    /**
     * Resolve a configured list, falling back to the defaults when empty.
     *
     * @param  list<string>  $default
     * @return list<string>
     */
    private function list(string $key, array $default): array
    {
        $value = $this->settings[$key] ?? null;

        if (! is_array($value)) {
            return $default;
        }

        $list = self::normalizeLines($value);

        return $list === [] ? $default : $list;
    }
}
