<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Control de Plagas - EcoPlagas</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-size: 11px;
            line-height: 1.2;
            width: 210mm;
            height: 297mm;
        }

        .certificate-container {
            width: 100%;
            height: 100%;
            position: relative;
            background: white;
        }

        /* Barras laterales verdes y azules */
        .left-bar {
            position: absolute;
            left: 0;
            top: 0;
            width: 12mm;
            height: 100%;
            background: #6BB544;
        }

        .right-bar {
            position: absolute;
            right: 0;
            top: 0;
            width: 12mm;
            height: 100%;
            background: #5BB7E8;
        }

        /* Contenido principal */
        .main-content {
            margin: 0 15mm;
            padding: 5mm 0;
            position: relative;
            height: calc(100% - 10mm);
        }

        /* Header con información de contacto en esquina superior derecha */
        .header-info {
            position: absolute;
            top: 3mm;
            right: 0;
            text-align: right;
            font-size: 10px;
            line-height: 1.2;
            color: #333;
        }

        .ruc-green {
            font-weight: bold;
            color: #6BB544;
            font-size: 11px;
            margin-bottom: 1mm;
        }

        .website {
            color: #5BB7E8;
            font-weight: bold;
            margin-top: 1mm;
        }

        /* Sección del logo y QR */
        .logo-section {
            margin: 12mm 0 8mm 0;
            width: 100%;
            position: relative;
        }

        .logo-container {
            position: absolute;
            left: 0;
            top: -5mm;
        }

        .logo-main {
            font-size: 40px;
            font-weight: bold;
            line-height: 0.9;
        }

        .eco-text {
            color: #6BB544;
        }

        .plagas-text {
            color: #5BB7E8;
        }

        .soluciones-text {
            font-size: 11px;
            color: #333;
            letter-spacing: 8px;
            margin-top: 2mm;
            font-weight: bold;
        }

        .agency-info {
            font-size: 8px;
            color: #333;
            margin-top: 3mm;
            line-height: 1.2;
            font-weight: bold;
            text-align: start;
        }

        /* QR Code */
        .qr-container {
            position: absolute;
            right: 0;
            top: 2mm;
            text-align: center;
        }

        .qr-code {
            width: 20mm;
            height: 20mm;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
            background: #fff;
        }

        .qr-number {
            font-size: 9px;
            text-align: center;
            margin-top: 2mm;
            font-weight: bold;
        }

        /* Título principal */
        .main-title {
            font-size: 22px;
            font-weight: bold;
            color: #5BB7E8;
            text-align: center;
            margin: 60mm 0 4mm 0;
            letter-spacing: 1px;
        }

        /* Subtítulo de certificación */
        .certification-text {
            text-align: center;
            font-size: 12px;
            margin-bottom: 4mm;
            font-weight: normal;
            line-height: 1.3;
            color: #333;
        }

        /* Línea separadora */
        .separator-line {
            width: 100%;
            height: 2px;
            background: #000;
            margin: 2mm 0 4mm 0;
        }

        /* Información del cliente */
        .client-section {
            margin: 4mm 0;
            font-size: 11px;
        }

        .client-row {
            width: 100%;
            margin-bottom: 2mm;
            position: relative;
        }

        .client-left {
            display: inline-block;
            width: 70%;
        }

        .client-right {
            position: absolute;
            right: 0;
            top: 0;
            text-align: right;
            font-weight: bold;
            white-space: nowrap;
        }

        .field-label {
            font-weight: bold;
            display: inline-block;
            width: 25mm;
            margin-right: 3mm;
        }

        .field-value {
            display: inline-block;
            width: calc(100% - 28mm);
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            font-weight: bold;
            min-height: 3mm;
        }

        /* Tabla de servicios */
        .services-header {
            font-size: 11px;
            font-weight: bold;
            margin: 4mm 0 1mm 0;
            width: 100%;
        }

        .header-item {
            display: inline-block;
            font-weight: bold;
        }

        .header-servicio {
            width: 22%;
        }

        .header-producto {
            width: 32%;
        }

        .header-categoria {
            width: 28%;
        }

        .header-registro {
            width: 18%;
        }

        .services-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1mm 0 4mm 0;
            font-size: 10px;
        }

        .services-table td {
            border: 1px solid #000;
            padding: 1.5mm;
            vertical-align: middle;
        }

        .service-type {
            width: 22%;
            text-align: left;
        }

        .product-col {
            width: 32%;
            text-align: left;
        }

        .category-col {
            width: 28%;
            text-align: left;
        }

        .registry-col {
            width: 18%;
            text-align: left;
        }

        .checkbox {
            display: inline-block;
            width: 3.5mm;
            height: 3.5mm;
            border: 1px solid #000;
            margin-right: 2mm;
            text-align: center;
            line-height: 3mm;
            font-weight: bold;
            font-size: 8px;
            vertical-align: middle;
        }

        .checkbox.checked {
            background: #000;
            color: white;
        }

        .empty-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            width: 100%;
            min-height: 2mm;
        }

        /* Firmas */
        .signatures-section {
            margin: 6mm 0 4mm 0;
            width: 100%;
            position: relative;
        }

        .signature-block {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }

        .signature-left {
            position: absolute;
            left: 0;
            top: 0;
        }

        .signature-right {
            position: absolute;
            right: 0;
            top: 0;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            height: 8mm;
            margin-bottom: 1mm;
            position: relative;
        }

        .signature-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 1mm;
        }

        .signature-name {
            font-weight: bold;
            font-size: 9px;
            display: block;
            margin-bottom: 1mm;
        }

        .signature-details {
            font-size: 8px;
            line-height: 1.2;
        }

        .signature-image {
            position: absolute;
            left: 25mm;
            height: 10mm;
            width: auto;
        }

        /* Número de certificado */
        .certificate-number {
            position: absolute;
            bottom: 40mm;
            right: 0;
            font-size: 22px;
            font-weight: bold;
            color: #E53935;
        }

        /* Footer con logos */
        .footer-section {
            position: absolute;
            bottom: 8mm;
            left: 0;
            right: 0;
        }

        .avales-title {
            font-size: 10px;
            margin-bottom: 2mm;
            font-weight: bold;
        }

        .logos-container {
            margin: 10mm 0 0 0;
            width: 100%;
            text-align: center;
            display: flex;
            gap: 5mm;
        }

        .logo-item {
            display: inline-block;
            text-align: center;
            font-size: 8px;
            line-height: 1.1;
            width: 18%;
            vertical-align: top;
            margin: 0 4%;
        }

        .logo-box {
            width: 15mm;
            height: 10mm;
            margin: 0 auto 7mm 10mm;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: bold;
        }

        .permit-number {
            font-weight: bold;
            margin-top: 1mm;
            font-size: 7px;
        }

        /* Watermark de fondo */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(0, 0, 0, 0.04);
            font-weight: bold;
            z-index: 0;
            letter-spacing: 10px;
        }

        .content-overlay {
            position: relative;
            z-index: 1;
        }

        .bold {
            font-weight: bold;
        }

        /* Mejoras para impresión */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            .certificate-container {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
    <div class="certificate-container">
        <!-- Barras laterales de color -->
        <div class="left-bar"></div>
        <div class="right-bar"></div>

        <!-- Watermark de fondo -->
        <div class="watermark">ECOPLAGAS SOLUCIONES</div>

        <div class="main-content content-overlay">
            <!-- Información corporativa superior derecha -->
            <div class="header-info">
                <div class="ruc-green">RUC N.º 1719710574001</div>
                <div>Diego García S8-61 y Av. Alpahuasi, Quito, Ecuador</div>
                <div>Teléfono: +593 99 503 1066 / +593 99 107 9118</div>
                <div>Correo: info@ecoplagasecuador.com</div>
                <div class="website">www.ecoplagasecuador.com</div>
            </div>

            <!-- Logo y QR Code -->
            <div class="logo-section">
                <div class="logo-container">
                    <img src="{{ resource_path('views/images/logo2.png') }}" alt="Logo" class="logo-box" style="height: 20mm; width: auto;">

                    <div class="logo-main">
                        <span class="eco-text">ECO</span><span class="plagas-text">PLAGAS</span>
                    </div>
                    <div class="soluciones-text">S O L U C I O N E S</div>
                    <div class="agency-info">
                        AGENCIA DE REGULACIÓN, CONTROL<br>
                        Y VIGILANCIA SANITARIA<br>
                        ARSA - 2023-22.0-0000331
                    </div>
                </div>

                <div class="qr-container">
                    <div class="qr-code">
                        <img src="{{ resource_path('views/images/qr1.svg') }}" alt="QR Code" style="width: 20mm; height: 20mm;">
                    </div>
                    <div class="qr-number">Cert. #{{ $certificate->certificate_number }}</div>
                </div>
            </div>

            <!-- Título principal -->
            <div class="main-title">CERTIFICADO DE CONTROL DE PLAGAS URBANAS</div>

            <!-- Texto de certificación -->
            <div class="certification-text">
                ECOPLAGAS Certifica que se ha efectuado los servicios de {{ strtoupper($certificate->service_description ?? 'EXTERMINIO DE INSECTOS RASTREROS') }}
            </div>

            <!-- Línea separadora -->
            <div class="separator-line"></div>

            <!-- Información del cliente -->
            <div class="client-section">
                <div class="client-row">
                    <div class="client-left">
                        <span class="field-label">Dirigido a:</span>
                        <span class="field-value">{{ strtoupper($certificate->client_name ?? $certificate->service->user->name) }}</span>
                    </div>
                    <div class="client-right">
                        RUC: {{ $certificate->client_ruc ?? '' }}
                    </div>
                </div>

                <div class="client-row">
                    <div class="client-left">
                        <span class="field-label">Dirección:</span>
                        <span class="field-value">{{ strtoupper($certificate->address ?? $certificate->service->address ?? $certificate->service->user->address ?? '') }}</span>
                    </div>
                    <div class="client-right">
                        Ciudad: {{ strtoupper($certificate->city ?? 'QUITO') }}
                    </div>
                </div>

                <div class="client-row">
                    <div class="client-left">
                        <span class="field-label">Área Tratada:</span>
                        <span class="field-value">{{ strtoupper($certificate->treated_area ?? '') }}</span>
                    </div>
                    <div class="client-right">
                        Teléfono: {{ $certificate->phone ?? $certificate->service->user->phone ?? '' }}
                    </div>
                </div>

                <div class="client-row">
                    <div class="client-left" style="width: 48%;">
                        <span class="field-label">Fecha de Expedición:</span>
                        <span class="field-value">{{ strtoupper(\Carbon\Carbon::parse($certificate->issue_date)->locale('es')->isoFormat('DD [DE] MMMM [DE] YYYY')) }}</span>
                    </div>
                    <div class="client-left" style="width: 48%;">
                        <span class="field-label">Válido Hasta:</span>
                        <span class="field-value">{{ strtoupper(\Carbon\Carbon::parse($certificate->valid_until)->locale('es')->isoFormat('DD/MMMM/YYYY')) }}</span>
                    </div>
                </div>
            </div>

            <!-- Header de tabla de servicios -->
            <div class="services-header">
                <span class="header-item header-servicio">Servicio Realizado:</span>
                <span class="header-item header-producto">Producto Utilizado:</span>
                <span class="header-item header-categoria">Categoría Toxicológica:</span>
                <span class="header-item header-registro">Reg. AGROCALIDAD</span>
            </div>

            <!-- Tabla de servicios -->
            <table class="services-table">
                <tr>
                    <td class="service-type">
                        <span class="checkbox {{ $certificate->desinsectacion ? 'checked' : '' }}">{{ $certificate->desinsectacion ? 'X' : '' }}</span>
                        Desinsectación
                    </td>
                    <td class="product-col">{{ $certificate->producto_desinsectacion ?: '' }}<span class="empty-line"></span></td>
                    <td class="category-col">{{ $certificate->categoria_desinsectacion ?: '' }}<span class="empty-line"></span></td>
                    <td class="registry-col">{{ $certificate->registro_desinsectacion ?: '' }}<span class="empty-line"></span></td>
                </tr>
                <tr>
                    <td class="service-type">
                        <span class="checkbox {{ $certificate->desinfeccion ? 'checked' : '' }}">{{ $certificate->desinfeccion ? 'X' : '' }}</span>
                        Desinfección
                    </td>
                    <td class="product-col">{{ $certificate->producto_desinfeccion ?: '' }}<span class="empty-line"></span></td>
                    <td class="category-col">{{ $certificate->categoria_desinfeccion ?: '' }}<span class="empty-line"></span></td>
                    <td class="registry-col">{{ $certificate->registro_desinfeccion ?: '' }}<span class="empty-line"></span></td>
                </tr>
                <tr>
                    <td class="service-type">
                        <span class="checkbox {{ $certificate->desratizacion ? 'checked' : '' }}">{{ $certificate->desratizacion ? 'X' : '' }}</span>
                        Desratización
                    </td>
                    <td class="product-col">{{ $certificate->producto_desratizacion ?: '' }}<span class="empty-line"></span></td>
                    <td class="category-col">{{ $certificate->categoria_desratizacion ?: '' }}<span class="empty-line"></span></td>
                    <td class="registry-col">{{ $certificate->registro_desratizacion ?: '' }}<span class="empty-line"></span></td>
                </tr>
                <tr>
                    <td class="service-type">
                        <span class="checkbox {{ $certificate->otro_servicio ? 'checked' : '' }}">{{ $certificate->otro_servicio ? 'X' : '' }}</span>
                        Otro
                    </td>
                    <td class="product-col">{{ $certificate->producto_otro ?: '' }}<span class="empty-line"></span></td>
                    <td class="category-col">{{ $certificate->categoria_otro ?: '' }}<span class="empty-line"></span></td>
                    <td class="registry-col">{{ $certificate->registro_otro ?: '' }}<span class="empty-line"></span></td>
                </tr>
            </table>

            <!-- Firmas -->
            <div class="signatures-section">
                <div class="signature-block signature-left">
                    <img src="{{ resource_path('views/images/firmatecnico.png') }}" alt="Firma" class="signature-image">
                    <div class="signature-line"></div>
                    <div class="signature-title">Técnico Especialista</div>
                    <div class="signature-details">
                        <span class="signature-name">ARNALDO ERAZO</span><br>
                        ANALISTA EN CONTROL E ING. AGROPECUARIO<br>
                        MAESTRÍA PROFESIONAL<br>
                        1079-2022-24 10021
                    </div>
                </div>

                <div class="signature-block signature-right">
                    <div class="signature-line"></div>
                    <div class="signature-title">Gerente General</div>
                    <div class="signature-details">
                    </div>
                </div>
            </div>

            <!-- Número de certificado -->
            <div class="certificate-number">N° {{ str_replace('CERT-', '', $certificate->certificate_number) }}</div>

            <!-- Footer con logos y certificaciones -->
            <div class="footer-section">
                <div class="avales-title">Avales y garantías Nacionales e Internacionales:</div>

                <div class="logos-container">
                    <img src="{{ resource_path('views/images/Imagen3.png') }}" alt="EPA Logo" class="logo-box" style="height: 12mm; width: auto;">
                    <img src="{{ resource_path('views/images/arcsa.png') }}" alt="Arcsa Logo" class="logo-box" style="height: 12mm; width: auto;">
                    <img src="{{ resource_path('views/images/ambiente.png') }}" alt="Ambiente Logo" class="logo-box" style="height: 12mm; width: auto;">
                    <img src="{{ resource_path('views/images/qambiente.png') }}" alt="QAmbiente Logo" class="logo-box" style="height: 12mm; width: auto;">
                    <img src="{{ resource_path('views/images/qr2.png') }}" alt="QR2" class="logo-box" style="height: 15mm; width: auto;">
                </div>
            </div>
        </div>
    </div>
</body>

</html>