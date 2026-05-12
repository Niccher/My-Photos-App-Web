<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<?php if (isset($title)): ?>
    <div class="mb-4">
        <h2 class="h4 mb-0"><?= esc($title) ?></h2>
        <?php if (isset($subtitle)): ?>
            <p class="text-muted small mb-0"><?= esc($subtitle) ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (!empty($searchQuery) && empty($photos)): ?>
    <div class="text-center py-5">
        <i class="bi bi-search" style="font-size: 4rem; color: #dee2e6;"></i>
        <h3 class="mt-3 text-muted">No results found</h3>
        <p class="text-muted">We couldn't find any photos matching "<?= esc($searchQuery) ?>".</p>
        <a href="<?= base_url() ?>" class="btn btn-link text-decoration-none">Clear search</a>
    </div>
<?php elseif (empty($photos)): ?>
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
        <div class="d-flex align-items-center gap-3 mb-3 mt-5 px-2">
            <h5 class="mb-0 fw-bold text-white opacity-75 timeline-header"><?= $currentDate ?></h5>
            <div class="flex-grow-1 border-bottom border-secondary opacity-25"></div>
        </div>
        <div class="photo-grid">
<?php endif; ?>
        
        <div class="photo-item" 
             draggable="true"
             data-id="<?= $photo['id'] ?>" 
             data-full="<?= base_url($photo['path']) ?>"
             data-filename="<?= $photo['filename'] ?>"
             data-size="<?= round($photo['size'] / 1024 / 1024, 2) ?> MB"
             data-dimensions="<?= $photo['width'] ? $photo['width'].' x '.$photo['height'] : 'Video' ?>"
             data-date="<?= date('M d, Y H:i', strtotime($photo['taken_at'])) ?>"
             data-favorite="<?= $photo['is_favorite'] ? '1' : '0' ?>"
             data-exif='<?= $photo['exif_data'] ? esc($photo['exif_data'], 'attr') : '' ?>'
             data-location="<?= ($photo['latitude'] && $photo['longitude']) ? $photo['latitude'].','.$photo['longitude'] : '' ?>"
             data-type="<?= strpos($photo['mime_type'], 'video/') === 0 ? 'video' : 'image' ?>">
            <div class="selection-overlay d-none position-absolute top-0 start-0 w-100 h-100 flex-row align-items-start justify-content-end p-2" style="z-index: 10; background: rgba(0,0,0,0.1);">
                <div class="selection-check d-flex align-items-center justify-content-center bg-white rounded-circle shadow-sm" style="width: 24px; height: 24px; cursor: pointer; border: 2px solid #1a73e8; color: #1a73e8;">
                    <i class="bi bi-check-lg d-none"></i>
                </div>
            </div>
            <?php if ($photo['is_favorite']): ?>
                <div class="position-absolute top-0 start-0 p-2" style="z-index: 5;">
                    <i class="bi bi-heart-fill text-danger shadow-sm"></i>
                </div>
            <?php endif; ?>
            <?php if (strpos($photo['mime_type'], 'video/') === 0): ?>
                <video src="<?= base_url($photo['path']) ?>" class="w-100 h-100 object-fit-cover" muted loop preload="metadata" onmouseover="this.play()" onmouseout="this.pause()"></video>
                <div class="position-absolute bottom-0 end-0 p-1 m-1 bg-dark bg-opacity-75 text-white rounded small" style="pointer-events: none;"><i class="bi bi-play-btn me-1"></i>Video</div>
            <?php else: ?>
                <img src="<?= base_url($photo['thumbnail_path']) ?>" alt="<?= $photo['filename'] ?>" loading="lazy">
            <?php endif; ?>
        </div>
        
    <?php endforeach; ?>
    </div> <!-- Close last grid -->

    <!-- Infinite Scroll Sentinel -->
    <div id="infiniteScrollSentinel" class="text-center py-4" style="min-height: 100px;">
        <div class="spinner-border text-primary d-none" role="status">
            <span class="visually-hidden">Loading more...</span>
        </div>
    </div>
    
    <!-- Hidden Pagination for SEO/Fallback -->
    <div class="d-none">
        <?= $pager->links() ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
