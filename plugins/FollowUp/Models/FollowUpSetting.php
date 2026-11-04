<?php

namespace Plugins\FollowUp\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Hidden(['mail_password'])]
#[Fillable([
    'per_page',
    'due_soon_days',
    'widget_limit',
    'email_summary_enabled',
    'email_subject_prefix',
    'summary_times',
    'mail_enabled',
    'mail_host',
    'mail_port',
    'mail_encryption',
    'mail_username',
    'mail_password',
    'mail_from_address',
    'mail_from_name',
])]
class FollowUpSetting extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'follow_up_settings';

    /**
     * The current plugin settings, creating the default row when needed.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'per_page' => (int) config('follow-up.per_page', 15),
            'due_soon_days' => (int) config('follow-up.due_soon_days', 7),
            'widget_limit' => (int) config('follow-up.widget_limit', 5),
            'email_summary_enabled' => true,
            'email_subject_prefix' => (string) config('follow-up.email.subject_prefix', 'Follow-up'),
            'summary_times' => implode(',', (array) config('follow-up.email.summary_times', ['08:00', '17:00'])),
        ]);
    }

    /**
     * The configured summary delivery times.
     *
     * @return list<string>
     */
    public function summaryTimes(): array
    {
        $times = preg_split('/[\s,;]+/', (string) $this->summary_times) ?: [];

        return collect($times)
            ->filter(fn (string $time): bool => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) === 1)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'per_page' => 'integer',
            'due_soon_days' => 'integer',
            'widget_limit' => 'integer',
            'email_summary_enabled' => 'boolean',
            'mail_enabled' => 'boolean',
            'mail_port' => 'integer',
            'mail_username' => 'encrypted',
            'mail_password' => 'encrypted',
        ];
    }
}
