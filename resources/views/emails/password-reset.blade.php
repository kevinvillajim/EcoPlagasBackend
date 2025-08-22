<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Contraseña - EcoPlagas</title>
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
        
        .alert-warning {
            background-color: #fef7e6;
            border: 2px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-info {
            background-color: #eff6ff;
            border: 2px solid #3b82f6;
            color: #1e40af;
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
        
        .info-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }
        
        .info-value {
            color: #1e293b;
            font-weight: 500;
            text-align: right;
            max-width: 60%;
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
        
        .btn-secondary {
            background-color: #3b82f6;
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: #6BB544;
            border: 2px solid #6BB544;
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
        
        .contact-item {
            margin: 12px 0;
            font-size: 15px;
            color: #0f172a;
            display: flex;
            align-items: center;
        }
        
        .contact-icon {
            margin-right: 12px;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }
        
        .contact-item strong {
            color: #1e293b;
            margin-right: 8px;
        }
        
        .contact-item a {
            color: #0369a1;
            text-decoration: none;
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
            .info-row { flex-direction: column; align-items: flex-start; gap: 5px; }
            .info-value { max-width: 100%; text-align: left; }
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
                <div class="company-tagline">Restablecimiento de Contraseña</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hola <strong>{{ $user->name }}</strong>,
            </div>
            
            <div class="content-text">
                Hemos recibido una solicitud para restablecer la contraseña de su cuenta en <strong>EcoPlagas Soluciones</strong>.
            </div>
            
            <!-- Alert de seguridad -->
            <div class="alert-box alert-warning">
                <span class="alert-icon">🔐</span>
                <div class="alert-title">Solicitud de Restablecimiento</div>
                <div class="alert-message">
                    Por motivos de seguridad, este enlace expirará en <strong>60 minutos</strong>. Si no solicitó este cambio, puede ignorar este mensaje.
                </div>
            </div>
            
            <!-- Información de la cuenta -->
            <div class="info-card">
                <div class="info-card-title">👤 Información de la Cuenta</div>
                
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $user->email }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Fecha de solicitud:</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
            
            <!-- Botón principal -->
            <div class="button-container">
                <div class="content-text" style="font-weight: bold; text-align: center; margin-bottom: 16px;">
                    Para restablecer su contraseña, haga clic en el siguiente botón:
                </div>
                
                <a href="{{ $resetUrl }}" class="btn btn-primary">🔑 Restablecer Contraseña</a>
                
                <div class="content-text" style="text-align: center; margin-top: 16px; font-size: 14px; color: #64748b;">
                    Este enlace expirará en 60 minutos por motivos de seguridad
                </div>
            </div>
            
            <!-- Instrucciones adicionales -->
            <div class="alert-box alert-info">
                <span class="alert-icon">💡</span>
                <div class="alert-title">Instrucciones</div>
                <div class="alert-message" style="text-align: left; margin-top: 15px;">
                    <strong style="color: #1e40af;">1.</strong> Haga clic en el botón "Restablecer Contraseña"<br><br>
                    <strong style="color: #1e40af;">2.</strong> Será redirigido a una página segura<br><br>
                    <strong style="color: #1e40af;">3.</strong> Ingrese su nueva contraseña<br><br>
                    <strong style="color: #1e40af;">4.</strong> Confirme la nueva contraseña e inicie sesión
                </div>
            </div>
            
            <!-- Problemas con el enlace -->
            <div class="contact-card">
                <div class="contact-title">¿Problemas con el enlace?</div>
                <div style="text-align: center; margin: 15px 0;">
                    <p style="margin: 10px 0; font-size: 14px;">Si el botón no funciona, copie y pegue este enlace en su navegador:</p>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; word-break: break-all; font-family: monospace; font-size: 13px; color: #475569;">
                        {{ $resetUrl }}
                    </div>
                </div>
            </div>
            
            <!-- Información de seguridad -->
            <div class="alert-box alert-warning">
                <span class="alert-icon">⚠️</span>
                <div class="alert-title">Importante - Seguridad</div>
                <div class="alert-message" style="text-align: left;">
                    • Si no solicitó este restablecimiento, ignore este mensaje<br>
                    • Nunca comparta este enlace con otras personas<br>
                    • Cree una contraseña segura con al menos 8 caracteres<br>
                    • Si tiene dudas, contáctenos inmediatamente
                </div>
            </div>
            
            <!-- Contacto para soporte -->
            <div class="button-container">
                <div class="content-text" style="text-align: center; margin-bottom: 16px;">
                    ¿Necesita ayuda?
                </div>
                
                <a href="tel:+593995031066" class="btn btn-secondary">📞 Soporte</a>
                <a href="mailto:info@ecoplagasecuador.com" class="btn btn-outline">✉️ Email</a>
            </div>
            
            <div class="content-text">
                Si no solicitó este restablecimiento de contraseña, puede ignorar este correo electrónico de forma segura. Su contraseña actual permanecerá sin cambios.
            </div>
            
            <div class="content-text" style="margin-top: 30px;">
                <strong>Atentamente,</strong><br>
                El equipo de Seguridad de EcoPlagas<br>
                <em style="color: #64748b;">Protegiendo su información</em>
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
                Este mensaje contiene un enlace de seguridad. Nunca comparta este enlace con terceros. Si no solicitó este restablecimiento, contacte soporte inmediatamente.
            </div>
        </div>
    </div>
</body>
</html>