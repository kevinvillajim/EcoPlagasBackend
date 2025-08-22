<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Fecha/Hora de Servicio - EcoPlagas</title>
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
        
        .change-highlight {
            background-color: #fef2f2;
            border: 2px solid #ef4444;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        
        .old-info {
            color: #dc2626;
            text-decoration: line-through;
            font-weight: 500;
        }
        
        .new-info {
            color: #16a34a;
            font-weight: 700;
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
                <div class="company-tagline">Cambio en Su Cita de Servicio</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Estimado/a <strong>{{ $user->name }}</strong>,
            </div>
            
            <div class="content-text">
                Le informamos que hemos modificado la fecha y/u hora de su servicio de control de plagas con <strong>{{ $companyName }}</strong>. Los detalles del cambio se encuentran a continuación.
            </div>
            
            <!-- Alert de cambio -->
            <div class="alert-box alert-warning">
                <span class="alert-icon">📅</span>
                <div class="alert-title">¡Importante! Cambio de Fecha/Hora</div>
                <div class="alert-message">
                    Hemos realizado modificaciones en su cita programada. Revise los nuevos detalles.
                </div>
            </div>
            
            @if($oldDate || $oldTime)
            <!-- Información del cambio -->
            <div class="change-highlight">
                <div class="info-card-title">🔄 Información del Cambio</div>
                
                @if($oldDate)
                <div class="info-row">
                    <span class="info-label">Fecha anterior:</span>
                    <span class="old-info">
                        @php
                            $oldDateTime = \Carbon\Carbon::parse($oldDate);
                            $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                            $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                        @endphp
                        {{ $dayNames[$oldDateTime->dayOfWeek] }}, {{ $oldDateTime->day }} de {{ $monthNames[$oldDateTime->month - 1] }} de {{ $oldDateTime->year }}
                    </span>
                </div>
                @endif
                
                @if($oldTime)
                <div class="info-row">
                    <span class="info-label">Hora anterior:</span>
                    <span class="old-info">{{ $oldTime }}</span>
                </div>
                @endif
            </div>
            @endif
            
            <!-- Nueva información del servicio -->
            <div class="info-card">
                <div class="info-card-title">✅ Nueva Información del Servicio</div>
                
                <div class="info-row">
                    <span class="info-label">Tipo de Servicio:</span>
                    <span class="info-value">{{ ucfirst($service->type) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Nueva Fecha:</span>
                    <span class="new-info">
                        @php
                            $newDateTime = \Carbon\Carbon::parse($service->scheduled_date);
                            $dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                            $monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                        @endphp
                        {{ $dayNames[$newDateTime->dayOfWeek] }}, {{ $newDateTime->day }} de {{ $monthNames[$newDateTime->month - 1] }} de {{ $newDateTime->year }}
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Nueva Hora:</span>
                    <span class="new-info">{{ $service->scheduled_time }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">{{ $service->address }}</span>
                </div>
                
                @if($service->technician)
                <div class="info-row">
                    <span class="info-label">Técnico Asignado:</span>
                    <span class="info-value">{{ $service->technician->name }}</span>
                </div>
                @endif
                
                @if($service->cost)
                <div class="info-row">
                    <span class="info-label">Costo:</span>
                    <span class="info-value">${{ number_format($service->cost, 2) }}</span>
                </div>
                @endif
            </div>
            
            <!-- Instrucciones -->
            <div class="alert-box alert-info">
                <span class="alert-icon">📝</span>
                <div class="alert-title">¿Qué debe hacer ahora?</div>
                <div class="alert-message" style="text-align: left; margin-top: 15px;">
                    <strong style="color: #1e40af;">✅ Confirme</strong> su disponibilidad para la nueva fecha y hora<br><br>
                    <strong style="color: #1e40af;">📞 Contáctenos</strong> inmediatamente si tiene algún inconveniente<br><br>
                    <strong style="color: #1e40af;">📝 Anote</strong> la nueva fecha en su calendario personal<br><br>
                    <strong style="color: #1e40af;">🏠 Asegúrese</strong> de estar disponible en la dirección indicada
                </div>
            </div>
            
            <!-- Botones de contacto -->
            <div class="button-container">
                <div class="content-text" style="font-weight: bold; text-align: center; margin-bottom: 16px;">
                    ¿Necesita reprogramar o tiene dudas?
                </div>
                
                <a href="tel:{{ $companyPhone }}" class="btn btn-primary">📞 Llamar</a>
                <a href="https://wa.me/{{ str_replace(['+', '-', ' '], '', $companyPhone) }}?text=Hola, necesito consultar sobre el cambio de fecha de mi servicio" class="btn btn-secondary">💬 WhatsApp</a>
            </div>
            
            <!-- Información de contacto -->
            <div class="contact-card">
                <div class="contact-title">📞 Información de Contacto</div>
                
                <div class="contact-item">
                    <span class="contact-icon">📱</span>
                    <strong>Teléfono:</strong> {{ $companyPhone }}
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <strong>Email:</strong> <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a>
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">🕐</span>
                    <strong>Horarios:</strong> Lunes a Viernes, 8:00 AM - 6:00 PM
                </div>
            </div>
            
            <!-- Nota de seguridad -->
            <div class="info-card">
                <div style="font-size: 14px; color: #64748b; text-align: center;">
                    <strong>Nota importante:</strong> Si usted no solicitó este cambio y tiene dudas sobre la autenticidad de este correo, 
                    por favor contáctenos directamente a nuestros números oficiales.
                </div>
            </div>
            
            <div class="content-text">
                Gracias por confiar en <strong>{{ $companyName }}</strong>. Su satisfacción es nuestra prioridad.
            </div>
            
            <div class="content-text" style="margin-top: 30px;">
                <strong>Atentamente,</strong><br>
                El equipo de {{ $companyName }}<br>
                <em style="color: #64748b;">Siempre atentos a servirle</em>
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
                Este es un email automático de cambio de cita. Si no solicitó este cambio, contáctenos inmediatamente.
            </div>
        </div>
    </div>
</body>
</html>