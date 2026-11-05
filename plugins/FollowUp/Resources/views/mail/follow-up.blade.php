<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $followUp->title }}</title>
</head>
<body style="margin:0;padding:24px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;overflow:hidden;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:18px;">
        <div style="height:6px;background-color:#4f46e5;"></div>

        <div style="padding:28px;">
            <p style="margin:0 0 6px;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#4f46e5;">Follow-up</p>

            <h1 style="margin:0 0 12px;font-size:23px;line-height:1.3;color:#111827;">{{ $followUp->title }}</h1>

            <p style="margin:0 0 22px;font-size:13px;color:#6b7280;">
                <span style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;border-radius:999px;background-color:#eef2ff;color:#4338ca;font-weight:bold;">{{ $followUp->status->label() }}</span>
                <span style="display:inline-block;margin:0 6px 6px 0;padding:4px 10px;border-radius:999px;background-color:#f3f4f6;color:#4b5563;font-weight:bold;">Prioridade {{ $followUp->priority->label() }}</span>

                @if ($followUp->isOverdue())
                    <span style="display:inline-block;margin:0 0 6px;padding:4px 10px;border-radius:999px;background-color:#fee2e2;color:#b91c1c;font-weight:bold;">Atrasado</span>
                @endif
            </p>

            <table style="width:100%;border-collapse:collapse;font-size:13px;background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;">
                <tr>
                    <td style="padding:10px 14px;color:#6b7280;width:38%;border-bottom:1px solid #eef0f4;">Cliente</td>
                    <td style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #eef0f4;">{{ $followUp->client?->name ?? 'Não informado' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;color:#6b7280;border-bottom:1px solid #eef0f4;">Projeto</td>
                    <td style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #eef0f4;">{{ $followUp->project?->name ?? 'Não informado' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;color:#6b7280;border-bottom:1px solid #eef0f4;">Responsável</td>
                    <td style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #eef0f4;">{{ $followUp->assignee?->full_name ?? 'Não atribuído' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;color:#6b7280;border-bottom:1px solid #eef0f4;">Prazo</td>
                    <td style="padding:10px 14px;font-weight:bold;border-bottom:1px solid #eef0f4;{{ $followUp->isOverdue() ? 'color:#dc2626;' : '' }}">{{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;color:#6b7280;border-bottom:1px solid #eef0f4;">Criado por</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #eef0f4;">{{ $followUp->creator?->full_name ?? 'Usuário removido' }} em {{ $followUp->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;color:#6b7280;border-bottom:1px solid #eef0f4;">Atualizado em</td>
                    <td style="padding:10px 14px;border-bottom:1px solid #eef0f4;">{{ $followUp->updated_at?->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td style="padding:10px 14px;color:#6b7280;">Concluído em</td>
                    <td style="padding:10px 14px;">{{ $followUp->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            </table>

            @if ($followUp->description)
                <h2 style="margin:24px 0 8px;font-size:14px;color:#111827;">Descrição</h2>
                <p style="margin:0;font-size:13px;line-height:1.7;color:#374151;white-space:pre-line;">{{ $followUp->description }}</p>
            @endif

            <h2 style="margin:24px 0 8px;font-size:14px;color:#111827;">Histórico</h2>

            @forelse ($followUp->updates as $update)
                <div style="border-top:1px solid #eef0f4;padding:12px 0;">
                    <p style="margin:0 0 4px;font-size:12px;color:#6b7280;">
                        <strong style="color:#111827;">{{ $update->user?->full_name ?? 'Usuário removido' }}</strong>
                        &middot; {{ $update->created_at?->format('d/m/Y H:i') }}

                        @if ($update->status_from && $update->status_to)
                            &middot; {{ $update->status_from->label() }} &rarr; {{ $update->status_to->label() }}
                        @endif
                    </p>
                    <p style="margin:0;font-size:13px;line-height:1.6;color:#374151;white-space:pre-line;">{{ $update->body }}</p>
                </div>
            @empty
                <p style="margin:0;font-size:13px;color:#6b7280;">Nenhuma atualização registrada.</p>
            @endforelse

            <p style="margin:24px 0 0;padding-top:16px;border-top:1px solid #eef0f4;font-size:12px;color:#9ca3af;">
                O resumo completo também está disponível no anexo Excel deste e-mail.
            </p>
        </div>
    </div>
</body>
</html>
