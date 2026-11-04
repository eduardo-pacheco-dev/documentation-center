<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $followUp->title }}</title>
</head>
<body style="margin:0;padding:24px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#4f46e5;">Follow-up</p>

        <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;">{{ $followUp->title }}</h1>

        <p style="margin:0 0 20px;font-size:13px;color:#6b7280;">
            Status: <strong style="color:#111827;">{{ $followUp->status->label() }}</strong>
            &nbsp;·&nbsp;
            Prioridade: <strong style="color:#111827;">{{ $followUp->priority->label() }}</strong>
            @if ($followUp->isOverdue())
                &nbsp;·&nbsp; <strong style="color:#dc2626;">Atrasado</strong>
            @endif
        </p>

        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <tr>
                <td style="padding:6px 0;color:#6b7280;width:38%;">Responsável</td>
                <td style="padding:6px 0;font-weight:bold;">{{ $followUp->assignee?->full_name ?? 'Não atribuído' }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#6b7280;">Prazo</td>
                <td style="padding:6px 0;font-weight:bold;">{{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#6b7280;">Criado por</td>
                <td style="padding:6px 0;">{{ $followUp->creator?->full_name ?? 'Usuário removido' }} em {{ $followUp->created_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#6b7280;">Atualizado em</td>
                <td style="padding:6px 0;">{{ $followUp->updated_at?->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td style="padding:6px 0;color:#6b7280;">Concluído em</td>
                <td style="padding:6px 0;">{{ $followUp->completed_at?->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
        </table>

        @if ($followUp->description)
            <h2 style="margin:24px 0 8px;font-size:14px;color:#111827;">Descrição</h2>
            <p style="margin:0;font-size:13px;line-height:1.6;white-space:pre-line;">{{ $followUp->description }}</p>
        @endif

        <h2 style="margin:24px 0 8px;font-size:14px;color:#111827;">Histórico</h2>

        @forelse ($followUp->updates as $update)
            <div style="border-top:1px solid #f3f4f6;padding:12px 0;">
                <p style="margin:0 0 4px;font-size:12px;color:#6b7280;">
                    <strong style="color:#111827;">{{ $update->user?->full_name ?? 'Usuário removido' }}</strong>
                    · {{ $update->created_at?->format('d/m/Y H:i') }}
                    @if ($update->status_from && $update->status_to)
                        · {{ $update->status_from->label() }} → {{ $update->status_to->label() }}
                    @endif
                </p>
                <p style="margin:0;font-size:13px;line-height:1.6;white-space:pre-line;">{{ $update->body }}</p>
            </div>
        @empty
            <p style="margin:0;font-size:13px;color:#6b7280;">Nenhuma atualização registrada.</p>
        @endforelse

        <p style="margin:24px 0 0;font-size:12px;color:#9ca3af;">
            O resumo completo também está disponível no anexo Excel deste e-mail.
        </p>
    </div>
</body>
</html>
