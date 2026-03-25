@extends('layouts.admin')

@section('title', 'Manage Attendance')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Manage Attendance</h2>
    <p class="mb-0 text-muted">
        Mark or update class-wise student attendance
    </p>
</div>

<div class="card">
    <div class="card-body">

        <!-- Class Info Header -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Class</label>
                <input type="text" id="className" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Section</label>
                <input type="text" id="sectionName" class="form-control" readonly>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" id="attendanceDate" class="form-control">
            </div>
        </div>

        <!-- Students Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th width="120">Present</th>
                        <th width="120">Absent</th>
                    </tr>
                </thead>
                <tbody id="attendanceTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Loading students...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-end mt-3">
            <button id="saveAttendanceBtn" class="btn btn-success">
                Save Attendance
            </button>
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
        
    // =============================
    // Read Query Params
    // =============================
    const urlParams = new URLSearchParams(window.location.search);

    const classId   = urlParams.get("class_id");
    const sectionId = urlParams.get("section_id");
    const date      = urlParams.get("attendance_date");

    if (!classId || !sectionId || !date) {
        alert("Invalid access");
        window.location.href = "/admin/academic/attendance";
        return;
    }

    const dateInput = document.getElementById("attendanceDate");
    const tableBody = document.getElementById("attendanceTableBody");

    dateInput.value = date;

    // Prevent future date
    if (new Date(date) > new Date()) {
        alert("Future attendance not allowed");
        window.location.href = "/admin/academic/attendance";
        return;
    }

    // =============================
    // Load Class Name
    // =============================
    apiRequest("GET", "/class-sections")
    .done(function (data) {

        const match = data.find(item =>
            item.class_id == classId &&
            item.section_id == sectionId
        );

        if (match) {
            document.getElementById("className").value = match.class_name;
            document.getElementById("sectionName").value = match.section_name;
        }
    });

    // =============================
    // Load Students
    // =============================
    function loadStudents() {

        apiRequest("GET", `/students?class_id=${classId}&section_id=${sectionId}`)
            .done(function (response) {

                const students = response.data;  // 🔥 THIS IS IMPORTANT

                const tbody = document.getElementById("attendanceTableBody");
                tbody.innerHTML = "";

                if (!students || students.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No students found
                            </td>
                        </tr>
                    `;
                    return;
                }

                students.forEach(student => {

                    const row = `
                        <tr data-student-id="${student.id}">
                            <td>${student.admission_no ?? '-'}</td>
                            <td>${student.name}</td>
                            <td class="text-center">
                                <input type="radio"
                                    name="attendance_${student.id}"
                                    value="present" checked>
                            </td>
                            <td class="text-center">
                                <input type="radio"
                                    name="attendance_${student.id}"
                                    value="absent">
                            </td>
                        </tr>
                    `;

                    tbody.insertAdjacentHTML("beforeend", row);
                });
                loadExistingAttendance();
            });
            
        }

    // =============================
    // Load Existing Attendance
    // =============================
    function loadExistingAttendance() {

        apiRequest("GET",
            `/admin/attendance?class_id=${classId}&section_id=${sectionId}&attendance_date=${date}`
        )
        .done(function (response) {

            const alreadyMarked = response.already_marked;
            const studentsData  = response.students ?? [];

            if (!alreadyMarked) {
                // Fresh attendance → default all present
                document.querySelectorAll("#attendanceTableBody tr")
                .forEach(row => {
                    const id = row.dataset.studentId;

                    const presentInput = row.querySelector(
                        `input[name="attendance_${id}"][value="present"]`
                    );

                    if (presentInput) presentInput.checked = true;
                });

                return;
            }

            // 🔥 EXISTING ATTENDANCE → APPLY REAL STATUS
            studentsData.forEach(record => {

                const input = document.querySelector(
                    `input[name="attendance_${record.student_id}"][value="${record.status}"]`
                );

                if (input) input.checked = true;
            });

        });
    }

    // =============================
    // Save / Update Attendance
    // =============================
    document.getElementById("saveAttendanceBtn")
    .addEventListener("click", function () {

        const rows = document.querySelectorAll("#attendanceTableBody tr");

        const attendanceData = [];

        rows.forEach(row => {

            const studentId = row.dataset.studentId;
            if (!studentId) return;

            const status =
                row.querySelector(
                    `input[name="attendance_${studentId}"]:checked`
                )?.value ?? "present";

            attendanceData.push({
                student_id: studentId,
                status: status
            });
        });

        apiRequest("POST", "/admin/attendance", {
            class_id: classId,
            section_id: sectionId,
            attendance_date: date,
            records: attendanceData
        })
        .done(function () {
            alert("Attendance saved successfully");
            window.location.reload();
        })
        .fail(function (err) {

            if (err.status === 422) {
                // Attendance exists → override
                if (confirm("Attendance already exists. Override?")) {

                    apiRequest("PUT", "/admin/attendance/override", {
                        class_id: classId,
                        section_id: sectionId,
                        attendance_date: date,
                        records: attendanceData
                    })
                    .done(function () {
                        alert("Attendance overridden successfully");
                        window.location.reload();
                    });
                }
            } else {
                alert("Failed to save attendance");
            }
        });
    });

    // Initial load
    loadStudents();

});
</script>
@endpush