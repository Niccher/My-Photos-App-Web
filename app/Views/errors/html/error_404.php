<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>404 Page Not Found — Photos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            color: #fff;
            margin: 0;
        }
        .error-card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 24px;
            padding: 3rem 2rem;
            width: 100%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .error-icon {
            font-size: 5rem;
            background: linear-gradient(135deg, #4285f4, #00c6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        .error-code {
            font-size: 1.25rem;
            font-weight: 700;
            color: #4285f4;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
        }
        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .error-desc {
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn-home {
            background: linear-gradient(135deg, #4285f4, #00c6ff);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 600;
            padding: 0.8rem 2rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(66,133,244,0.4);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div class="error-code">Error 404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-desc">
            We searched every corner of your library, but the page you're looking for seems to have vanished or never existed.
        </p>
        <a href="<?= base_url() ?>" class="btn-home">
            <i class="bi bi-house-door-fill"></i> Back to Gallery
        </a>
    </div>
</body>
</html>
