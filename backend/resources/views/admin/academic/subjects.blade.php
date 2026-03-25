@extends('layouts.admin')

@section('title', 'Subjects')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Subjects Management</h2>
    <p class="mb-0 text-muted">
        Manage global subjects and assign them to classes
    </p>
</div>

<div class="row">

    <!-- ========================================= -->
    <!-- 🟢 LEFT SIDE – GLOBAL SUBJECT MASTER -->
    <!-- ========================================= -->

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">

                <div class="d-flex justify-content-between mb-3">
                    <h4>Subject Master</h4>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        + Add Subject
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="masterSubjectTable">
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Loading subjects...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>


    <!-- ========================================= -->
    <!-- 🔵 RIGHT SIDE – CLASS SUBJECT ASSIGNMENT -->
    <!-- ========================================= -->

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">

                <h4 class="mb-3">Assign Subjects to Class</h4>

                <div class="mb-3">
                    <label class="form-label">Select Class</label>
                    <select id="classSelect" class="form-control">
                        <option value="">Select Class</option>
                    </select>
                </div>

                <div id="classSubjectsContainer" style="display:none;">

                    <h6 class="mb-2">Assigned Subjects</h6>
                    <ul id="assignedSubjectList" class="list-group mb-3"></ul>

                    <h6>Add / Remove Subjects</h6>
                    <div id="subjectCheckboxContainer" class="mb-3"></div>

                    <button class="btn btn-success" id="saveClassSubjectsBtn">
                        Save Changes
                    </button>

                </div>

            </div>
        </div>
    </div>

</div>


<!-- =================== ADD SUBJECT MODAL =================== -->

<div class="modal fade" id="addSubjectModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Subject Name</label>
                    <input type="text" id="subjectName" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject Code</label>
                    <input type="text" id="subjectCode" class="form-control">
                </div>

                <div id="subjectError" class="text-danger small"></div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="saveSubjectBtn">
                    Save
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

    if (!token) {
        window.location.href = "/login";
        return;
    }

     // ===============================
    // LOAD LOGGED-IN USER
    // ===============================
    apiRequest("GET", "/me")
        .done(function (res) {

            const roleMap = {
                super_admin: "Super Admin",
                school_admin: "School Admin"
            };

            document.getElementById("headerUserName").innerText =
                res.name ?? "Admin";

            document.getElementById("headerUserRole").innerText =
                roleMap[res.role] ?? res.role;

        });


    loadClasses();
    loadSubjects();

    // =========================================
    // LOAD CLASSES
    // =========================================
    function loadClasses() {
        apiRequest("GET", "/classes")
            .done(function (res) {

                const classes = res.data ?? res ?? [];
                const select = document.getElementById("classSelect");

                classes.forEach(cls => {
                    select.innerHTML += `
                        <option value="${cls.id}">
                            ${cls.name}
                        </option>
                    `;
                });
            });
    }

    // =========================================
    // LOAD SUBJECT MASTER
    // =========================================
    function loadSubjects() {
        apiRequest("GET", "/subjects")
            .done(function (res) {

                const subjects = res.data ?? res ?? [];
                let rows = "";

                subjects.forEach(subject => {

                    const statusBadge = subject.is_active
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-secondary">Inactive</span>';

                    rows += `
                        <tr>
                            <td>${subject.name}</td>
                            <td>${subject.code ?? '-'}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-danger"
                                    onclick="deactivateSubject(${subject.id})">
                                    Deactivate
                                </button>
                            </td>
                        </tr>
                    `;
                });

                document.getElementById("masterSubjectTable").innerHTML = rows;
            });
    }

    // =========================================
    // CREATE SUBJECT
    // =========================================
    document.getElementById("saveSubjectBtn")
        .addEventListener("click", function () {

            const name = document.getElementById("subjectName").value;
            const code = document.getElementById("subjectCode").value;

            apiRequest("POST", "/subjects", { name, code })
                .done(function () {

                    loadSubjects();
                    bootstrap.Modal.getInstance(
                        document.getElementById("addSubjectModal")
                    ).hide();
                })
                .fail(function (xhr) {

                    document.getElementById("subjectError").innerText =
                        xhr.responseJSON?.message ?? "Error occurred";
                });
        });

    // =========================================
    // CLASS CHANGE
    // =========================================
    document.getElementById("classSelect")
        .addEventListener("change", function () {

            const classId = this.value;

            if (!classId) return;

            document.getElementById("classSubjectsContainer").style.display = "block";

            loadAssignedSubjects(classId);
        });

    // =========================================
    // LOAD ASSIGNED SUBJECTS
    // =========================================
    function loadAssignedSubjects(classId) {

        apiRequest("GET", `/classes/${classId}/subjects`)
            .done(function (assigned) {

                apiRequest("GET", "/subjects")
                    .done(function (allRes) {

                        const allSubjects = allRes.data ?? allRes ?? [];
                        const assignedIds = assigned.map(s => s.id);

                        // Assigned List
                        let listHtml = "";

                        assigned.forEach(sub => {
                            listHtml += `
                                <li class="list-group-item d-flex justify-content-between">
                                    ${sub.name}
                                    <button class="btn btn-sm btn-danger"
                                        onclick="removeSubject(${classId}, ${sub.id})">
                                        Remove
                                    </button>
                                </li>
                            `;
                        });

                        document.getElementById("assignedSubjectList").innerHTML = listHtml;

                        // Checkbox Section
                        let checkboxHtml = "";

                        allSubjects.forEach(sub => {

                            const checked = assignedIds.includes(sub.id) ? "checked" : "";

                            checkboxHtml += `
                                <div class="form-check">
                                    <input class="form-check-input subject-checkbox"
                                        type="checkbox"
                                        value="${sub.id}"
                                        ${checked}>
                                    <label class="form-check-label">
                                        ${sub.name}
                                    </label>
                                </div>
                            `;
                        });

                        document.getElementById("subjectCheckboxContainer").innerHTML = checkboxHtml;

                    });
            });
    }

    // =========================================
    // SAVE CLASS SUBJECTS
    // =========================================
    document.getElementById("saveClassSubjectsBtn")
        .addEventListener("click", function () {

            const classId = document.getElementById("classSelect").value;

            const selected = [];

            document.querySelectorAll(".subject-checkbox:checked")
                .forEach(cb => selected.push(parseInt(cb.value)));

            apiRequest("PUT", `/classes/${classId}/subjects`, {
                subjects: selected
            })
            .done(function (res) {
                alert(res.message);
                loadAssignedSubjects(classId);
            });
        });

});

// GLOBAL FUNCTIONS
function deactivateSubject(id) {
    apiRequest("PATCH", `/subjects/${id}/deactivate`)
        .done(() => location.reload());
}

function removeSubject(classId, subjectId) {
    apiRequest("DELETE", `/classes/${classId}/subjects/${subjectId}`)
        .done(() => location.reload());
}
</script>
@endpush