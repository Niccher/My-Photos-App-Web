<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Photo — Photos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #000;
            color: #fff;
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .viewer-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .photo-wrapper {
            max-width: 100%;
            max-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        img, video {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            box-shadow: 0 0 50px rgba(0,0,0,0.8);
            border-radius: 8px;
        }
        .overlay-header {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            padding: 1.5rem 2rem;
            background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent);
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .brand {
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .brand span { color: #4285f4; }
        .footer-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 1.5rem 2rem;
            background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
            z-index: 10;
            text-align: center;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
        }
    </style>
</head>
<body>

<div class="viewer-container">
    <div class="overlay-header">
        <a href="#" class="brand"><span>Photos</span> Shared</a>
    </div>

    <div class="photo-wrapper">
        <?php if (strpos($photo['mime_type'], 'video/') === 0): ?>
            <video src="<?= base_url($photo['path']) ?>" controls autoplay></video>
        <?php else: ?>
            <img src="<?= base_url($photo['path']) ?>" alt="Shared Photo">
        <?php endif; ?>
    </div>

    <div class="footer-info">
        Shared via Photos App &bull; <?= date('F j, Y', strtotime($photo['taken_at'])) ?>
    </div>
</div>

</body>
</html>
