<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EcoPlagas Soluciones')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8fafc;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .email-container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }
        
        /* Header with logo and gradient */
        .email-header {
            background: linear-gradient(135deg, #6BB544 0%, #5BB7E8 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1.5" fill="rgba(255,255,255,0.08)"/><circle cx="50" cy="10" r="0.8" fill="rgba(255,255,255,0.12)"/><circle cx="10" cy="60" r="1.2" fill="rgba(255,255,255,0.06)"/><circle cx="90" cy="30" r="0.6" fill="rgba(255,255,255,0.15)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.6;
            z-index: 1;
        }
        
        .logo-container {
            position: relative;
            z-index: 2;
        }
        
        .company-logo {
            height: 80px;
            width: auto;
            margin-bottom: 15px;
            filter: brightness(1.1) contrast(1.05);
            transition: transform 0.3s ease;
        }
        
        .company-tagline {
            color: rgba(255, 255, 255, 0.95);
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 10px;
        }
        
        /* Main content area */
        .email-content {
            padding: 40px 30px;
        }
        
        .email-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
            text-align: center;
        }
        
        .email-subtitle {
            font-size: 16px;
            color: #64748b;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .content-section {
            margin: 25px 0;
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
            margin-bottom: 18px;
        }
        
        /* Alert/Notification boxes */
        .alert-box {
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
            position: relative;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #f0fff4 0%, #e6fffa 100%);
            border: 2px solid #68d391;
            color: #22543d;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fffbeb 0%, #fef5e7 100%);
            border: 2px solid #f6ad55;
            color: #744210;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #ebf8ff 0%, #e6fffa 100%);
            border: 2px solid #63b3ed;
            color: #2a4365;
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
        
        /* Information cards */
        .info-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
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
            display: flex;
            align-items: center;
        }
        
        .info-card-title::before {
            content: attr(data-icon);
            margin-right: 10px;
            font-size: 20px;
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
        
        /* Buttons */
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
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6BB544 0%, #5BB7E8 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(107, 181, 68, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: #5BB7E8;
            border: 2px solid #5BB7E8;
        }
        
        /* Contact information */
        .contact-card {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 1px solid #7dd3fc;
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
        
        .contact-item::before {
            content: attr(data-icon);
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
        
        /* Footer */
        .email-footer {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #cbd5e1;
            text-align: center;
            padding: 30px;
        }
        
        .footer-content {
            margin-bottom: 20px;
        }
        
        .footer-links {
            margin: 15px 0;
        }
        
        .footer-links a {
            color: #7dd3fc;
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
        
        /* Responsive design */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 10px;
            }
            
            .email-header,
            .email-content {
                padding: 25px 20px;
            }
            
            .email-title {
                font-size: 24px;
            }
            
            .company-logo {
                height: 60px;
            }
            
            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .info-value {
                max-width: 100%;
                text-align: left;
            }
            
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
        
        /* Utility classes - CSS Puro */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .font-medium { font-weight: 500; }
        .text-small { font-size: 14px; }
        .text-large { font-size: 18px; }
        .text-xlarge { font-size: 20px; }
        .margin-bottom-small { margin-bottom: 16px; }
        .margin-bottom-medium { margin-bottom: 24px; }
        .margin-top-small { margin-top: 16px; }
        .margin-top-medium { margin-top: 24px; }
        
        /* Specific email content styles */
        .greeting-text {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        .content-paragraph {
            font-size: 16px;
            line-height: 1.7;
            color: #4a5568;
            margin-bottom: 18px;
        }
        
        .signature-section {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <!-- Header -->
            <div class="email-header">
                <div class="logo-container">
                    <img src="https://ecoplagasecuador.com/logoEcoPlagasNegro.png" alt="EcoPlagas Logo" class="company-logo">
                    <div class="company-tagline">@yield('tagline', 'Especialistas en Control de Plagas')</div>
                </div>
            </div>
            
            <!-- Content -->
            <div class="email-content">
                @if(isset($title))
                    <h1 class="email-title">{{ $title }}</h1>
                @endif
                
                @if(isset($subtitle))
                    <p class="email-subtitle">{{ $subtitle }}</p>
                @endif
                
                @yield('content')
            </div>
            
            <!-- Footer -->
            <div class="email-footer">
                <div class="footer-content">
                    <p>&copy; {{ date('Y') }} EcoPlagas Soluciones. Todos los derechos reservados.</p>
                    <div class="footer-links">
                        <a href="https://www.ecoplagasecuador.com">www.ecoplagasecuador.com</a> |
                        <a href="mailto:info@ecoplagasecuador.com">info@ecoplagasecuador.com</a>
                    </div>
                    <div class="footer-disclaimer">
                        @yield('disclaimer', 'Este es un mensaje automático de EcoPlagas Soluciones. Si recibió este email por error, por favor contáctenos.')
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>