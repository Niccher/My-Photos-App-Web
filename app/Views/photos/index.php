<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php if (empty($photos)): ?>
    <div class="text-center py-5">
        <i class="bi bi-image" style="font-size: 4rem; color: #dee2e6;"></i>
        <h3 class="mt-3 text-muted">No photos yet</h3>
        <p class="text-muted">Upload some photos or scan the uploads folder to get started.</p>
    </div>
<?php else: ?>
    <?php 
    $currentDate = ''; 
    foreach ($photos as $photo): 
        $photoDate = date('F Y', strtotime($photo['taken_at']));
        if ($photoDate !== $currentDate):
            if ($currentDate !== '') echo '</div>'; // Close previous grid
            $currentDate = $photoDate;
    ?>
        <h5 class="mb-3 mt-4 text-muted px-2"><?= $currentDate ?></h5>
        <div class="photo-grid">
    <?php endif; ?>
        
        <div class="photo-item" 
             data-id="<?= $photo['id'] ?>" 
             data-full="<?= base_url($photo['path']) ?>"
             data-filename="<?= $photo['filename'] ?>"
             data-size="<?= round($photo['size'] / 1024 / 1024, 2) ?> MB"
             data-dimensions="<?= $photo['width'] ? $photo['width'].' x '.$photo['height'] : 'Video' ?>"
             data-date="<?= date('M d, Y H:i', strtotime($photo['taken_at'])) ?>"
             data-type="<?= strpos($photo['mime_type'], 'video/') === 0 ? 'video' : 'image' ?>">
            <?php if (strpos($photo['mime_type'], 'video/') === 0): ?>
                <video src="<?= base_url($photo['path']) ?>" class="w-100 h-100 object-fit-cover" muted loop preload="metadata" onmouseover="this.play()" onmouseout="this.pause()"></video>
                <div class="position-absolute bottom-0 end-0 p-1 m-1 bg-dark bg-opacity-75 text-white rounded small" style="pointer-events: none;"><i class="bi bi-play-btn me-1"></i>Video</div>
            <?php else: ?>
                <img src="<?= base_url($photo['thumbnail_path']) ?>" alt="<?= $photo['filename'] ?>" loading="lazy">
            <?php endif; ?>
        </div>
        
    <?php endforeach; ?>
    </div> <!-- Close last grid -->
<?php endif; ?>

<?= $this->endSection() ?>
