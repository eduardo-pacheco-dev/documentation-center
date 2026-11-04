<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Resumo de follow-ups</title>
</head>
<body style="margin:0;padding:24px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:640px;margin:0 auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#4f46e5;">Resumo periódico</p>

        <h1 style="margin:0 0 6px;font-size:22px;line-height:1.3;">Follow-ups em aberto</h1>

        <p style="margin:0 0 20px;font-size:13px;color:#6b7280;">
            Olá, {{ $user->full_name }}. Você tem {{ $followUps->count() }} follow-up(s) em aberto.
        </p>

        @foreach ($followUps as $followUp)
            <div style="border-top:1px solid #e5e7eb;padding:12px 0;">
                <p style="margin:0 0 4px;font-size:14px;font-weight:bold;">
                    {{ $followUp->title }}
                </p>
                <p style="margin:0;font-size:12px;color:#6b7280;">
                    Status: {{ $followUp->status->label() }}
                    · Prioridade: {{ $followUp->priority->label() }}
                    · Responsável: {{ $followUp->assignee?->full_name ?? 'Não atribuído' }}
                    · Prazo: {{ $followUp->due_date?->format('d/m/Y') ?? 'Sem prazo' }}
                    @if ($followUp->isOverdue())
                        · <strong style="color:#dc2626;">Atrasado</strong>
                    @endif
                </p>
            </div>
        @endforeach

        <p style="margin:24px 0 0;font-size:12px;color:#9ca3af;">
            A lista completa também está disponível no anexo Excel deste e-mail.
        </p>
    </div>
</body>
</html>
