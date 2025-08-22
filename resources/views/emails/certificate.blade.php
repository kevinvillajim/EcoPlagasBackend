<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Control de Plagas - EcoPlagas</title>
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
        
        .certificate-number {
            background-color: #6BB544;
            color: white;
            padding: 16px;
            text-align: center;
            border-radius: 10px;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 20px;
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
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <img src="https://ecoplagasecuador.com/logoEcoPlagasNegro.png" alt="EcoPlagas Logo" class="company-logo">
                <div class="company-tagline">Certificado de Control de Plagas</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Estimado/a <strong>{{ $userName }}</strong>,
            </div>
            
            <div class="content-text">
                Nos complace informarle que su certificado de control de plagas ha sido generado exitosamente por <strong>{{ $companyName }}</strong>.
            </div>
            
            <!-- Success alert -->
            <div class="alert-box alert-success">
                <span class="alert-icon">🎉</span>
                <div class="alert-title">¡Certificado Generado Exitosamente!</div>
                <div class="alert-message">
                    Su certificado oficial está listo y adjunto a este correo
                </div>
            </div>
            
            <!-- Certificate details -->
            <div class="info-card">
                <div class="certificate-number">
                    {{ $certificateNumber }}
                </div>
                
                <div class="info-card-title">📋 Información del Certificado</div>
                
                <div class="info-row">
                    <span class="info-label">Tipo de Certificado:</span>
                    <span class="info-value">{{ $certificateType }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Fecha de Emisión:</span>
                    <span class="info-value">{{ $issueDate }}</span>
                </div>
                
                <div class="info-row">
                    <span class="info-label">Servicio Realizado:</span>
                    <span class="info-value">{{ $serviceType }}</span>
                </div>
            </div>
            
            <!-- Validity info -->
            <div class="alert-box alert-info">
                <span class="alert-icon">✅</span>
                <div class="alert-title">Validez del Certificado</div>
                <div class="alert-message">
                    <strong>Válido hasta: {{ $validUntil }}</strong><br>
                    Este certificado es válido para inspecciones y auditorías hasta la fecha indicada.
                </div>
            </div>
            
            <!-- Attachment info -->
            <div class="info-card">
                <div class="info-card-title">📎 Certificado Adjunto</div>
                <div class="content-text">
                    Su certificado oficial en formato PDF está adjunto a este correo electrónico.
                </div>
                <div class="info-row">
                    <span class="info-label">Nombre del archivo:</span>
                    <span class="info-value" style="font-family: monospace;">certificado_{{ $certificateNumber }}.pdf</span>
                </div>
            </div>
            
            <!-- Important warning -->
            <div class="alert-box alert-warning">
                <span class="alert-icon">⚠️</span>
                <div class="alert-title">Importante</div>
                <div class="alert-message" style="text-align: left;">
                    <strong>• Conserve</strong> este certificado en un lugar seguro<br>
                    <strong>• Presente</strong> el certificado cuando sea requerido por autoridades<br>
                    <strong>• Programe</strong> una renovación antes de la fecha de vencimiento<br>
                    <strong>• En caso de pérdida,</strong> puede solicitar una copia en nuestras oficinas
                </div>
            </div>
            
            <!-- Contact info -->
            <div class="contact-card">
                <div class="contact-title">📞 ¿Necesita Asistencia?</div>
                
                <div class="content-text" style="text-align: center; margin-bottom: 16px;">
                    Si tiene alguna pregunta sobre su certificado o necesita programar una renovación:
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">📱</span>
                    <strong>Teléfono:</strong> +593 99 503 1066
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <strong>Email:</strong> <a href="mailto:info@ecoplagasecuador.com">info@ecoplagasecuador.com</a>
                </div>
                
                <div class="contact-item">
                    <span class="contact-icon">🕐</span>
                    <strong>Horario:</strong> Lunes a Viernes, 8:00 AM - 6:00 PM
                </div>
            </div>
            
            <div class="content-text">
                Gracias por confiar en <strong>{{ $companyName }}</strong> para sus necesidades de control de plagas.
            </div>
            
            <div class="content-text" style="margin-top: 30px;">
                <strong>Atentamente,</strong><br>
                El equipo de {{ $companyName }}<br>
                <em style="color: #64748b;">Su socio en control de plagas</em>
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
                Certificado generado automáticamente el {{ now()->format('d/m/Y H:i') }}. Este mensaje fue enviado automáticamente.
            </div>
        </div>
    </div>
</body>
</html>