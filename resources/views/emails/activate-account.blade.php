<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activar cuenta - Parkeando</title>
</head>
<body style="background-color: #f9fafb; font-family: Arial, sans-serif; padding: 40px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 32px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <tr>
            <td>
                <img src="https://parkeando.es/logo.png" alt="Logo Parkeando" width="120" style="margin-bottom: 20px;">

                <h1 style="color: #111827;">¡Hola {{ $username }}!</h1>

                <p style="color: #374151;">Gracias por registrarte en <strong>Parkeando</strong>.</p>
                <p style="color: #374151;">Para activar tu cuenta, haz clic en el siguiente botón:</p>

                <a href="{{ $activationUrl }}" style="background-color: #1d4ed8; color: #ffffff; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-block; margin: 20px 0;">
                    Activar cuenta
                </a>

                <p style="color: #6b7280;">Si tú no creaste esta cuenta, puedes ignorar este mensaje.</p>
                <p style="font-weight: 600;">¡Gracias por usar Parkeando!</p>
            </td>
        </tr>
    </table>
</body>
</html>
