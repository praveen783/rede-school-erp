@extends('layouts.admin')

@section('title', 'Timetable')

@section('content')

<div class="form-head mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="text-black font-w700 mb-1">Timetable Management</h2>
        <p class="mb-0 text-muted">Create or Manage Class Timetable</p>
    </div>
</div>

{{-- Filters Section --}}
@include('admin.academic.timetable._partials.filters')

{{-- ================= Existing Timetables Section ================= --}}
<hr class="my-4">

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Existing Timetables</h5>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Academic Year</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>

                <tbody id="timetableListBody">
                    <tr>
                        <td colspan="5" class="text-center">
                            Loading...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>

document.addEventListener("DOMContentLoaded", function () {

    // ====================== GLOBAL TOAST ======================
    function showToast(message, type = "success") {

        const bgMap = {
            success: "bg-success",
            error: "bg-danger",
            warning: "bg-warning",
            info: "bg-info"
        };

        const toastId = "toast_" + Date.now();

        const toastHTML = `
            <div id="${toastId}"
                 class="toast align-items-center text-white ${bgMap[type]} border-0 mb-2"
                 role="alert">

                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button"
                            class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        const container = document.getElementById("globalToastContainer");
        if (!container) return;

        container.insertAdjacentHTML("beforeend", toastHTML);

        const toastEl = new bootstrap.Toast(
            document.getElementById(toastId),
            { delay: 3000 }
        );

        toastEl.show();

        document.getElementById(toastId)
            .addEventListener("hidden.bs.toast", function () {
                this.remove();
            });
    }

    // ================= AUTH CHECK =================
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


    // ================= LOAD DATA =================
    loadYears();
    loadClasses();
    loadTimetables();

    // ================= LOAD YEARS =================
    function loadYears() {
        apiRequest("GET", "/academic-years")
            .done(function (years) {

                const select = document.getElementById("yearSelect");
                if (!select) return;

                select.innerHTML = '<option value="">Select Year</option>';

                years.forEach(y => {
                    select.insertAdjacentHTML("beforeend",
                        `<option value="${y.id}">${y.name}</option>`);
                });
            });
    }

    // ================= LOAD CLASSES =================
    let classSectionData = [];

    function loadClasses() {
        apiRequest("GET", "/class-sections")
            .done(function (data) {

                classSectionData = data;

                const grouped = {};
                const select = document.getElementById("classSelect");
                if (!select) return;

                select.innerHTML = '<option value="">Select Class</option>';

                data.forEach(item => {
                    if (!grouped[item.class_id]) {
                        grouped[item.class_id] = item.class_name;
                    }
                });

                Object.keys(grouped).forEach(id => {
                    select.insertAdjacentHTML("beforeend",
                        `<option value="${id}">${grouped[id]}</option>`);
                });
            });
    }

    // ================= LOAD SECTIONS =================
    const classSelect = document.getElementById("classSelect");

    if (classSelect) {
        classSelect.addEventListener("change", function () {

            const classId = this.value;
            const sectionSelect = document.getElementById("sectionSelect");
            if (!sectionSelect) return;

            sectionSelect.innerHTML =
                '<option value="">Select Section</option>';

            if (!classId) return;

            const filtered = classSectionData.filter(
                item => item.class_id == classId
            );

            filtered.forEach(item => {
                sectionSelect.insertAdjacentHTML("beforeend",
                    `<option value="${item.section_id}">
                        ${item.section_name}
                    </option>`);
            });
        });
    }

    // ================= CREATE TIMETABLE =================
    const createBtn = document.getElementById("createTimetableBtn");

    if (createBtn) {
        createBtn.addEventListener("click", function () {

            const yearId = document.getElementById("yearSelect").value;
            const classId = document.getElementById("classSelect").value;
            const sectionId = document.getElementById("sectionSelect").value;

            if (!yearId || !classId || !sectionId) {
                showToast(
                    "Please select Academic Year, Class and Section",
                    "warning"
                );
                return;
            }

            apiRequest("POST", "/timetables", {
                academic_year_id: yearId,
                class_id: classId,
                section_id: sectionId
            })
            .done(function (res) {

                const timetableId = res.data.id;

                showToast("Timetable created successfully", "success");

                setTimeout(() => {
                    window.location.href =
                        `/admin/academic/timetable/${timetableId}/manage`;
                }, 800);
            })
            .fail(function (err) {
                showToast(
                    err.responseJSON?.message ?? "Something went wrong",
                    "error"
                );
            });
        });
    }

    // ================= LOAD EXISTING TIMETABLES =================
    function loadTimetables() {

        apiRequest("GET", "/timetables")
            .done(function (data) {

                const tbody = document.getElementById("timetableListBody");
                if (!tbody) return;

                tbody.innerHTML = "";

                if (!data.length) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center">
                                No Timetables Found
                            </td>
                        </tr>
                    `;
                    return;
                }

                data.forEach(item => {

                    tbody.insertAdjacentHTML("beforeend", `
                        <tr>
                            <td>${item.academic_year?.name ?? '-'}</td>
                            <td>${item.school_class?.name ?? '-'}</td>
                            <td>${item.section?.name ?? '-'}</td>
                            <td>
                                ${item.is_active
                                    ? '<span class="badge bg-success">Active</span>'
                                    : '<span class="badge bg-secondary">Inactive</span>'
                                }
                            </td>
                            <td>
                                <a href="/admin/academic/timetable/${item.id}/manage"
                                   class="btn btn-sm btn-primary">
                                   Manage
                                </a>
                            </td>
                        </tr>
                    `);
                });
            })
            .fail(function () {
                showToast("Failed to load timetables", "error");
            });
    }

});
</script>

@endpush
