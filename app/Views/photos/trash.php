<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="mb-4">
    <h2 class="h4 mb-0">Trash</h2>
    <p class="text-muted small mb-0">Deleted photos (items are permanently removed after 60 days)</p>
</div>

<?php if (empty($photos)): ?>
    <div class="text-center py-5">
        <i class="bi bi-trash" style="font-size: 4rem; color: #dee2e6;"></i>
        <h3 class="mt-3 text-muted">Trash is empty</h3>
        <p class="text-muted">Deleted items will stay here for a while before being permanently removed.</p>
    </div>
<?php else: ?>
    <?php 
    $currentDate = ''; 
    foreach ($photos as $photo): 
        $photoDate = date('F Y', strtotime($photo['deleted_at'] ?? $photo['taken_at']));
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
             data-dimensions="<?= $photo['width'] ?> x <?= $photo['height'] ?>"
             data-date="<?= date('M d, Y H:i', strtotime($photo['taken_at'])) ?>">
            <img src="<?= base_url($photo['thumbnail_path']) ?>" alt="<?= $photo['filename'] ?>" loading="lazy" style="opacity: 0.7; filter: grayscale(50%);">
        </div>
        
    <?php endforeach; ?>
    </div> <!-- Close last grid -->
<?php endif; ?>

<?= $this->endSection() ?>
