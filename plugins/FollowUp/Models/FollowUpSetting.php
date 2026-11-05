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
    'summary_period',
    'summary_times',
    'summary_days',
    'mail_enabled',
    'mail_host',
    'mail_port',
    'mail_encryption',
    'mail_username',
    'mail_password',
    'mail_from_address',
    'mail_from_name',
    'summary_recipients',
    'summary_cc_recipients',
])]
class FollowUpSetting extends Model
{
    public const SUMMARY_PERIOD_ALL = 'all';

    public const SUMMARY_PERIOD_LAST_30_DAYS = 'last_30_days';

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
            'summary_period' => self::SUMMARY_PERIOD_ALL,
            'summary_times' => implode(',', (array) config('follow-up.email.summary_times', ['08:00', '17:00'])),
            'summary_days' => implode(',', self::allSummaryDays()),
        ]);
    }

    /**
     * The options for the summary period select.
     *
     * @return array<string, string>
     */
    public static function summaryPeriodOptions(): array
    {
        return [
            self::SUMMARY_PERIOD_ALL => 'Todos os follow-ups em aberto',
            self::SUMMARY_PERIOD_LAST_30_DAYS => 'Somente os últimos 30 dias (por prazo)',
        ];
    }

    /**
     * The configured summary period.
     */
    public function summaryPeriod(): string
    {
        return array_key_exists((string) $this->summary_period, self::summaryPeriodOptions())
            ? (string) $this->summary_period
            : self::SUMMARY_PERIOD_ALL;
    }

    /**
     * Whether the summary should only include follow-ups due in the last 30 days.
     */
    public function summaryLimitedToLastThirtyDays(): bool
    {
        return $this->summaryPeriod() === self::SUMMARY_PERIOD_LAST_30_DAYS;
    }

    /**
     * The configured summary delivery times.
     *
     * @return list<string>
     */
    public function summaryTimes(): array
    {
        return self::parseSummaryTimes((string) $this->summary_times);
    }

    /**
     * The weekdays available for the periodic summary.
     *
     * @return array<int, string>
     */
    public static function summaryDayOptions(): array
    {
        return [
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
            0 => 'Domingo',
        ];
    }

    /**
     * Every weekday, used as the default selection.
     *
     * @return list<int>
     */
    public static function allSummaryDays(): array
    {
        return array_keys(self::summaryDayOptions());
    }

    /**
     * The configured weekdays of the periodic summary.
     *
     * @return list<int>
     */
    public function summaryDays(): array
    {
        $days = self::parseSummaryDays((string) $this->summary_days);

        return $days === [] ? self::allSummaryDays() : $days;
    }

    /**
     * Normalize a comma separated list of weekdays (0 = Sunday, 6 = Saturday).
     *
     * @return list<int>
     */
    public static function parseSummaryDays(string $value): array
    {
        $days = preg_split('/[\s,;]+/', $value) ?: [];

        return collect($days)
            ->filter(fn (string $day): bool => ctype_digit($day) && (int) $day >= 0 && (int) $day <= 6)
            ->map(fn (string $day): int => (int) $day)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Normalize a comma separated list of HH:MM times.
     *
     * @return list<string>
     */
    public static function parseSummaryTimes(string $value): array
    {
        $times = preg_split('/[\s,;]+/', $value) ?: [];

        return collect($times)
            ->filter(fn (string $time): bool => preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time) === 1)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * The e-mail addresses that receive the general periodic summary.
     *
     * @return list<string>
     */
    public function summaryRecipients(): array
    {
        return self::parseSummaryRecipients((string) $this->summary_recipients);
    }

    /**
     * The additional copy recipients configured from the schedule composer.
     *
     * @return list<string>
     */
    public function summaryCcRecipients(): array
    {
        return self::parseSummaryRecipients((string) $this->summary_cc_recipients);
    }

    /**
     * Normalize a comma separated list of e-mail addresses.
     *
     * @return list<string>
     */
    public static function parseSummaryRecipients(string $value): array
    {
        $recipients = preg_split('/[\s,;]+/', $value) ?: [];

        return collect($recipients)
            ->filter(fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
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
