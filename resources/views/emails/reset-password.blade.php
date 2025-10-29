<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Parkeando</title>
</head>
<body style="background-color: #f9fafb; font-family: Arial, sans-serif; padding: 40px;">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 32px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <tr>
            <td>
                <img src="https://parkeando.es/logo.png" alt="Logo Parkeando" width="120" style="margin-bottom: 20px;">

                <h1 style="color: #111827; font-size: 22px; margin-bottom: 16px;">¡Hola {{ $username }}!</h1>

                <p style="color: #374151; font-size: 16px; line-height: 1.6;">
                    Recibimos una solicitud para restablecer la contraseña de tu cuenta.<br>
                    Si solicitaste este cambio, haz clic en el botón de abajo para crear una nueva contraseña:
                </p>

                <a href="{{ $url }}" style="
                    background-color: #1d4ed8;
                    color: #ffffff;
                    padding: 14px 28px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-weight: bold;
                    display: inline-block;
                    margin: 20px 0;
                ">
                    Restablecer Contraseña
                </a>

                <p style="color: #6b7280; font-size: 14px; line-height: 1.6;">
                    Este enlace expirará en <strong>60 minutos</strong>.<br>
                    Si no solicitaste este cambio, puedes ignorar este email.
                </p>

                <p style="color: #111827; font-weight: 600; margin-top: 24px;">
                    ¡Gracias por usar Parkeando!
                </p>
            </td>
        </tr>
    </table>

</body>
</html>
