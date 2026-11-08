'use strict';

// Keep confirmation links functional even if Bootstrap's CDN is unavailable.
document.querySelectorAll('[data-delete-id]').forEach(link => {
    link.addEventListener('click', event => {
        if (!window.bootstrap?.Modal) return;
        event.preventDefault();
        document.getElementById('deleteRecordId').value = link.dataset.deleteId;
        document.getElementById('deleteRecordLabel').textContent = link.dataset.deleteNumber;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteModal')).show();
    });
});

document.querySelectorAll('img[data-fallback]').forEach(image => {
    image.addEventListener('error', () => {
        image.src = image.dataset.fallback;
        image.alt = 'Foundation archive placeholder; reference image unavailable';
    }, { once: true });
});
