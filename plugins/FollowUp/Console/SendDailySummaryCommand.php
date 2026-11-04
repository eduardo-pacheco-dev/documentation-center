<?php

namespace Plugins\FollowUp\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Plugins\FollowUp\Mail\FollowUpSummaryMail;
use Plugins\FollowUp\Models\FollowUp;

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
    protected $description = 'Envia o resumo dos follow-ups em aberto para o criador de cada um.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $queued = 0;

        FollowUp::query()
            ->open()
            ->with(['assignee', 'creator'])
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->get()
            ->filter(fn (FollowUp $followUp): bool => $followUp->creator?->email !== null)
            ->groupBy('created_by')
            ->each(function (Collection $followUps) use (&$queued): void {
                $creator = $followUps->first()->creator;

                Mail::to($creator->email)->queue(new FollowUpSummaryMail($creator, $followUps->values()));

                $queued++;
            });

        $this->info("Resumos de follow-ups enfileirados: {$queued}.");

        return self::SUCCESS;
    }
}
