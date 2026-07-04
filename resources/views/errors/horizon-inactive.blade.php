<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horizon Console Inactive - GovJobs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.6);
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --accent-color: #2563eb;
            --accent-light: rgba(37, 99, 235, 0.1);
            --border-color: rgba(226, 232, 240, 0.8);
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --glass-bg: rgba(255, 255, 255, 0.45);
            --glass-border: rgba(255, 255, 255, 0.25);
            --shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.05);
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-primary: #090d16;
                --bg-card: rgba(17, 24, 39, 0.6);
                --text-primary: #f3f4f6;
                --text-secondary: #9ca3af;
                --accent-color: #3b82f6;
                --accent-light: rgba(59, 130, 246, 0.1);
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
            padding: 3rem 2.5rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: var(--shadow);
            animation: fadeIn 0.8s ease-out;
        }
        .icon-wrapper {
            width: 80px;
            height: 80px;
            background: var(--accent-light);
            color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 1.5rem auto;
            border: 1px solid var(--border-color);
            animation: pulse 2.5s infinite;
        }
        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }
        .subtitle {
            color: var(--text-secondary);
            font-size: 1.05rem;
            margin-bottom: 2rem;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }
        .checks-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        .check-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .check-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .check-info i {
            font-size: 1.25rem;
            width: 24px;
            text-align: center;
        }
        .check-title {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-primary);
        }
        .check-desc {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        .check-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        .status-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }
        .status-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }
        .status-warning {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: var(--accent-color);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
        }
        .btn-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        .btn-secondary:hover {
            background: var(--accent-light);
            color: var(--accent-color);
        }
        .instructions {
            text-align: left;
            background: rgba(15, 23, 42, 0.02);
            border: 1px dashed var(--border-color);
            border-radius: 12px;
            padding: 1.25rem;
        }
        .instructions-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .instructions-list {
            list-style-type: none;
            padding-left: 0;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .instructions-list li {
            position: relative;
            padding-left: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .instructions-list li::before {
            content: "•";
            position: absolute;
            left: 0.25rem;
            color: var(--accent-color);
            font-weight: bold;
        }
        .code-inline {
            font-family: monospace;
            background: rgba(15, 23, 42, 0.05);
            padding: 0.1rem 0.3rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        @media (prefers-color-scheme: dark) {
            .code-inline {
                background: rgba(255, 255, 255, 0.1);
            }
            .instructions {
                background: rgba(255, 255, 255, 0.01);
            }
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
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrapper">
            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
            </svg>
        </div>
        <h1>Horizon Inactive Locally</h1>
        <p class="subtitle">Laravel Horizon is configured to monitor Redis queues, but your local development environment is running on the Database driver.</p>
        
        <div class="checks-grid">
            <!-- Queue Connection Check -->
            <div class="check-card">
                <div class="check-info">
                    <i class="fas fa-network-wired" style="color: var(--accent-color);"></i>
                    <div>
                        <div class="check-title">Queue Connection Driver</div>
                        <div class="check-desc">Current config: <span class="code-inline">{{ $queueDriver }}</span></div>
                    </div>
                </div>
                @if($queueDriver === 'redis')
                    <div class="check-status status-success"><i class="fas fa-check-circle"></i> Redis</div>
                @else
                    <div class="check-status status-warning"><i class="fas fa-exclamation-circle"></i> {{ ucfirst($queueDriver) }}</div>
                @endif
            </div>

            <!-- PHP Redis Check -->
            <div class="check-card">
                <div class="check-info">
                    <i class="fab fa-php" style="color: #777bb4;"></i>
                    <div>
                        <div class="check-title">PHP Redis Extension</div>
                        <div class="check-desc">Needed for low-level connection</div>
                    </div>
                </div>
                @if($hasRedisExtension)
                    <div class="check-status status-success"><i class="fas fa-check-circle"></i> Loaded</div>
                @else
                    <div class="check-status status-danger"><i class="fas fa-times-circle"></i> Missing</div>
                @endif
            </div>

            <!-- Redis Server Check -->
            <div class="check-card">
                <div class="check-info">
                    <i class="fas fa-server" style="color: #d82c20;"></i>
                    <div>
                        <div class="check-title">Redis Server Status</div>
                        <div class="check-desc">Listening on port 6379</div>
                    </div>
                </div>
                @if($redisWorking)
                    <div class="check-status status-success"><i class="fas fa-check-circle"></i> Online</div>
                @else
                    <div class="check-status status-danger"><i class="fas fa-times-circle"></i> Offline</div>
                @endif
            </div>
        </div>

        <div class="btn-container">
            <a href="/admin/dashboard" class="btn btn-primary">
                <i class="fas fa-chart-line"></i> Open Local Queue Control Center
            </a>
        </div>

        <div class="instructions">
            <div class="instructions-title">
                <i class="fas fa-info-circle" style="color: var(--accent-color);"></i>
                How to Enable Horizon Locally
            </div>
            <ul class="instructions-list">
                <li>Make sure a **Redis server** is installed and running on port <span class="code-inline">6379</span>.</li>
                <li>Switch the queue connection by updating <span class="code-inline">.env</span>:
                    <br><span class="code-inline">QUEUE_CONNECTION=redis</span>
                </li>
                <li>Since the PHP extension is missing, install the PHP client via Composer:
                    <br><span class="code-inline">composer require predis/predis</span>
                </li>
                <li>Update your <span class="code-inline">.env</span> file client setting:
                    <br><span class="code-inline">REDIS_CLIENT=predis</span>
                </li>
                <li>Run Horizon in your terminal:
                    <br><span class="code-inline">php artisan horizon</span>
                </li>
            </ul>
        </div>

        <div class="divider"></div>
        <div class="footer-text">
            Portal Administration Console &bull; GovJobs
        </div>
    </div>
</body>
</html>
