@extends('layouts.admin')

@section('title','Teacher Allocation')

@section('content')

<div class="form-head mb-4">
    <h2>Teacher Allocation</h2>
</div>

<div class="card">
    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-4">
                <select id="classSelect" class="form-control"></select>
            </div>
            <div class="col-md-4">
                <select id="sectionSelect" class="form-control"></select>
            </div>
        </div>

        <div id="allocationContainer" style="display:none;">

            <div class="card mb-4">
                <div class="card-header">
                    <strong>Current Allocation Overview</strong>
                </div>
                <div class="card-body">

                    <!-- CLASS TEACHER -->
                    <div class="mb-4">
                        <h5>Class Teacher</h5>
                        <div id="classTeacherBlock"></div>
                    </div>

                    <!-- SUBJECT TEACHERS -->
                    <h5>Subject Teachers</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody id="subjectTeacherTable">
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>

            <!-- EDIT PANEL (Hidden by default) -->
            <div id="editPanel" style="display:none;">
                <div class="card">
                    <div class="card-header bg-light">
                        <strong>Edit Allocation</strong>
                    </div>
                    <div class="card-body">

                        <div id="editContent"></div>

                        <button class="btn btn-success mt-3" id="saveChangesBtn">
                            Save Changes
                        </button>
                        <button class="btn btn-secondary mt-3"
                                onclick="cancelEdit()">
                            Cancel
                        </button>

                    </div>
                </div>
            </div>

        </div>

        <div class="modal fade" id="assignTeacherModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="assignModalTitle">
                            Assign Teacher
                        </h5>
                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <input type="text"
                            id="teacherSearch"
                            class="form-control mb-3"
                            placeholder="Search teacher...">

                        <select id="teacherSelect"
                                class="form-control">
                        </select>

                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-success"
                                id="saveTeacherAssignment">
                            Save
                        </button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')


<script>
document.addEventListener("DOMContentLoaded", function () {

    const classSelect   = document.getElementById("classSelect");
    const sectionSelect = document.getElementById("sectionSelect");

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    let teachers = [];
    let subjects = [];
    let currentClassId = null;
    let currentSectionId = null;
    let selectedSubjectId = null;
    let isClassTeacherMode = false;

    const modal = new bootstrap.Modal(
        document.getElementById("assignTeacherModal")
    );

     if (!token || !user) {
        window.location.href = "/login";
        return;
    }

    // let teachers = [];
    // let subjects = [];
    // let currentAllocation = null;

   

    // ===============================
    // LOAD ADMIN INFO
    // ===============================
    apiRequest("GET", "/me").done(function (res) {

        const roleMap = {
            super_admin: "Super Admin",
            school_admin: "School Admin"
        };

        document.getElementById("headerUserName").innerText =
            res.name ?? "Admin";

        document.getElementById("headerUserRole").innerText =
            roleMap[res.role] ?? res.role;
    });
    // ============================
    // LOAD CLASSES
    // ============================
    function loadClasses() {

        apiRequest("GET", "/class-sections")
            .done(function (data) {

                const grouped = {};

                data.forEach(item => {
                    if (!grouped[item.class_id]) {
                        grouped[item.class_id] =
                            item.class_name;
                    }
                });

                classSelect.innerHTML =
                    '<option value="">Select Class</option>';

                Object.keys(grouped).forEach(id => {
                    classSelect.innerHTML += `
                        <option value="${id}">
                            ${grouped[id]}
                        </option>`;
                });
            });
    }

    // ============================
    // LOAD SECTIONS
    // ============================
    classSelect.addEventListener("change", function () {

        currentClassId = this.value;
        sectionSelect.innerHTML =
            '<option value="">Select Section</option>';

        if (!currentClassId) return;

        apiRequest("GET", "/class-sections")
            .done(function (data) {

                data.filter(d =>
                    d.class_id == currentClassId
                ).forEach(item => {

                    sectionSelect.innerHTML += `
                        <option value="${item.section_id}">
                            ${item.section_name}
                        </option>`;
                });
            });
    });

    // ============================
    // SECTION CHANGE
    // ============================

    sectionSelect.addEventListener("change", function () {

        currentSectionId = this.value;

        if (!currentSectionId) return;

        document.getElementById("allocationContainer")
            .style.display = "block";

        loadAllocationOverview();
    });

    // ============================
    // LOAD OVERVIEW
    // ============================
    function loadAllocationOverview() {

        Promise.all([
            apiRequest("GET",
                `/classes/${currentClassId}/subjects`),
            apiRequest("GET", "/teachers"),
            apiRequest("GET",
                "/admin/teacher-allocations",
                {
                    class_id: currentClassId,
                    section_id: currentSectionId
                })
        ]).then(([subRes, teacherRes, allocationRes]) => {

            subjects = subRes ?? [];
            teachers = teacherRes.data ?? teacherRes ?? [];
            const allocation = allocationRes;

            renderClassTeacher(allocation.class_teacher);
            renderSubjectTeachers(
                subjects,
                allocation.subject_teachers
            );

        });
    }

    // ============================
    // RENDER CLASS TEACHER
    // ============================
    function renderClassTeacher(data) {

        const container =
            document.getElementById("classTeacherBlock");

        if (data) {

            container.innerHTML = `
                <strong>${data.teacher.name}</strong>
                <button class="btn btn-sm btn-outline-primary ms-3"
                    onclick="openClassTeacherModal()">
                    Update
                </button>
            `;
        } else {

            container.innerHTML = `
                <span class="text-danger">
                    Not Assigned
                </span>
                <button class="btn btn-sm btn-primary ms-3"
                    onclick="openClassTeacherModal()">
                    Assign
                </button>
            `;
        }
    }

    // ============================
    // RENDER SUBJECT TABLE
    // ============================
    function renderSubjectTeachers(subjects, allocations) {

        const table =
            document.getElementById("subjectTeacherTable");

        table.innerHTML = "";

        subjects.forEach(sub => {

            const assigned =
                allocations?.find(a =>
                    a.subject_id == sub.id
                );

            table.innerHTML += `
                <tr>
                    <td>${sub.name}</td>
                    <td>
                        ${
                            assigned
                            ? assigned.teacher.name
                            : '<span class="text-danger">Not Assigned</span>'
                        }
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary"
                            onclick="openSubjectModal(${sub.id})">
                            ${
                                assigned ? 'Update' : 'Assign'
                            }
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    // ============================
    // OPEN CLASS MODAL
    // ============================
    window.openClassTeacherModal = function () {

        isClassTeacherMode = true;
        selectedSubjectId = null;

        populateTeacherSelect();

        document.getElementById("assignModalTitle")
            .innerText = "Assign Class Teacher";

        modal.show();
    };

    // ============================
    // OPEN SUBJECT MODAL
    // ============================
    window.openSubjectModal = function (subjectId) {

        isClassTeacherMode = false;
        selectedSubjectId = subjectId;

        populateTeacherSelect();

        document.getElementById("assignModalTitle")
            .innerText = "Assign Subject Teacher";

        modal.show();
    };

    // ============================
    // POPULATE TEACHER DROPDOWN
    // ============================
    function populateTeacherSelect() {

        const select = document.getElementById("teacherSelect");

        select.innerHTML = '<option value="">Select Teacher</option>';

        teachers.forEach(t => {

            // Get subject names
            const subjectNames = t.subjects && t.subjects.length
                ? t.subjects.map(s => s.name).join(", ")
                : "No Subjects";

            select.innerHTML += `
                <option value="${t.id}">
                    ${t.name} (${subjectNames})
                </option>
            `;
        });
    }

    // ============================
    // SAVE ASSIGNMENT
    // ============================
    document.getElementById("saveTeacherAssignment")
        .addEventListener("click", function () {

        const teacherId =
            document.getElementById("teacherSelect").value;

        if (!teacherId) {
            alert("Select teacher");
            return;
        }

        if (isClassTeacherMode) {

            apiRequest("POST",
                "/admin/class-teacher-assign",
                {
                    class_id: currentClassId,
                    section_id: currentSectionId,
                    teacher_id: teacherId
                }
            )
            .done(function (res) {

                alert(res.message || "Class teacher updated successfully");

                modal.hide();
                loadAllocationOverview();
            })
            .fail(function (xhr) {

                alert(
                    xhr.responseJSON?.message ||
                    "Failed to assign class teacher"
                );
            });

        } else {

            apiRequest("POST",
                "/admin/subject-teacher-assign",
                {
                    class_id: currentClassId,
                    section_id: currentSectionId,
                    allocations: [{
                        subject_id: selectedSubjectId,
                        teacher_id: teacherId
                    }]
                }
            )
            .done(function (res) {

                alert(res.message || "Subject teacher updated successfully");

                modal.hide();
                loadAllocationOverview();
            })
            .fail(function (xhr) {

                alert(
                    xhr.responseJSON?.message ||
                    "Failed to assign subject teacher"
                );
            });
        }
    });

    loadClasses();
});
</script>

@endpush