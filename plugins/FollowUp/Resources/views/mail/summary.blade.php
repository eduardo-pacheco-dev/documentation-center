<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resumo de follow-ups</title>
</head>
<body style="margin:0;padding:24px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;overflow:hidden;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:18px;">
        <div style="height:6px;background-color:#4f46e5;"></div>

        <div style="padding:28px;">
            <p style="margin:0 0 6px;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#4f46e5;">Resumo periódico</p>

            <h1 style="margin:0 0 8px;font-size:23px;line-height:1.3;color:#111827;">Follow-ups em aberto</h1>

            <p style="margin:0 0 20px;font-size:13px;line-height:1.6;color:#6b7280;">
                Olá, {{ $user->full_name }}. Você tem {{ $followUps->count() }} follow-up(s) em aberto.
            </p>

            @foreach ($followUps as $followUp)
                <div style="margin-bottom:10px;padding:14px;border:1px solid #e5e7eb;border-radius:12px;background-color:#f9fafb;">
                    <p style="margin:0 0 6px;font-size:14px;font-weight:bold;color:#111827;">{{ $followUp->title }}</p>

                    <p style="margin:0 0 8px;font-size:12px;color:#6b7280;">
                        <span style="display:inline-block;margin:0 6px 4px 0;padding:2px 8px;border-radius:999px;background-color:#eef2ff;color:#4338ca;font-weight:bold;">{{ $followUp->status->label() }}</span>
                        <span style="display:inline-block;margin:0 6px 4px 0;padding:2px 8px;border-radius:999px;background-color:#f3f4f6;color:#4b5563;font-weight:bold;">{{ $followUp->priority->label() }}</span>

                        @if ($followUp->isOverdue())
                            <span style="display:inline-block;margin:0 6px 4px 0;padding:2px 8px;border-radius:999px;background-color:#fee2e2;color:#b91c1c;font-weight:bold;">Atrasado</span>
                        @endif
                    </p>

                    <table style="width:100%;border-collapse:collapse;font-size:12px;color:#4b5563;">
                        <tr>
                            <td style="padding:2px 0;width:50%;"><strong>Cliente:</strong> {{ $followUp->client?->name ?? 'Não informado' }}</td>
                            <td style="padding:2px 0;"><strong>Projeto:</strong> {{ $followUp->project?->name ?? 'Não informado' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:2px 0;"><strong>Responsável:</strong> {{ $followUp->assignee?->full_name ?? 'Não atribuído' }}</td>
                            <td style="padding:2px 0;{{ $followUp->isOverdue() ? 'color:#dc2626;font-weight:bold;' : '' }}"><strong>Prazo:</strong> {{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}</td>
                        </tr>
                    </table>
                </div>
            @endforeach

            <p style="margin:24px 0 0;padding-top:16px;border-top:1px solid #eef0f4;font-size:12px;color:#9ca3af;">
                A lista completa também está disponível no anexo Excel deste e-mail.
            </p>
        </div>
    </div>
</body>
</html>
