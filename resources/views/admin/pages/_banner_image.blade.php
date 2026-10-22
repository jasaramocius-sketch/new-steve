<div class="col-md-12">
    <small class="text-muted d-block mb-2">This image is shown as the background of the page title banner on the frontend. If not set, the page title shows on a plain background.</small>

    @if (isset($page) && $page->image)
        <div class="mb-2 page-image-current">
            <img src="{{ storedImageUrl($page->image, 'assets/images/pages') }}" alt="Current banner image"
                 class="admin-image-preview">
        </div>
    @endif

    <input type="hidden" name="image_from_manager" id="image_from_manager_page">
    <input type="hidden" name="remove_section_image" id="remove_page_image" value="0">
    <div id="impPreview_page" class="d-none mt-2"></div>
    <div class="d-flex gap-2 mt-1">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('remove_page_image').value='0'; impOpen_page()">
            <i class="fas fa-images me-1"></i> Browse Image Manager
        </button>
        <button type="button" id="clear_btn_page" data-preview="impPreview_page" data-current=".page-image-current" class="btn btn-sm btn-outline-danger {{ (isset($page) && $page->image) ? '' : 'd-none' }}" onclick="clearPickerImage('image_from_manager_page','impPreview_page','.page-image-current','remove_page_image')">
            <i class="fas fa-times me-1"></i> Clear Image
        </button>
    </div>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'page', 'targetInput' => 'image_from_manager'])

<script>
// Clear a selected/picked image (hidden input + preview + current image) for image-manager pickers.
function clearPickerImage(hiddenId, previewId, currentImgSel, removeFlagId) {
    var hidden = document.getElementById(hiddenId);
    if (hidden) hidden.value = '';
    var preview = document.getElementById(previewId);
    if (preview) {
        preview.innerHTML = '';
        preview.classList.add('d-none');
    }
    if (currentImgSel) {
        document.querySelectorAll(currentImgSel).forEach(function(el) {
            el.classList.add('d-none');
        });
    }
    if (removeFlagId) {
        var flag = document.getElementById(removeFlagId);
        if (flag) flag.value = '1';
    }
}

// Show the Clear button when an image is present — either an already-saved image
// (.page-image-current) OR a newly picked one (preview div).
document.addEventListener('DOMContentLoaded', function() {
    var obsConfig = { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] };
    document.querySelectorAll('[data-preview]').forEach(function(btn) {
        var previewId  = btn.getAttribute('data-preview');
        var currentSel = btn.getAttribute('data-current');
        var preview    = document.getElementById(previewId);
        if (!preview) return;

        var update = function() {
            var hasPickedImage = preview.querySelector('img') !== null;
            var hasExistingImage = false;
            if (currentSel) {
                document.querySelectorAll(currentSel).forEach(function(el) {
                    if (!el.classList.contains('d-none')) hasExistingImage = true;
                });
            }
            btn.classList.toggle('d-none', !hasPickedImage && !hasExistingImage);
        };

        new MutationObserver(update).observe(preview, obsConfig);
        if (currentSel) {
            document.querySelectorAll(currentSel).forEach(function(el) {
                new MutationObserver(update).observe(el, obsConfig);
            });
        }
    });
});
</script>
