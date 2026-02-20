$(document).ready(function () {
    const $loading = $('#loadingOverlay');

    // Sidebar Toggle
    $('#sidebarToggle').on('click', function () {
        $('#sidebarMenu').toggleClass('active');
    });

    // Lightbox Logic
    const $lightboxModal = new bootstrap.Modal('#lightboxModal');
    const $lightboxImage = $('#lightboxImage');
    let currentPhotoId = null;

    $('.photo-item').on('click', function () {
        const $this = $(this);
        const fullUrl = $this.data('full');
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

        $lightboxImage.attr('src', fullUrl);
        $lightboxModal.show();
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

    // Dropzone logic is handled automatically by the dropzone class in the HTML.
});
