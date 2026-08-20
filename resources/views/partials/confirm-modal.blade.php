{{-- Delete Confirm Modal — accessible replacement for window.confirm() --}}
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">
                    <i class="bi bi-exclamation-triangle text-danger me-1"></i>Konfirmasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="confirmDeleteMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-danger" id="confirmDeleteBtn">
                    <i class="bi bi-trash me-1"></i>Hapus
                </button>
            </div>
        </div>
    </div>
</div>
