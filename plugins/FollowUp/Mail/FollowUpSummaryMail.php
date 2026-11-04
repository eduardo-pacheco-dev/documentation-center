<?php

namespace Plugins\FollowUp\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Support\FollowUpExport;

class FollowUpSummaryMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  Collection<int, FollowUp>  $followUps
     */
    public function __construct(
        public User $user,
        public Collection $followUps,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $prefix = (string) config('follow-up.email.subject_prefix', 'Follow-up');

        return new Envelope(
            subject: $prefix.' — Resumo '.now()->format('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'follow-up::mail.summary',
            with: [
                'user' => $this->user,
                'followUps' => $this->followUps,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $export = app(FollowUpExport::class);
        $spreadsheet = $export->all($this->followUps);

        $path = tempnam(sys_get_temp_dir(), 'follow-up-summary-');

        (new Xlsx($spreadsheet))->save($path);

        $contents = (string) file_get_contents($path);

        @unlink($path);

        $spreadsheet->disconnectWorksheets();

        return [
            Attachment::fromData(
                fn (): string => $contents,
                'follow-ups-resumo-'.now()->format('Y-m-d').'.xlsx',
            )->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
