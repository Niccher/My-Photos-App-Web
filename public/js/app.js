$(document).ready(function () {
    const $loading = $('#loadingOverlay');

    // Sidebar Toggle
    $('#sidebarToggle').on('click', function () {
        $('#sidebarMenu').toggleClass('active');
    });

    // Lightbox Logic
    const $lightboxModal = new bootstrap.Modal('#lightboxModal');
    const $lightboxImageContainer = $('#lightboxImageContainer');
    let currentPhotoId = null;
    let isSelectMode = false;
    let selectedIds = new Set();

    // Corrected $loading variable initialization
    // const $loading = $('.loading-overlay'); // This line was moved from the top, but the original $loading was for #loadingOverlay. Reverting to original.
    let currentIndex = -1;
    let $allPhotos = $('.photo-item'); // This was moved from inside openPhoto, ensuring it's available globally.

    function openPhoto(index) {
        if (index < 0 || index >= $allPhotos.length) return;

        currentIndex = index;
        const $this = $allPhotos.eq(index);
        const fullUrl = $this.data('full');
        const dataType = $this.data('type') || 'image';
        currentPhotoId = $this.data('id');
        const context = window.location.pathname.split('/').pop() || 'index';

        console.log('Opening photo index:', currentIndex, 'ID:', currentPhotoId);

        if (context === 'trash') {
            $('#btnRestore').show();
            $('#btnArchive').hide();
            $('#btnDelete').attr('title', 'Delete Permanently');
        } else {
            $('#btnRestore').hide();
            $('#btnArchive').show();
            $('#btnArchive i').attr('class', context === 'archive' ? 'bi bi-archive-fill fs-5' : 'bi bi-archive fs-5');
        }

        $('#metaFilename').text($this.data('filename') || 'Unknown');
        $('#metaDate').text($this.data('date') || '-');
        $('#metaSize').text($this.data('size') || '-');
        $('#metaDimensions').text($this.data('dimensions') || '-');

        // Favorite status
        const isFavorite = $this.data('favorite') == '1';
        $('#btnFavorite i').attr('class', isFavorite ? 'bi bi-heart-fill text-danger fs-5' : 'bi bi-heart fs-5');

        const photoExif = $this.data('exif');
        const photoLocation = $this.data('location');

        // Reset and populate EXIF
        $('#metaExifContainer').hide();
        if (photoExif) {
            try {
                const exif = typeof photoExif === 'string' ? JSON.parse(photoExif) : photoExif;
                let exifHtml = '';
                if (exif.Model) exifHtml += `<strong>${exif.Model}</strong><br>`;
                if (exif.ExposureTime) exifHtml += `Exposure: ${exif.ExposureTime}s, `;
                if (exif.FNumber) exifHtml += `f/${exif.FNumber}, `;
                if (exif.ISOSpeedRatings) exifHtml += `ISO ${exif.ISOSpeedRatings}`;

                $('#metaExif').html(exifHtml);
                $('#metaExifContainer').show();
            } catch (e) {
                console.error('Error parsing EXIF:', e);
            }
        }

        // Reset and populate Location
        $('#metaLocationContainer').hide();
        if (photoLocation) {
            const parts = photoLocation.split(',');
            if (parts.length === 2) {
                const lat = parts[0];
                const lng = parts[1];
                $('#metaLocation').attr('href', `https://www.google.com/maps?q=${lat},${lng}`)
                    .text(`${parseFloat(lat).toFixed(4)}, ${parseFloat(lng).toFixed(4)}`);
                $('#metaLocationContainer').show();
            }
        }

        $lightboxImageContainer.empty();
        if (dataType === 'video') {
            $lightboxImageContainer.append(`<video src="${fullUrl}" class="img-fluid" style="max-height: 100vh; max-width: 100%;" controls autoplay></video>`);
        } else {
            $lightboxImageContainer.append(`<img src="${fullUrl}" class="img-fluid" style="max-height: 100vh; max-width: 100%; object-fit: contain;">`);
        }

        // Show/hide nav arrows based on position
        $('#btnPrevPhoto').toggle(currentIndex > 0);
        $('#btnNextPhoto').toggle(currentIndex < $allPhotos.length - 1);

        $lightboxModal.show();
    }

    $(document).on('click', '.photo-item', function () {
        // Refresh photo list in case of dynamic changes (AJAX/Masonry)
        $allPhotos = $('.photo-item');
        if (!isSelectMode) { // Only open photo if not in select mode
            openPhoto($allPhotos.index(this));
        }
    });

    $('#btnPrevPhoto').on('click', function (e) {
        e.stopPropagation();
        openPhoto(currentIndex - 1);
    });

    $('#btnNextPhoto').on('click', function (e) {
        e.stopPropagation();
        openPhoto(currentIndex + 1);
    });

    // Keyboard Navigation
    $(document).on('keydown', function (e) {
        if ($('#lightboxModal').is(':visible')) {
            if (e.key === 'ArrowLeft') $('#btnPrevPhoto:visible').click();
            if (e.key === 'ArrowRight') $('#btnNextPhoto:visible').click();
            if (e.key === 'Escape') $lightboxModal.hide();
        }
    });

    // Pause video when lightbox closes
    document.getElementById('lightboxModal').addEventListener('hidden.bs.modal', event => {
        $lightboxImageContainer.find('video').each(function () {
            this.pause();
        });
        currentIndex = -1;
        $('#shareLinkPopup').addClass('d-none');
    });

    $('#btnInfo').on('click', function () {
        $('#metadataPanel').toggleClass('d-none');
    });

    $('#btnCloseMetadata').on('click', function () {
        $('#metadataPanel').addClass('d-none');
    });

    // Public Sharing Link
    $('#btnShareLink').on('click', function () {
        if (!currentPhotoId) return;

        $.post(BASE_URL + 'photos/generate-link/' + currentPhotoId, function (res) {
            if (res.status === 'success') {
                $('#sharedUrlText').text(res.url);
                $('#shareLinkPopup').removeClass('d-none').hide().fadeIn(200);
            }
        });
    });

    $('#btnCopyLink').on('click', function () {
        const url = $('#sharedUrlText').text();
        navigator.clipboard.writeText(url).then(() => {
            const $btn = $(this);
            const originalText = $btn.text();
            $btn.text('Copied!').addClass('btn-success').removeClass('btn-primary');
            setTimeout(() => {
                $btn.text(originalText).addClass('btn-primary').removeClass('btn-success');
                $('#shareLinkPopup').fadeOut(300, function () { $(this).addClass('d-none'); });
            }, 2000);
        });
    });

    // Photo Actions
    $('#btnArchive').on('click', function () {
        if (!currentPhotoId) return;
        $.post(BASE_URL + 'photos/archive/' + currentPhotoId, function (res) {
            if (res.status === 'success') {
                $lightboxModal.hide();
                $(`[data-id="${currentPhotoId}"]`).fadeOut(300, function () { $(this).remove(); });
            }
        });
    });

    $('#btnDelete').on('click', function () {
        if (!currentPhotoId) return;
        const context = window.location.pathname.split('/').pop() || 'index';
        if (context === 'trash' && !confirm('Permanently delete this photo? This cannot be undone.')) return;

        $.post(BASE_URL + 'photos/delete/' + currentPhotoId, function (res) {
            if (res.status === 'success') {
                $lightboxModal.hide();
                $(`[data-id="${currentPhotoId}"]`).fadeOut(300, function () { $(this).remove(); });
            }
        });
    });

    $('#btnRestore').on('click', function () {
        if (!currentPhotoId) return;
        $.post(BASE_URL + 'photos/restore/' + currentPhotoId, function (res) {
            if (res.status === 'success') {
                $lightboxModal.hide();
                $(`[data-id="${currentPhotoId}"]`).fadeOut(300, function () { $(this).remove(); });
            }
        });
    });

    // Favorites Logic
    $('#btnFavorite').on('click', function () {
        if (!currentPhotoId) return;
        const $btn = $(this);

        $.post(BASE_URL + 'photos/favorite/' + currentPhotoId, function (res) {
            if (res.status === 'success') {
                const $item = $(`[data-id="${currentPhotoId}"]`);
                $item.data('favorite', res.is_favorite ? '1' : '0');

                // Toggle heart in lightbox
                $btn.find('i').attr('class', res.is_favorite ? 'bi bi-heart-fill text-danger fs-5' : 'bi bi-heart fs-5');

                // Toggle heart on grid item
                if (res.is_favorite) {
                    if ($item.find('.bi-heart-fill').length === 0) {
                        $item.prepend('<div class="position-absolute top-0 start-0 p-2" style="z-index: 5;"><i class="bi bi-heart-fill text-danger shadow-sm"></i></div>');
                    }
                } else {
                    $item.find('.bi-heart-fill').parent().remove();
                    // If we are on the favorites page, remove the item from view
                    if (window.location.pathname.includes('favorites')) {
                        $lightboxModal.hide();
                        $item.fadeOut(300, function () { $(this).remove(); });
                    }
                }
            }
        });
    });

    // Albums Logic
    const $addToAlbumModal = new bootstrap.Modal('#addToAlbumModal');

    $('#btnAddToAlbum').on('click', function () {
        if (!currentPhotoId) return;
        $addToAlbumModal.show();

        const $container = $('#albumListContainer');

        $container.html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>');

        $.get(BASE_URL + 'albums', { json: 1 }, function (res) {
            if (res.albums) {
                if (res.albums.length === 0) {
                    $container.html('<div class="text-center p-3 text-muted small">No albums found. Create one first!</div>');
                    return;
                }

                let html = '';
                res.albums.forEach(album => {
                    html += `<button type="button" class="list-group-item list-group-item-action bg-transparent text-white border-secondary small py-2 btn-confirm-add" data-album-id="${album.id}">${album.name}</button>`;
                });
                $container.html(html);
            }
        });
    });

    $(document).on('click', '.btn-confirm-add', function () {
        const albumId = $(this).data('album-id');

        $.post(BASE_URL + 'albums/add-photo', { album_id: albumId, photo_id: currentPhotoId }, function (res) {
            if (res.status === 'success') {
                $addToAlbumModal.hide();
                alert('Added to album!');
            } else {
                alert(res.message);
            }
        });
    });

    $('#formCreateAlbum').on('submit', function (e) {
        e.preventDefault();
        $.post(BASE_URL + 'albums/create', $(this).serialize(), function (res) {
            if (res.status === 'success') {
                location.reload();
            } else {
                alert(res.message);
            }
        });
    });

    // --- Search Logic ---
    $('#searchInput').on('keypress', function (e) {
        if (e.which === 13) { // Enter key
            const q = $(this).val();
            const url = new URL(window.location.href);
            if (q) url.searchParams.set('q', q);
            else url.searchParams.delete('q');
            window.location.href = url.href;
        }
    });

    // --- Bulk Selection Logic ---
    const $bulkToolbar = $('#bulkActionsToolbar');
    const $selectedCount = $('#selectedCount');

    $('#btnToggleSelect, #btnCancelSelect').on('click', function () {
        isSelectMode = !isSelectMode;
        toggleSelectMode();
    });

    function toggleSelectMode() {
        isSelectMode ? $('body').addClass('select-mode') : $('body').removeClass('select-mode');
        $('#btnToggleSelect').toggleClass('btn-primary btn-outline-secondary');
        $('#selectModeText').text(isSelectMode ? 'Cancel' : 'Select');
        $('.selection-overlay').toggleClass('d-none', !isSelectMode);

        if (!isSelectMode) {
            selectedIds.clear();
            $('.photo-item').removeClass('selected').find('.bi-check-lg').addClass('d-none');
            updateBulkToolbar();
        }
    }

    $(document).on('click', '.photo-item', function (e) {
        if (!isSelectMode) return;

        e.preventDefault();
        e.stopPropagation();

        const id = $(this).data('id');
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
            $(this).removeClass('selected').find('.bi-check-lg').addClass('d-none');
        } else {
            selectedIds.add(id);
            $(this).addClass('selected').find('.bi-check-lg').removeClass('d-none');
        }

        updateBulkToolbar();
    });

    function updateBulkToolbar() {
        const count = selectedIds.size;
        $selectedCount.text(count);
        $bulkToolbar.toggleClass('d-none', count === 0);
    }

    // --- Bulk Actions ---
    $('#bulkFavorite, #bulkArchive, #bulkDelete').on('click', function () {
        const action = $(this).attr('id').replace('bulk', '').toLowerCase();
        if (selectedIds.size === 0) return;

        if (action === 'delete' && !confirm(`Delete ${selectedIds.size} selected photos?`)) return;

        $.post(BASE_URL + 'bulk-action', {
            action: action,
            ids: Array.from(selectedIds)
        }, function (res) {
            if (res.status === 'success') {
                location.reload();
            }
        });
    });

    $('#bulkAddToAlbum').on('click', function () {
        if (selectedIds.size === 0) return;
        $addToAlbumModal.show();

        // Re-use album list fetching logic
        const $container = $('#albumListContainer');
        $container.html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>');

        $.get(BASE_URL + 'albums', { json: 1 }, function (res) {
            if (res.albums) {
                if (res.albums.length === 0) {
                    $container.html('<div class="text-center p-3 text-muted small">No albums found.</div>');
                    return;
                }

                let html = '';
                res.albums.forEach(album => {
                    html += `<button type="button" class="list-group-item list-group-item-action bg-transparent text-white border-secondary small py-2 btn-confirm-bulk-add" data-album-id="${album.id}">${album.name}</button>`;
                });
                $container.html(html);
            }
        });
    });

    $(document).on('click', '.btn-confirm-bulk-add', function () {
        const albumId = $(this).data('album-id');
        $.post(BASE_URL + 'bulk-action', {
            action: 'add_to_album',
            album_id: albumId,
            ids: Array.from(selectedIds)
        }, function (res) {
            if (res.status === 'success') {
                location.reload();
            }
        });
    });

    // Scan Logic
    $('#btnScan').on('click', function () {
        $loading.css('display', 'flex');
        $.ajax({
            url: 'scan',
            method: 'GET',
            success: function (response) {
                alert(response.message);
                location.reload();
            },
            error: function () {
                alert('Scan failed.');
            },
            complete: function () {
                $loading.hide();
            }
        });
    });

    // Dropzone Initialization
    if ($('#photoDropzone').length) {
        let myDropzone = new Dropzone("#photoDropzone", {
            paramName: "file",
            maxFilesize: 250, // MB
            acceptedFiles: "image/*,video/*",
            timeout: 60000,
            dictDefaultMessage: "Drop photos here or click to upload",
            init: function () {
                this.on("queuecomplete", function (file) {
                    // Reload page when all uploads in the queue are complete
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                });
                this.on("error", function (file, message) {
                    console.error("Upload Error:", message);
                });
            }
        });
    }
});
