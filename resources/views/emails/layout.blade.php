<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            padding: 32px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            font-family: 'Outfit', sans-serif;
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            color: #bfdbfe;
        }
        .content {
            padding: 32px;
            line-height: 1.6;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            padding: 12px 24px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            margin: 16px 0;
            text-align: center;
        }
        .job-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            background-color: #f8fafc;
        }
        .job-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 8px 0;
        }
        .job-meta {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 12px;
        }
        .job-badge {
            display: inline-block;
            background-color: rgba(37, 99, 235, 0.08);
            color: #2563eb;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
        }
        .tag-result {
            background-color: rgba(16, 185, 129, 0.08);
            color: #10b981;
        }
        .tag-admit {
            background-color: rgba(245, 158, 11, 0.08);
            color: #f59e0b;
        }
    </style>
</head>
<body>
    <div style="padding: 24px 0; background-color: #f8fafc;">
        <div class="container">
            <div class="header">
                <h1>Sarkari Vision Mission</h1>
                <p>Your Ultimate Government Careers Navigator</p>
            </div>
            <div class="content">
                @yield('content')
            </div>
            <div class="footer">
                <p>You are receiving this because you registered or subscribed to alerts on Sarkari Vision Mission.</p>
                <p>
                    <a href="{{ $unsubscribe_url ?? '#' }}">Unsubscribe</a> | 
                    <a href="{{ url('/') }}">Visit Website</a>
                </p>
                <p>&copy; {{ date('Y') }} Sarkari Vision Mission. All rights reserved.</p>
            </div>
        </div>
    </div>
    @if(!empty($tracking_token))
        <img src="{{ route('email.track.open', ['token' => $tracking_token]) }}" width="1" height="1" alt="" style="display:none;" />
    @endif
</body>
</html>
