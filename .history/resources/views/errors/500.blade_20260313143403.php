<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error | {{ setting('site_name', 'Video Portal') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:#111;">
    <div style="text-align:center;padding:40px 20px;">
        <div style="font-size:96px;font-weight:900;color:#222;line-height:1;margin-bottom:8px;">500</div>
        <h1 style="font-size:22px;font-weight:700;color:#e5e5e5;margin-bottom:10px;">Terjadi Kesalahan Server</h1>
        <p style="color:#555;font-size:14px;margin-bottom:28px;">Maaf, ada masalah di server kami. Coba lagi beberapa saat.</p>
        <a href="/"
           style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#ff2d55,#ff6b35);color:#fff;padding:10px 24px;border-radius:4px;font-size:14px;font-weight:600;text-decoration:none;">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:16px;height:16px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>
