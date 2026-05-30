<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Portal — Vision Mission API Documentation</title>
    
    <!-- Google Fonts Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Swagger UI Official Assets via CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.11.0/swagger-ui.css">
    
    <style>
        /* Base Premium Styling Reset */
        body {
            margin: 0;
            background-color: #0b0f19;
            color: #f3f4f6;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        /* ─── Premium Header ────────────────────────────────────────────────── */
        .portal-header {
            background: linear-gradient(135deg, #111827 0%, #070b13 100%);
            border-bottom: 1px solid #1f2937;
            padding: 1.5rem 3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
        }

        .portal-title-area {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .portal-logo {
            background: linear-gradient(135deg, #2563eb 0%, #06b6d4 100%);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.25rem;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(37, 99, 235, 0.4);
        }

        .portal-title-text h1 {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #ffffff, #9ca3af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .portal-title-text p {
            margin: 0.15rem 0 0 0;
            font-size: 0.75rem;
            color: #06b6d4;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .header-links a {
            color: #9ca3af;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            background: rgba(31, 41, 55, 0.5);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid #374151;
        }

        .header-links a:hover {
            color: #ffffff;
            border-color: #06b6d4;
            background: rgba(6, 182, 212, 0.1);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.2);
        }

        /* ─── Custom Swagger Dark Theme Overrides ───────────────────────────── */
        #swagger-ui {
            padding: 2rem 3rem;
            background-color: #0b0f19;
        }

        /* Info Container */
        .swagger-ui .info {
            margin: 2rem 0;
            background: linear-gradient(135deg, rgba(17, 24, 39, 0.8) 0%, rgba(7, 11, 19, 0.8) 100%);
            border: 1px solid #1f2937;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(8px);
        }

        .swagger-ui .info .title {
            font-family: 'Outfit', sans-serif;
            color: #ffffff !important;
            font-size: 2.25rem !important;
            font-weight: 700;
            letter-spacing: -0.03em;
        }

        .swagger-ui .info p, 
        .swagger-ui .info li,
        .swagger-ui .info td,
        .swagger-ui .info .markdown p {
            color: #9ca3af !important;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Scheme Selectors */
        .swagger-ui .scheme-container {
            background-color: #111827 !important;
            box-shadow: none !important;
            border: 1px solid #1f2937;
            border-radius: 12px;
            padding: 1.5rem !important;
            margin: 2rem 0 !important;
        }

        .swagger-ui select,
        .swagger-ui input[type=text] {
            background-color: #1f2937 !important;
            color: #ffffff !important;
            border: 1px solid #374151 !important;
            border-radius: 8px !important;
            padding: 0.5rem 1rem !important;
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem !important;
        }

        .swagger-ui input[type=text]:focus {
            border-color: #06b6d4 !important;
            outline: none;
        }

        /* Buttons & Auth UI */
        .swagger-ui .btn.authorize {
            background-color: transparent !important;
            color: #10b981 !important;
            border-color: #10b981 !important;
            border-radius: 8px !important;
            padding: 0.5rem 1.25rem !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
        }

        .swagger-ui .btn.authorize:hover {
            background-color: rgba(16, 185, 129, 0.1) !important;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2) !important;
        }

        .swagger-ui .btn.authorize svg {
            fill: #10b981 !important;
        }

        /* Operations Blocks styling */
        .swagger-ui .opblock {
            border-radius: 12px !important;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            background-color: #111827 !important;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2) !important;
            margin-bottom: 1.25rem !important;
            overflow: hidden;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .swagger-ui .opblock:hover {
            transform: translateY(-2px);
        }

        .swagger-ui .opblock .opblock-summary {
            padding: 0.85rem 1.5rem !important;
            border-bottom: 0px !important;
        }

        .swagger-ui .opblock-tag {
            font-family: 'Outfit', sans-serif;
            color: #ffffff !important;
            font-size: 1.25rem !important;
            border-bottom: 1px solid #1f2937 !important;
            padding: 1.5rem 0.5rem 0.5rem 0.5rem !important;
        }

        .swagger-ui .opblock .opblock-summary-method {
            font-family: 'Outfit', sans-serif !important;
            font-weight: 700 !important;
            border-radius: 6px !important;
            padding: 0.35rem 0.75rem !important;
            text-shadow: none !important;
        }

        .swagger-ui .opblock .opblock-summary-path {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600 !important;
            color: #ffffff !important;
            font-size: 0.95rem !important;
        }

        .swagger-ui .opblock .opblock-summary-description {
            color: #9ca3af !important;
            font-size: 0.85rem !important;
        }

        /* Specific Methods Accents */
        /* GET */
        .swagger-ui .opblock.opblock-get {
            border-color: rgba(37, 99, 235, 0.3) !important;
            background: linear-gradient(to right, rgba(37, 99, 235, 0.03), rgba(37, 99, 235, 0.01)) !important;
        }
        .swagger-ui .opblock.opblock-get:hover {
            border-color: #2563eb !important;
        }
        .swagger-ui .opblock.opblock-get .opblock-summary-method {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        /* POST */
        .swagger-ui .opblock.opblock-post {
            border-color: rgba(16, 185, 129, 0.3) !important;
            background: linear-gradient(to right, rgba(16, 185, 129, 0.03), rgba(16, 185, 129, 0.01)) !important;
        }
        .swagger-ui .opblock.opblock-post:hover {
            border-color: #10b981 !important;
        }
        .swagger-ui .opblock.opblock-post .opblock-summary-method {
            background-color: #10b981 !important;
            color: #ffffff !important;
        }

        /* Parameters / Responses Table */
        .swagger-ui .opblock-section-header {
            background-color: #1f2937 !important;
            border-bottom: 1px solid #374151 !important;
            padding: 0.75rem 1.5rem !important;
        }

        .swagger-ui .opblock-section-header h4 {
            font-family: 'Outfit', sans-serif;
            color: #ffffff !important;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .swagger-ui table thead tr td,
        .swagger-ui table thead tr th {
            color: #ffffff !important;
            font-weight: 600 !important;
            border-bottom: 2px solid #1f2937 !important;
            padding: 0.75rem !important;
            font-size: 0.85rem !important;
        }

        .swagger-ui .parameters-col_name {
            color: #06b6d4 !important;
            font-weight: 600 !important;
        }

        .swagger-ui .parameter__name.required::after {
            color: #ef4444 !important;
        }

        .swagger-ui .parameter__type {
            color: #a78bfa !important;
            font-family: monospace;
        }

        .swagger-ui td {
            padding: 0.75rem !important;
            border-bottom: 1px solid #1f2937 !important;
        }

        /* Model & Schema Viewer styling */
        .swagger-ui .model-box {
            background-color: #0b0f19 !important;
            border: 1px solid #1f2937 !important;
            border-radius: 8px !important;
            padding: 1rem !important;
        }

        .swagger-ui .tabli {
            color: #9ca3af !important;
        }

        .swagger-ui .tabli.active {
            color: #ffffff !important;
            border-bottom-color: #06b6d4 !important;
        }

        .swagger-ui .response-col_status {
            color: #10b981 !important;
            font-weight: 700 !important;
        }

        .swagger-ui .response-col_status.error {
            color: #ef4444 !important;
        }

        .swagger-ui .microlight {
            background-color: #070b13 !important;
            border: 1px solid #1f2937 !important;
            border-radius: 8px !important;
            color: #e5e7eb !important;
            font-family: monospace !important;
            padding: 1.25rem !important;
        }

        /* Dialog / Modal authorize overrides */
        .swagger-ui .dialog-ux .modal-ux {
            background-color: #111827 !important;
            border: 1px solid #1f2937 !important;
            border-radius: 16px !important;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6) !important;
        }

        .swagger-ui .dialog-ux .modal-ux-header {
            border-bottom: 1px solid #1f2937 !important;
            padding: 1.5rem !important;
        }

        .swagger-ui .dialog-ux .modal-ux-header h3 {
            font-family: 'Outfit', sans-serif;
            color: #ffffff !important;
            font-weight: 700;
        }

        .swagger-ui .dialog-ux .modal-ux-content {
            padding: 1.5rem !important;
            color: #e5e7eb !important;
        }

        .swagger-ui .dialog-ux .modal-ux-content h4 {
            color: #ffffff !important;
        }

        /* Custom execution feedback console UI */
        .swagger-ui .execute-wrapper {
            padding: 1.5rem !important;
            background-color: #1f2937 !important;
            border-top: 1px solid #374151 !important;
        }

        .swagger-ui .btn.execute {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-color: #2563eb !important;
            border-radius: 8px !important;
            padding: 0.5rem 2rem !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4) !important;
            transition: all 0.2s ease !important;
        }

        .swagger-ui .btn.execute:hover {
            background-color: #1d4ed8 !important;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.6) !important;
        }
    </style>
</head>
<body>

    <!-- Premium Dashboard Header -->
    <header class="portal-header">
        <div class="portal-title-area">
            <div class="portal-logo">VM</div>
            <div class="portal-title-text">
                <h1>Developer Portal</h1>
                <p>API Reference & Interactive Console</p>
            </div>
        </div>
        <div class="header-links">
            <a href="/">Back to Web Portal</a>
        </div>
    </header>

    <!-- Visual Swagger Container -->
    <div id="swagger-ui"></div>

    <!-- Standalone visual scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.11.0/swagger-ui-bundle.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/swagger-ui/5.11.0/swagger-ui-standalone-preset.js"></script>
    
    <script>
        window.onload = function() {
            // Build the Swagger UI configuration
            const ui = SwaggerUIBundle({
                url: "/api/v1/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                persistAuthorization: true,
                docExpansion: "list"
            });

            window.ui = ui;
        };
    </script>
</body>
</html>
