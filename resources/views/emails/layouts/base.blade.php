<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Notifikasi NOTAGO')</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f1f5f9; color: #1e293b; }
        .wrapper { max-width: 600px; margin: 32px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); padding: 32px 40px; }
        .header-logo { font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; }
        .header-logo span { color: #93c5fd; }
        .header-subtitle { color: #bfdbfe; font-size: 13px; margin-top: 4px; }
        .body { padding: 36px 40px; }
        .greeting { font-size: 18px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .text { font-size: 14px; color: #475569; line-height: 1.7; margin-bottom: 16px; }
        .info-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px 24px; margin: 20px 0; }
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #64748b; font-weight: 500; }
        .info-value { color: #1e293b; font-weight: 600; text-align: right; max-width: 60%; }
        .btn { display: inline-block; padding: 12px 28px; border-radius: 8px; font-size: 14px; font-weight: 600; text-decoration: none; margin-top: 8px; }
        .btn-primary { background: #2563eb; color: #ffffff; }
        .btn-success { background: #16a34a; color: #ffffff; }
        .btn-danger { background: #dc2626; color: #ffffff; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .divider { height: 1px; background: #e2e8f0; margin: 24px 0; }
        .footer { background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px 40px; text-align: center; }
        .footer-text { font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .alert-box { border-radius: 8px; padding: 16px 20px; margin: 16px 0; font-size: 13px; }
        .alert-warning { background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; }
        .alert-danger { background: #fff1f2; border: 1px solid #fca5a5; color: #991b1b; }
        .alert-success { background: #f0fdf4; border: 1px solid #86efac; color: #166534; }
        .progress-bar-wrap { background: #e2e8f0; border-radius: 4px; height: 8px; margin-top: 6px; }
        .progress-bar-fill { height: 8px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #f1f5f9; color: #475569; font-weight: 600; padding: 10px 12px; text-align: left; }
        td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; color: #1e293b; }
        tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-logo">NOTA<span>GO</span></div>
        <div class="header-subtitle">Sistem RAB & Material Management</div>
    </div>
    <div class="body">
        @yield('content')
    </div>
    <div class="footer">
        <div class="footer-text">
            Email ini dikirim otomatis oleh sistem NOTAGO.<br>
            Harap tidak membalas email ini langsung.<br>
            &copy; {{ date('Y') }} NOTAGO — Sistem RAB Material
        </div>
    </div>
</div>
</body>
</html>
