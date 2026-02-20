$(document).ready(function () {
    const $loading = $('#loadingOverlay');

    // Sidebar Toggle
    $('#sidebarToggle').on('click', function () {
        $('#sidebarMenu').toggleClass('active');
    });

    // Lightbox Logic
    const $lightboxModal = new bootstrap.Modal('#lightboxModal');
    const $lightboxImage = $('#lightboxImage');

    $('.photo-item').on('click', function () {
        const $this = $(this);
        const fullUrl = $this.data('full');

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

    // Upload Logic
    $('#fileInput').on('change', function () {
        const files = this.files;
        if (files.length === 0) return;

        let uploadedCount = 0;
        let errorCount = 0;
        const totalFiles = files.length;
        $loading.css('display', 'flex');

        function uploadNext(index) {
            if (index >= totalFiles) {
                if (errorCount > 0) {
                    alert('Transfers complete. ' + uploadedCount + ' successful, ' + errorCount + ' failed.');
                }
                location.reload();
                return;
            }

            const formData = new FormData();
            formData.append('file', files[index]);

            $.ajax({
                url: 'upload',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                timeout: 30000, // 30 second timeout
                success: function (response) {
                    if (response.status === 'success') {
                        uploadedCount++;
                    } else {
                        errorCount++;
                        console.error('Upload failed for ' + files[index].name + ':', response.message);
                    }
                },
                error: function (xhr, status, error) {
                    errorCount++;
                    console.error('Upload error for ' + files[index].name, status, error);
                },
                complete: function () {
                    uploadNext(index + 1);
                }
            });
        }

        uploadNext(0);
    });
});
