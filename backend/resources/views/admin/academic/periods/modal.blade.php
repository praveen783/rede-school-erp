<div class="modal fade" id="periodModal">
    <div class="modal-dialog">
        <div class="modal-content p-4">

            <h5 class="mb-3">Period Details</h5>

            <form id="periodForm">

                <input type="hidden" id="periodId">

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" id="periodName" class="form-control" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Start Time</label>
                        <input type="time" id="startTime" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>End Time</label>
                        <input type="time" id="endTime" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Order</label>
                    <input type="number" id="periodOrder" class="form-control" required>
                </div>

                <div class="form-check mb-2">
                    <input type="checkbox" id="isBreak" class="form-check-input">
                    <label class="form-check-label">Is Break (Lunch)</label>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" id="isActive" class="form-check-input" checked>
                    <label class="form-check-label">Active</label>
                </div>

                <div class="text-end">
                    <button type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="button"
                        class="btn btn-primary"
                        onclick="savePeriod()">
                        Save
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>