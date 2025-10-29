<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud cancelada - Parkeando</title>
</head>
<body style="background-color: #f9fafb; font-family: Arial, sans-serif; padding: 40px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; padding: 32px; text-align: center; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <tr>
            <td>
                <img src="https://parkeando.es/logo.png" alt="Logo Parkeando" width="120" style="margin-bottom: 20px;">

                <h1 style="color: #111827;">¡Hola {{ $username }}!</h1>

                <p style="color: #374151;">Tu solicitud de eliminación de cuenta ha sido <strong>cancelada exitosamente</strong>.</p>

                <p style="color: #374151;">Tu cuenta y todos tus datos permanecen intactos.</p>

                <p><strong>ID de solicitud:</strong> {{ $deleteRequest->id }}</p>
                <p><strong>Fecha de cancelación:</strong> {{ $deleteRequest->cancelled_at->format('d/m/Y H:i') }}</p>

                <hr style="margin: 24px 0; border: none; border-top: 1px solid #e5e7eb;">

                <p style="text-align: left; color: #374151;">
                    <strong>¿Qué significa esto?</strong><br>
                    • Tu cuenta sigue activa y funcional<br>
                    • Todos tus datos están seguros<br>
                    • Puedes seguir usando Parkeando normalmente<br>
                    • Si cambias de opinión, podrás solicitar la eliminación nuevamente
                </p>

                <p style="color: #6b7280;">Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>
                <p style="font-weight: 600;">¡Gracias por seguir con nosotros!</p>
            </td>
        </tr>
    </table>
</body>
</html>
