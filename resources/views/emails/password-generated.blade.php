<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de Acceso - Linhir</title>
    <style>
        body {
            background-color: #121212;
            color: #E8E8E8;
            font-family: 'Figtree', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #1D1D1D;
        }
        .header {
            background-color: #252525;
            padding: 20px;
            text-align: center;
            border-bottom: 2px solid #C54B47;
        }
        .logo {
            color: #F0E6D2;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
        }
        .password-box {
            background-color: #252525;
            border: 1px solid #9A8667;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
            margin: 25px 0;
            font-size: 22px;
            letter-spacing: 2px;
            color: #F0E6D2;
        }
        .button {
            display: inline-block;
            background-color: #C54B47;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin: 15px 0;
        }
        .button:hover {
            background-color: #A93C38;
        }
        .footer {
            background-color: #252525;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6D6D6D;
            border-top: 1px solid #252525;
        }
        .accent {
            color: #D4B44A;
        }
        .divider {
            height: 1px;
            background-color: #252525;
            margin: 25px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">LINHIR</div>
        </div>
        
        <div class="content">
            <h2>Hola {{ $nombre }},</h2>
            <p>Te damos la bienvenida al gremio <span class="accent">Linhir</span>. Se ha generado una contraseña para que puedas acceder a tu cuenta.</p>
            
            <div class="divider"></div>
            
            <p>Tu contraseña de acceso es:</p>
            <div class="password-box">{{ $password }}</div>
            
            <p>Te recomendamos cambiar esta contraseña después de iniciar sesión por primera vez.</p>
            
            <div style="text-align: center;">
                <a href="{{ url('https://linhir.online/') }}" class="button">Iniciar Sesión</a>
            </div>
            
            <div class="divider"></div>
            
            <p>Si tienes problemas para acceder, no dudes en contactar con los administradores del gremio.</p>
            <p>¡Te esperamos en <span class="accent">Linhir</span>!</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Linhir. Todos los derechos reservados.</p>
            <p>Este es un mensaje automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>