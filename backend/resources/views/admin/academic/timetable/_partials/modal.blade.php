<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- 🔹 HEADER -->
            <div class="modal-header">
                <h5 class="modal-title">Assign Period</h5>
                <button type="button" 
                        class="btn-close" 
                        data-bs-dismiss="modal">
                </button>
            </div>

            <!-- 🔹 BODY -->
            <div class="modal-body">

                <!-- Hidden Fields -->
                <input type="hidden" id="modalEntryId">
                <input type="hidden" id="modalDay">
                <input type="hidden" id="modalPeriod">

                <!-- Subject Dropdown -->
                <div class="mb-3">
                    <label class="form-label">Subject</label>
                    <select id="modalSubject" class="form-control"></select>
                </div>

                <!-- Teacher Dropdown -->
                <div class="mb-3">
                    <label class="form-label">Teacher</label>
                    <select id="modalTeacher" class="form-control"></select>
                </div>

            </div>

            <!-- 🔹 FOOTER -->
            <div class="modal-footer">

                <button type="button"
                        class="btn btn-danger"
                        id="deleteEntryBtn"
                        style="display:none;">
                    Delete
                </button>

                <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-primary"
                        onclick="saveEntry()">
                    Save
                </button>

            </div>

        </div>
    </div>
</div>