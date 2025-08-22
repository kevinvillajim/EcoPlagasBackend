<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activación de Cuenta - EcoPlagas</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        
        .email-container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        
        .header {
            background-color: #6BB544;
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .logo-container {
            position: relative;
            z-index: 2;
        }
        
        .company-logo {
            height: 80px;
            width: auto;
            margin-bottom: 15px;
            filter: brightness(1.1);
        }
        
        .company-tagline {
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.95;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .content-text {
            font-size: 16px;
            line-height: 1.7;
            color: #4a5568;
            margin-bottom: 20px;
        }
        
        .alert-box {
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        
        .alert-success {
            background-color: #f0fdf4;
            border: 2px solid #22c55e;
            color: #15803d;
        }
        
        .alert-warning {
            background-color: #fef7e6;
            border: 2px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-icon {
            font-size: 42px;
            margin-bottom: 12px;
            display: block;
        }
        
        .alert-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .alert-message {
            font-size: 16px;
            line-height: 1.5;
        }
        
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin: 20px 0;
        }
        
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 16px 32px;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            margin: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: #6BB544;
            color: white;
            box-shadow: 0 2px 8px rgba(107, 181, 68, 0.3);
        }
        
        .contact-card {
            background-color: #f0f9ff;
            border: 1px solid #93c5fd;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .contact-title {
            font-size: 18px;
            font-weight: 700;
            color: #0369a1;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .footer {
            background-color: #1f2937;
            color: #d1d5db;
            text-align: center;
            padding: 30px;
        }
        
        .footer-links {
            margin: 15px 0;
        }
        
        .footer-links a {
            color: #6BB544;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 500;
        }
        
        .footer-disclaimer {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 20px;
            line-height: 1.5;
        }
        
        @media only screen and (max-width: 600px) {
            body { padding: 10px; }
            .header, .content { padding: 25px 20px; }
            .company-logo { height: 60px; }
            .btn { display: block; margin: 10px 0; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="https://ecoplagasecuador.com/logoEcoPlagasNegro.png" alt="EcoPlagas Logo" class="company-logo">
                <div class="company-tagline">Bienvenido a EcoPlagas</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hola <strong>{{ $user->name }}</strong>,
            </div>
            
            <div class="content-text">
                Tu cuenta ha sido creada exitosamente en nuestro sistema de <strong>EcoPlagas Soluciones</strong>. Para completar el proceso de registro, necesitas activar tu cuenta y establecer tu contraseña.
            </div>
            
            <!-- Welcome alert -->
            <div class="alert-box alert-success">
                <span class="alert-icon">🎉</span>
                <div class="alert-title">¡Cuenta Creada Exitosamente!</div>
                <div class="alert-message">
                    Solo falta un paso más para completar tu registro
                </div>
            </div>
            
            <!-- Activation button -->
            <div class="button-container">
                <div class="content-text" style="font-weight: bold; text-align: center; margin-bottom: 16px;">
                    Haz clic en el siguiente botón para activar tu cuenta:
                </div>
                
                <a href="{{ $activationUrl }}" class="btn btn-primary">🔐 Activar Mi Cuenta</a>
            </div>
            
            <!-- Important warning -->
            <div class="alert-box alert-warning">
                <span class="alert-icon">⚠️</span>
                <div class="alert-title">Importante</div>
                <div class="alert-message">
                    Este enlace es válido por <strong>24 horas</strong> y solo puede ser utilizado una vez.
                </div>
            </div>
            
            <!-- Alternative link -->
            <div class="contact-card">
                <div class="contact-title">¿Problemas con el botón?</div>
                <div style="text-align: center; margin: 15px 0;">
                    <p style="margin: 10px 0; font-size: 14px;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; word-break: break-all; font-family: monospace; font-size: 13px; color: #475569;">
                        {{ $activationUrl }}
                    </div>
                </div>
            </div>
            
            <!-- Benefits -->
            <div class="info-card">
                <div style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 15px;">🌟 Una vez que actives tu cuenta, podrás:</div>
                
                <div style="padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #6BB544; margin-right: 8px;">✓</span>
                    <span style="color: #1e293b;">Acceder a tu panel de control personalizado</span>
                </div>
                
                <div style="padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #6BB544; margin-right: 8px;">✓</span>
                    <span style="color: #1e293b;">Solicitar servicios de control de plagas</span>
                </div>
                
                <div style="padding: 12px 0; border-bottom: 1px solid #e2e8f0;">
                    <span style="color: #6BB544; margin-right: 8px;">✓</span>
                    <span style="color: #1e293b;">Ver el historial de tus servicios</span>
                </div>
                
                <div style="padding: 12px 0;">
                    <span style="color: #6BB544; margin-right: 8px;">✓</span>
                    <span style="color: #1e293b;">Descargar certificados y reportes</span>
                </div>
            </div>
            
            <div class="content-text">
                Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos. Nuestro equipo está aquí para ayudarte.
            </div>
            
            <div class="content-text" style="margin-top: 30px;">
                <strong>Atentamente,</strong><br>
                El equipo de EcoPlagas Soluciones<br>
                <em style="color: #64748b;">Tu aliado en el control de plagas</em>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ date('Y') }} EcoPlagas Soluciones. Todos los derechos reservados.</p>
            <div class="footer-links">
                <a href="https://www.ecoplagasecuador.com">www.ecoplagasecuador.com</a> |
                <a href="mailto:info@ecoplagasecuador.com">info@ecoplagasecuador.com</a>
            </div>
            <div class="footer-disclaimer">
                Este es un mensaje automático de activación. Si no solicitaste esta cuenta, puedes ignorar este mensaje de forma segura.
            </div>
        </div>
    </div>
</body>
</html>