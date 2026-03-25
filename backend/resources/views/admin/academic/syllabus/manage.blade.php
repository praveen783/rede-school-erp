@extends('layouts.admin')

@section('title', 'Manage Syllabus')

@section('content')

<div class="form-head mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="text-black font-w700 mb-1" id="syllabusTitle">
            Loading...
        </h2>
        <p class="mb-0 text-muted" id="syllabusMeta"></p>
    </div>

    <a href="{{ url('admin/academic/syllabus') }}"
       class="btn btn-secondary">
        ← Back to List
    </a>
</div>

<div class="row">

    <!-- ===============================
         UNITS SECTION
    ================================ -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Units</h5>
                <button class="btn btn-sm btn-primary" id="addUnitBtn">
                    + Add Unit
                </button>
            </div>
            <div class="card-body">
                <div id="unitsList">
                    <p class="text-muted">Loading units...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ===============================
         RESOURCES SECTION
    ================================ -->
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Resources</h5>
                <button class="btn btn-sm btn-success" id="uploadPdfBtn">
                    + Upload PDF
                </button>
            </div>
            <div class="card-body">
                <div id="resourcesList">
                    <p class="text-muted">Loading resources...</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ===============================
     ADD UNIT MODAL
================================ -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Unit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <div class="mb-3">
            <label class="form-label">Unit Title</label>
            <input type="text" id="unitTitle" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Learning Outcomes</label>
            <textarea id="learningOutcomes" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Estimated Hours</label>
            <input type="number" id="estimatedHours" class="form-control">
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
        </button>
        <button class="btn btn-primary" id="saveUnitBtn">
            Save Unit
        </button>
      </div>

    </div>
  </div>
</div>

<!-- ===============================
     UPLOAD PDF MODAL
================================ -->
<div class="modal fade" id="uploadPdfModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload PDF Resource</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Resource Title</label>
                    <input type="text" class="form-control" id="resourceTitle">
                </div>

                <div class="mb-3">
                    <label class="form-label">Select PDF</label>
                    <input type="file" class="form-control" id="resourceFile" accept=".pdf">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-success" id="savePdfBtn">
                    Upload
                </button>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>

document.addEventListener("DOMContentLoaded", function () {
    
    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }
    // Load logged-in user
    apiRequest("GET", "/me")
        .done(function (res) {

            const userName = res.name ?? "Admin";
            const userRole = res.role ?? "--";

            document.getElementById("headerUserName").innerText = userName;

            const roleMap = {
                super_admin: "Super Admin",
                school_admin: "School Admin"
            };

            document.getElementById("headerUserRole").innerText =
                roleMap[userRole] ?? userRole;

        })
        .fail(function () {
            console.error("Failed to load profile");
        });

    const syllabusId = "{{ request()->route('id') }}";

    loadSyllabus();

    // ================================
    // LOAD SYLLABUS DETAILS
    // ================================
    function loadSyllabus() {

        apiRequest("GET", `/syllabus/${syllabusId}`)
            .done(function (response) {

                const data = response.data;

                document.getElementById("syllabusTitle").innerText = data.title;

                document.getElementById("syllabusMeta").innerText =
                    `${data.class?.name} | ${data.subject?.name} | ${data.board?.name}`;

                renderUnits(data.units);
                renderResources(data.resources);
            });
    }

    // ================================
    // RENDER UNITS
    // ================================
    function renderUnits(units) {

        const container = document.getElementById("unitsList");
        container.innerHTML = "";

        if (!units || units.length === 0) {
            container.innerHTML = `<p class="text-muted">No units added</p>`;
            return;
        }

        units.forEach(unit => {

            const html = `
                <div class="border rounded p-2 mb-2">
                    <strong>${unit.unit_order}. ${unit.unit_title}</strong>
                    <br>
                    <small>${unit.learning_outcomes ?? ''}</small>
                    <div class="mt-2 text-end">
                        <button class="btn btn-sm btn-danger deleteUnitBtn"
                                data-id="${unit.id}">
                            Delete
                        </button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML("beforeend", html);
        });
    }

    // ================================
    // RENDER RESOURCES
    // ================================
    function renderResources(resources) {

        const container = document.getElementById("resourcesList");
        container.innerHTML = "";

        if (!resources || resources.length === 0) {
            container.innerHTML = `<p class="text-muted">No resources uploaded</p>`;
            return;
        }

        resources.forEach(res => {

            const html = `
                <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center">
                    <div>
                        📄 ${res.resource_title}
                    </div>
                    <div>
                        <a href="${res.resource_url}" target="_blank"
                           class="btn btn-sm btn-primary">
                           View
                        </a>
                        <button class="btn btn-sm btn-danger deleteResourceBtn"
                                data-id="${res.id}">
                            Delete
                        </button>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML("beforeend", html);
        });
    }

    // ================================
    // OPEN ADD UNIT MODAL
    // ================================
    document.getElementById("addUnitBtn")
        .addEventListener("click", function () {

            const modal = new bootstrap.Modal(
                document.getElementById("addUnitModal")
            );
            modal.show();
        });

    document.getElementById("uploadPdfBtn")
        .addEventListener("click", function () {

            const modal = new bootstrap.Modal(
                document.getElementById("uploadPdfModal")
            );

            modal.show();
        });

    document.getElementById("savePdfBtn")
        .addEventListener("click", function () {

            const title = document.getElementById("resourceTitle").value;
            const fileInput = document.getElementById("resourceFile");

            if (!title || !fileInput.files.length) {
                alert("Title and file are required");
                return;
            }

            const formData = new FormData();
            formData.append("resource_type", "pdf");
            formData.append("resource_title", title);
            formData.append("file", fileInput.files[0]);

            $.ajax({
                url: `/api/syllabus/${syllabusId}/resources`,
                method: "POST",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("auth_token"),
                    "Accept": "application/json"
                },
                data: formData,
                processData: false,   // VERY IMPORTANT
                contentType: false,   // VERY IMPORTANT
                success: function (response) {

                    alert("PDF uploaded successfully");

                    // Close modal
                    bootstrap.Modal.getInstance(
                        document.getElementById("uploadPdfModal")
                    ).hide();

                    // Reset form
                    document.getElementById("resourceTitle").value = "";
                    document.getElementById("resourceFile").value = "";

                    loadResources(); // refresh list
                },
                error: function (err) {
                    alert(err.responseJSON?.message ?? "Upload failed");
                }
            });

        });

    function loadResources() {

        apiRequest("GET", `/syllabus/${syllabusId}`)
            .done(function (response) {

                const resources = response.data.resources;
                const container = document.getElementById("resourcesList");

                if (!resources.length) {
                    container.innerHTML =
                        '<p class="text-muted">No resources added yet</p>';
                    return;
                }

                container.innerHTML = "";

                resources.forEach(res => {

                    container.innerHTML += `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <a href="${res.resource_url}" target="_blank">
                                📄 ${res.resource_title}
                            </a>
                            <button class="btn btn-sm btn-danger deleteResourceBtn"
                                data-id="${res.id}">
                                Delete
                            </button>
                        </div>
                    `;
                });
            });
    }

    // ================================
    // SAVE UNIT
    // ================================
    document.getElementById("saveUnitBtn")
        .addEventListener("click", function () {

            const title = document.getElementById("unitTitle").value;
            const outcomes = document.getElementById("learningOutcomes").value;
            const hours = document.getElementById("estimatedHours").value;

            if (!title) {
                alert("Unit title is required");
                return;
            }

            // Auto calculate next order
            const currentUnits =
                document.querySelectorAll("#unitsList .border").length;

            const nextOrder = currentUnits + 1;

            apiRequest("POST", `/syllabus/${syllabusId}/units`, {
                unit_title: title,
                unit_order: nextOrder,
                learning_outcomes: outcomes,
                estimated_hours: hours
            })
            .done(function () {

                bootstrap.Modal.getInstance(
                    document.getElementById("addUnitModal")
                ).hide();

                loadSyllabus();

                document.getElementById("unitTitle").value = "";
                document.getElementById("learningOutcomes").value = "";
                document.getElementById("estimatedHours").value = "";
            })
            .fail(function (err) {
                alert(err.responseJSON?.message ?? "Failed to add unit");
            });
    });
    // ================================
    // DELETE RESOURCE
    // ================================
    document.addEventListener("click", function (e) {

        if (!e.target.classList.contains("deleteResourceBtn")) return;

        const resourceId = e.target.getAttribute("data-id");

        if (!confirm("Delete this resource?")) return;

        apiRequest("DELETE", `/resources/${resourceId}`)
            .done(function () {
                loadResources();
            })
            .fail(function () {
                alert("Failed to delete resource");
            });
    });

});
</script>
@endpush