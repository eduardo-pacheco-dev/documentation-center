<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Teste de envio</title>
</head>
<body style="margin:0;padding:24px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:560px;margin:0 auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:16px;padding:28px;">
        <p style="margin:0 0 4px;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;color:#4f46e5;">Follow-up</p>

        <h1 style="margin:0 0 12px;font-size:20px;line-height:1.3;">Teste de envio de e-mail</h1>

        <p style="margin:0 0 12px;font-size:14px;line-height:1.6;">
            Se você recebeu esta mensagem, o servidor de e-mail configurado em
            <strong>{{ config('app.name') }}</strong> está funcionando.
        </p>

        <p style="margin:0;font-size:12px;color:#6b7280;">
            Enviado em {{ now()->format('d/m/Y H:i') }}.
        </p>
    </div>
</body>
</html>
