<?php

namespace Plugins\FollowUp\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Support\FollowUpExport;

class FollowUpMail extends Mailable
{
    /**
     * Create a new message instance.
     */
    public function __construct(
        public FollowUp $followUp,
        public ?string $mailSubject = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $prefix = (string) config('follow-up.email.subject_prefix', 'Follow-up');

        return new Envelope(
            subject: $this->mailSubject ?: $prefix.': '.$this->followUp->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->followUp->loadMissing(['assignee', 'creator', 'updates.user']);

        return new Content(
            view: 'follow-up::mail.follow-up',
            with: ['followUp' => $this->followUp],
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
        $spreadsheet = $export->single($this->followUp);

        $path = tempnam(sys_get_temp_dir(), 'follow-up-mail-');

        (new Xlsx($spreadsheet))->save($path);

        $contents = (string) file_get_contents($path);

        @unlink($path);

        $spreadsheet->disconnectWorksheets();

        return [
            Attachment::fromData(fn (): string => $contents, $export->singleFileName($this->followUp))
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
