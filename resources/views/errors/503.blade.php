<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduled Maintenance - GovJobs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #f8fafc;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --accent-color: #2563eb;
            --border-color: rgba(226, 232, 240, 0.8);
            --glass-bg: rgba(255, 255, 255, 0.45);
            --glass-border: rgba(255, 255, 255, 0.25);
            --shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.05);
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-primary: #090d16;
                --text-primary: #f3f4f6;
                --text-secondary: #9ca3af;
                --accent-color: #3b82f6;
                --border-color: rgba(37, 99, 235, 0.15);
                --glass-bg: rgba(17, 24, 39, 0.65);
                --glass-border: rgba(255, 255, 255, 0.08);
                --shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            }
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            line-height: 1.6;
        }
        .container {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2rem;
            max-width: 550px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow);
            animation: fadeIn 0.8s ease-out;
        }
        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 2rem auto;
            border: 1px solid rgba(37, 99, 235, 0.2);
            animation: pulse 2s infinite;
        }
        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1rem;
            letter-spacing: -0.02em;
        }
        p {
            color: var(--text-secondary);
            font-size: 1.05rem;
            margin-bottom: 2rem;
        }
        .divider {
            height: 1px;
            background: var(--border-color);
            margin: 2rem 0;
        }
        .footer-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
            opacity: 0.8;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(37, 99, 235, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrapper">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h1>Under Maintenance</h1>
        <p>{{ $message }}</p>
        <div class="divider"></div>
        <div class="footer-text">
            Portal Administration Console &bull; GovJobs
        </div>
    </div>
</body>
</html>
