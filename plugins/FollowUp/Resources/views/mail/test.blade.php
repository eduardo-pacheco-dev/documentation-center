<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste de envio</title>
</head>
<body style="margin:0;padding:24px;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <div style="max-width:560px;margin:0 auto;overflow:hidden;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:18px;">
        <div style="height:6px;background-color:#4f46e5;"></div>

        <div style="padding:28px;">
            <p style="margin:0 0 6px;font-size:11px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#4f46e5;">Follow-up</p>

            <h1 style="margin:0 0 12px;font-size:21px;line-height:1.3;color:#111827;">Teste de envio de e-mail</h1>

            <p style="margin:0 0 16px;font-size:14px;line-height:1.7;color:#374151;">
                Se você recebeu esta mensagem, o servidor de e-mail configurado em
                <strong>{{ config('app.name') }}</strong> está funcionando.
            </p>

            <div style="padding:12px 14px;border-radius:12px;background-color:#ecfdf5;border:1px solid #a7f3d0;color:#047857;font-size:13px;font-weight:bold;">
                Configuração de e-mail validada com sucesso.
            </div>

            <p style="margin:20px 0 0;font-size:12px;color:#9ca3af;">
                Enviado em {{ now()->format('d/m/Y H:i') }}.
            </p>
        </div>
    </div>
</body>
</html>
