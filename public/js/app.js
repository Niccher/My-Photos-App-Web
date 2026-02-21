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

    $('.photo-item').on('click', function () {
        const $this = $(this);
        const fullUrl = $this.data('full');
        const dataType = $this.data('type') || 'image';
        currentPhotoId = $this.data('id');
        const context = window.location.pathname.split('/').pop() || 'index';

        if (context === 'trash') {
            $('#btnRestore').show();
            $('#btnArchive').hide();
            $('#btnDelete').attr('title', 'Delete Permanently');
        } else {
            $('#btnRestore').hide();
            $('#btnArchive').show();
            $('#btnArchive i').attr('class', context === 'archive' ? 'bi bi-archive-fill fs-5' : 'bi bi-archive fs-5');
        }

        // Populate metadata
        $('#metaFilename').text($this.data('filename'));
        $('#metaDate').text($this.data('date'));
        $('#metaSize').text($this.data('size'));
        $('#metaDimensions').text($this.data('dimensions'));

        $lightboxImageContainer.empty();
        if (dataType === 'video') {
            $lightboxImageContainer.append(`<video src="${fullUrl}" class="img-fluid" style="max-height: 100vh;" controls autoplay></video>`);
        } else {
            $lightboxImageContainer.append(`<img src="${fullUrl}" class="img-fluid" style="max-height: 100vh;">`);
        }

        $lightboxModal.show();
    });

    // Pause video when lightbox closes
    document.getElementById('lightboxModal').addEventListener('hidden.bs.modal', event => {
        $lightboxImageContainer.find('video').each(function () {
            this.pause();
        });
    });

    $('#btnInfo').on('click', function () {
        $('#metadataPanel').toggleClass('d-none');
    });

    $('#btnCloseMetadata').on('click', function () {
        $('#metadataPanel').addClass('d-none');
    });

    // Photo Actions
    $('#btnArchive').on('click', function () {
        if (!currentPhotoId) return;
        $.post(window.location.origin + '/hosts/Photos/photos/archive/' + currentPhotoId, function (res) {
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

        $.post(window.location.origin + '/hosts/Photos/photos/delete/' + currentPhotoId, function (res) {
            if (res.status === 'success') {
                $lightboxModal.hide();
                $(`[data-id="${currentPhotoId}"]`).fadeOut(300, function () { $(this).remove(); });
            }
        });
    });

    $('#btnRestore').on('click', function () {
        if (!currentPhotoId) return;
        $.post(window.location.origin + '/hosts/Photos/photos/restore/' + currentPhotoId, function (res) {
            if (res.status === 'success') {
                $lightboxModal.hide();
                $(`[data-id="${currentPhotoId}"]`).fadeOut(300, function () { $(this).remove(); });
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
