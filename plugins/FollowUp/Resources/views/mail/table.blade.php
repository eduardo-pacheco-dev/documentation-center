<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Follow-ups</title>
</head>
<body style="margin:0;padding:24px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:720px;margin:0 auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#4f46e5;">Follow-ups</p>

        <h1 style="margin:0 0 6px;font-size:22px;line-height:1.3;">Tabela de follow-ups</h1>

        <p style="margin:0 0 20px;font-size:13px;color:#6b7280;">
            {{ $followUps->count() }} follow-up(s) conforme os filtros aplicados.
        </p>

        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background-color:#f9fafb;color:#6b7280;text-align:left;">
                    <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Título</th>
                    <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Status</th>
                    <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Prioridade</th>
                    <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Responsável</th>
                    <th style="padding:8px;border-bottom:1px solid #e5e7eb;">Prazo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($followUps as $followUp)
                    <tr>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;font-weight:bold;">{{ $followUp->title }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $followUp->status->label() }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $followUp->priority->label() }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $followUp->assignee?->full_name ?? 'Não atribuído' }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;{{ $followUp->isOverdue() ? 'color:#dc2626;font-weight:bold;' : '' }}">
                            {{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}
                            @if ($followUp->isOverdue())
                                (atrasado)
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:16px;text-align:center;color:#6b7280;">Nenhum follow-up encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p style="margin:24px 0 0;font-size:12px;color:#9ca3af;">
            A mesma tabela está disponível no anexo Excel deste e-mail.
        </p>
    </div>
</body>
</html>
