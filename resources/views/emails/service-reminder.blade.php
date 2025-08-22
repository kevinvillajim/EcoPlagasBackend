<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Servicio - EcoPlagas</title>
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
        
        .alert-info {
            background-color: #eff6ff;
            border: 2px solid #3b82f6;
            color: #1e40af;
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
            background-color: #25D366;
            color: white;
            box-shadow: 0 2px 8px rgba(37, 211, 102, 0.3);
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
                <div class="company-tagline">Su Cita Está Programada</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Estimado/a <strong>{{ $client->name }}</strong>,
            </div>
            
            <div class="content-text">
                Le escribimos para recordarle que tiene un servicio de control de plagas programado con <strong>EcoPlagas Soluciones</strong>.
            </div>
            
            <!-- Alert del recordatorio -->
            <div class="alert-box alert-info">
                <span class="alert-icon">⏰</span>
                <div class="alert-title">
                    @if($reminderType === 'three-days')
                        ¡Su cita es en 3 días!
                    @else
                        ¡Su cita es HOY!
                    @endif
                </div>
                <div class="alert-message">
                    @if($reminderType === 'three-days')
                        Este es un recordatorio anticipado para que pueda prepararse para el servicio.
                    @else
                        Por favor, asegúrese de estar disponible en el horario acordado.
                    @endif
                </div>
            </div>
            
            <!-- Información del servicio -->
            <div class="info-card">
                <div class="info-card-title">📅 Detalles de su Cita</div>
                
                <div class="info-row">
                    <span class="info-label">Fecha:</span>
                    <span class="info-value" style="font-weight: bold;">{{ \Carbon\Carbon::parse($service->scheduled_date)->format('l, d \\de F \\de Y') }}</span>
                </div>
                
                @if($service->scheduled_time)
                <div class="info-row">
                    <span class="info-label">Hora:</span>
                    <span class="info-value" style="font-weight: bold;">{{ $service->scheduled_time }}</span>
                </div>
                @endif
                
                <div class="info-row">
                    <span class="info-label">Tipo de Servicio:</span>
                    <span class="info-value">{{ ucfirst($service->type) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">{{ $service->address }}</span>
                </div>
                
                @if($service->description)
                <div class="info-row">
                    <span class="info-label">Descripción:</span>
                    <span class="info-value">{{ $service->description }}</span>
                </div>
                @endif
                
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value" style="color: #2563eb; font-weight: 600;">Confirmado</span>
                </div>
            </div>
            
            <!-- Preparativos -->
            <div class="alert-box alert-success">
                <span class="alert-icon">📝</span>
                <div class="alert-title">Preparativos para el Servicio</div>
                <div class="alert-message" style="text-align: left; margin-top: 15px;">
                    <strong style="color: #065f46;">• Acceso libre:</strong> Asegúrese de que nuestros técnicos puedan acceder a todas las áreas a tratar<br><br>
                    <strong style="color: #065f46;">• Mascotas:</strong> Mantenga a las mascotas alejadas durante el tratamiento<br><br>
                    <strong style="color: #065f46;">• Alimentos:</strong> Cubra o retire alimentos y utensilios de cocina<br><br>
                    <strong style="color: #065f46;">• Disponibilidad:</strong> Esté presente para coordinar con nuestro técnico
                </div>
            </div>
            
            <!-- Botones de contacto -->
            <div class="button-container">
                <div class="content-text" style="font-weight: bold; text-align: center; margin-bottom: 16px;">
                    ¿Necesita reprogramar o tiene consultas?
                </div>
                <p style="text-align: center; margin-bottom: 24px;">Contáctenos con anticipación para cualquier cambio o consulta.</p>
                
                <a href="tel:+593995031066" class="btn btn-primary">📞 Llamar</a>
                <a href="https://wa.me/593995031066?text=Hola, necesito consultar sobre mi cita programada" class="btn btn-secondary">💬 WhatsApp</a>
                
                @if($reminderType !== 'same-day')
                <a href="#" class="btn btn-outline">📅 Reprogramar</a>
                @endif
            </div>
            
            <!-- Información adicional -->
            @if($reminderType === 'same-day')
            <div class="alert-box alert-warning">
                <span class="alert-icon">⚡</span>
                <div class="alert-title">Importante - Servicio HOY</div>
                <div class="alert-message">
                    Nuestro técnico llegará en el horario acordado. Por favor, mantenga su teléfono disponible por si necesitamos contactarle.
                </div>
            </div>
            @endif
            
            <!-- Información de contacto -->
            <div class="contact-card">
                <div class="contact-title">📞 Información de Contacto</div>
                
                <div class="contact-item">
                    <span class="contact-icon">📱</span>
                    <strong>Emergencias:</strong> +593 99 503 1066
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">📱</span>
                    <strong>Oficina:</strong> +593 99 107 9118
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <strong>Email:</strong> <a href="mailto:info@ecoplagasecuador.com">info@ecoplagasecuador.com</a>
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">🕐</span>
                    <strong>Atención:</strong> 24/7 para emergencias
                </div>
            </div>
            
            <div class="content-text">
                Gracias por elegirnos como su proveedor de confianza en control de plagas. Nuestro equipo está preparado para brindarle un servicio de excelencia.
            </div>
            
            <div class="content-text" style="margin-top: 30px;">
                <strong>Atentamente,</strong><br>
                El equipo de EcoPlagas Soluciones<br>
                <em style="color: #64748b;">Listos para proteger su espacio</em>
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
                Este es un recordatorio automático. Si no puede atender la cita, por favor contáctenos con anticipación.
            </div>
        </div>
    </div>
</body>
</html>