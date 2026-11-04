<?php

namespace Plugins\FollowUp\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Plugins\FollowUp\Mail\FollowUpSummaryMail;
use Plugins\FollowUp\Mail\FollowUpTableMail;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpSetting;

class SendDailySummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'follow-up:send-daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia o resumo dos follow-ups em aberto para os destinatários configurados ou para o criador de cada um.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $followUps = FollowUp::query()
            ->open()
            ->with(['assignee', 'creator'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->get();

        $settings = FollowUpSetting::current();

        $recipients = $settings->summaryRecipients();

        $copyRecipients = $settings->summaryCcRecipients();

        if ($recipients !== []) {
            $mail = Mail::to($recipients);

            if ($copyRecipients !== []) {
                $mail->cc($copyRecipients);
            }

            $prefix = (string) config('follow-up.email.subject_prefix', 'Follow-up');

            $mail->send(new FollowUpTableMail(
                $followUps,
                $prefix.' — Resumo '.now()->format('d/m/Y'),
            ));

            $this->info('Resumo geral de follow-ups enviado para '.implode(', ', $recipients).'.');

            return self::SUCCESS;
        }

        $queued = 0;

        $followUps
            ->filter(fn (FollowUp $followUp): bool => $followUp->creator?->email !== null)
            ->groupBy('created_by')
            ->each(function (Collection $followUps) use (&$queued, $copyRecipients): void {
                $creator = $followUps->first()->creator;

                $mail = Mail::to($creator->email);

                if ($copyRecipients !== []) {
                    $mail->cc($copyRecipients);
                }

                $mail->queue(new FollowUpSummaryMail($creator, $followUps->values()));

                $queued++;
            });

        $this->info("Resumos de follow-ups enfileirados: {$queued}.");

        return self::SUCCESS;
    }
}
