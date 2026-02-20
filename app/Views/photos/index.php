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
             data-dimensions="<?= $photo['width'] ?> x <?= $photo['height'] ?>"
             data-date="<?= date('M d, Y H:i', strtotime($photo['taken_at'])) ?>">
            <img src="<?= base_url($photo['thumbnail_path']) ?>" alt="<?= $photo['filename'] ?>" loading="lazy">
        </div>
        
    <?php endforeach; ?>
    </div> <!-- Close last grid -->
<?php endif; ?>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-black border-0 flex-row">
            <div class="modal-header border-0 p-3 position-absolute top-0 start-0 w-100 d-flex justify-content-between" style="z-index: 1056; background: linear-gradient(to bottom, rgba(0,0,0,0.5), transparent);">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                <button type="button" class="btn btn-link text-white p-0" id="btnInfo">
                    <i class="bi bi-info-circle fs-4"></i>
                </button>
            </div>
            <div class="modal-body p-0 d-flex align-items-center justify-content-center flex-grow-1 overflow-hidden">
                <img id="lightboxImage" src="" class="img-fluid" style="max-height: 100vh;">
            </div>
            
            <!-- Metadata Panel -->
            <div id="metadataPanel" class="bg-white p-4 h-100 d-none overflow-auto" style="width: 360px; z-index: 1057;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Details</h5>
                    <button type="button" class="btn-close" id="btnCloseMetadata"></button>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Filename</label>
                    <span id="metaFilename" class="text-break"></span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Created</label>
                    <span id="metaDate"></span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Size</label>
                    <span id="metaSize"></span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Dimensions</label>
                    <span id="metaDimensions"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
