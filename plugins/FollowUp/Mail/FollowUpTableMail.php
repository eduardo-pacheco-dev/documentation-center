<?php

namespace Plugins\FollowUp\Mail;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Support\FollowUpExport;

class FollowUpTableMail extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param  Collection<int, FollowUp>  $followUps
     */
    public function __construct(
        public Collection $followUps,
        public ?string $mailSubject = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject ?: 'Follow-ups — '.now()->format('d/m/Y'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->followUps->loadMissing(['assignee', 'creator', 'client', 'project']);

        return new Content(
            view: 'follow-up::mail.table',
            with: ['followUps' => $this->followUps],
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

        $path = tempnam(sys_get_temp_dir(), 'follow-up-table-');

        (new Xlsx($spreadsheet))->save($path);

        $contents = (string) file_get_contents($path);

        @unlink($path);

        $spreadsheet->disconnectWorksheets();

        return [
            Attachment::fromData(fn (): string => $contents, $export->allFileName())
                ->withMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ];
    }
}
