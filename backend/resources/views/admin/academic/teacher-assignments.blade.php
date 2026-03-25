@extends('layouts.admin')

@section('title', 'Class Timetable')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Class Timetable</h2>
    <p class="mb-0 text-muted">
        Manage and view class-wise timetable
    </p>
</div>

<div class="card">
    <div class="card-body">

        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Select Class</label>
                <select id="classSelect" class="form-control">
                    <option value="">Select Class</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Select Section</label>
                <select id="sectionSelect" class="form-control">
                    <option value="">Select Section</option>
                </select>
            </div>

            <div class="col-md-4 d-flex align-items-end">
                <button id="loadTimetableBtn" class="btn btn-primary w-100">
                    Load Timetable
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Day</th>
                        <th>Period</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody id="timetableBody">
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Select class and section to view timetable
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

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }

    // ================================
    // LOAD ADMIN PROFILE
    // ================================
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

    // ================================
    // LOAD CLASSES
    // ================================
    function loadClasses() {
        apiRequest("GET", "/classes")
            .done(function (classes) {

                const select = document.getElementById("classSelect");

                classes.forEach(cls => {
                    select.insertAdjacentHTML("beforeend",
                        `<option value="${cls.id}">${cls.name}</option>`
                    );
                });
            });
    }

    // ================================
    // LOAD SECTIONS WHEN CLASS CHANGES
    // ================================
    document.getElementById("classSelect")
        .addEventListener("change", function () {

            const classId = this.value;
            const sectionSelect = document.getElementById("sectionSelect");

            sectionSelect.innerHTML =
                `<option value="">Select Section</option>`;

            if (!classId) return;

            apiRequest("GET", `/classes/${classId}/sections`)
                .done(function (sections) {

                    sections.forEach(section => {
                        sectionSelect.insertAdjacentHTML("beforeend",
                            `<option value="${section.id}">
                                ${section.name}
                             </option>`
                        );
                    });
                });
        });

    // ================================
    // LOAD TIMETABLE
    // ================================
    document.getElementById("loadTimetableBtn")
        .addEventListener("click", function () {

            const classId   = document.getElementById("classSelect").value;
            const sectionId = document.getElementById("sectionSelect").value;

            if (!classId || !sectionId) {
                alert("Please select class and section");
                return;
            }

            apiRequest("GET",
                `/timetables?class_id=${classId}&section_id=${sectionId}`
            )
            .done(function (data) {

                const tbody = document.getElementById("timetableBody");
                tbody.innerHTML = "";

                if (!data || data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No timetable entries found
                            </td>
                        </tr>
                    `;
                    return;
                }

                data.forEach(item => {

                    const row = `
                        <tr>
                            <td>${item.day}</td>
                            <td>${item.period}</td>
                            <td>${item.subject?.name ?? '-'}</td>
                            <td>${item.teacher?.name ?? '-'}</td>
                            <td>${item.start_time}</td>
                            <td>${item.end_time}</td>
                        </tr>
                    `;

                    tbody.insertAdjacentHTML("beforeend", row);
                });
            })
            .fail(function () {
                alert("Failed to load timetable");
            });
        });

});
</script>
@endpush