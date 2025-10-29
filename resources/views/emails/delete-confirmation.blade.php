<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de eliminación - Parkeando</title>
</head>
<body style="background-color: #f9fafb; font-family: Arial, sans-serif; padding: 40px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 32px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <tr>
            <td>
                <img src="https://parkeando.es/logo.png" alt="Logo Parkeando" width="120" style="margin-bottom: 20px;">

                <h1 style="color: #111827;">¡Hola {{ $username }}!</h1>

                <p style="color: #374151;">Hemos recibido tu solicitud de eliminación de cuenta y datos personales.</p>

                <p><strong>Motivo:</strong> {{ $reasonText }}</p>

                @if ($additionalInfo)
                    <p><strong>Información adicional:</strong> {{ $additionalInfo }}</p>
                @endif

                <p style="color: #b91c1c; font-weight: bold;">
                    IMPORTANTE: Para proceder con la eliminación, debes confirmar tu solicitud.
                </p>

                <a href="{{ $confirmationUrl }}" style="background-color: #1d4ed8; color: #ffffff; padding: 14px 28px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 16px 0;">Confirmar eliminación</a>

                <p style="color: #6b7280; margin-top: 20px;">Durante los próximos 30 días podrás cancelar la solicitud:</p>

                <a href="{{ $cancellationUrl }}" style="background-color: #e5e7eb; color: #111827; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block;">Cancelar solicitud</a>

                <hr style="margin: 24px 0; border: none; border-top: 1px solid #e5e7eb;">

                <p style="text-align: left; color: #374151;">
                    <strong>¿Qué se eliminará?</strong><br>
                    • Tu cuenta de usuario<br>
                    • Todos tus datos personales<br>
                    • Historial de aparcamiento<br>
                    • Vehículos registrados<br>
                    • Notificaciones y preferencias
                </p>

                <p style="color: #6b7280;">Si no solicitaste esta eliminación, ignora este mensaje.</p>
                <p style="font-weight: 600;">Gracias por usar Parkeando</p>
            </td>
        </tr>
    </table>
</body>
</html>
