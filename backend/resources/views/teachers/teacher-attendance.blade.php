@extends('layouts.teacher')

@section('title', 'Teacher Attendance')

@section('content')

<!-- FILTERS -->
<div class="card mb-4">
    <div class="card-body row">

        <div class="col-md-4">
            <label>Class & Section</label>
            <select id="classSelect" class="form-control">
                <option value="">Select</option>
            </select>
        </div>

        <div class="col-md-4">
            <label>Date</label>
            <input type="date" id="attendanceDate" class="form-control">
        </div>

        <div class="col-md-4 d-flex align-items-end">
            <button id="loadStudentsBtn" class="btn btn-primary w-100">
                Load Students
            </button>
        </div>

    </div>
</div>

<!-- STUDENTS TABLE -->
<div class="card">
    <div class="card-body">

        <table class="table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="studentsTable">
                <tr>
                    <td colspan="2" class="text-center">
                        Select class & date
                    </td>
                </tr>
            </tbody>
        </table>

        <button id="submitAttendanceBtn"
                class="btn btn-success mt-3"
                disabled>
            Submit Attendance
        </button>

    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user || user.role !== "teacher") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    /* ---------------- HEADER PROFILE ---------------- */
    apiRequest("GET", "/teacher/me")
        .done(res => {
            document.getElementById("headerUserName").innerText = res.name;
            document.getElementById("headerUserRole").innerText = "Teacher";
        });

    const today = new Date().toISOString().split("T")[0];
    attendanceDate.value = today;
    attendanceDate.max   = today;

    /* ---------------- LOAD ASSIGNED CLASSES ---------------- */
    apiRequest("GET", "/teacher/class-teacher-classes")
        .done(res => {
            const select = document.getElementById("classSelect");
            res.forEach(item => {
                const opt = document.createElement("option");
                opt.value = `${item.class_id}|${item.section_id}`;
                opt.text  = `${item.class_name} - ${item.section_name}`;
                select.appendChild(opt);
            });
        });

    /* ---------------- LOAD STUDENTS / ATTENDANCE ---------------- */
    document.getElementById("loadStudentsBtn").onclick = () => {

        const value = classSelect.value;
        if (!value) return alert("Please select class & section");

        if (attendanceDate.value !== today) {
            alert("Attendance can be taken only for today");
            return;
        }

        const [class_id, section_id] = value.split("|");

        studentsTable.innerHTML = `
            <tr>
                <td colspan="2" class="text-center">Loading...</td>
            </tr>`;
        submitAttendanceBtn.disabled = true;

        apiRequest("GET", "/teacher/attendance", {
            class_id,
            section_id,
            attendance_date: attendanceDate.value
        })
        .done(res => {
            if (res.length > 0) {
                renderReadOnlyAttendance(res);
                submitAttendanceBtn.style.display = "none";
            } else {
                loadStudentsForMarking(class_id, section_id);
            }
        })
        .fail(err => {
            if (err.status === 403) {
                studentsTable.innerHTML = `
                    <tr>
                        <td colspan="2" class="text-center text-danger">
                            Only class teacher can take attendance
                        </td>
                    </tr>`;
            } else {
                alert("Unable to load attendance");
            }
        });
    };

    function loadStudentsForMarking(class_id, section_id) {

        submitAttendanceBtn.style.display = "inline-block";

        apiRequest("GET", "/teacher/students", {
            class_id, section_id
        })
        .done(res => {

            studentsTable.innerHTML = "";

            if (res.length === 0) {
                studentsTable.innerHTML = `
                    <tr>
                        <td colspan="2" class="text-center">
                            No students found
                        </td>
                    </tr>`;
                return;
            }

            res.forEach(stu => {
                studentsTable.innerHTML += `
                    <tr>
                        <td>${stu.name}</td>
                        <td>
                            <select class="form-control status" data-id="${stu.id}">
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                            </select>
                        </td>
                    </tr>`;
            });

            submitAttendanceBtn.disabled = false;
        });
    }

    function renderReadOnlyAttendance(data) {
        studentsTable.innerHTML = "";
        data.forEach(row => {
            studentsTable.innerHTML += `
                <tr>
                    <td>${row.student.name}</td>
                    <td>
                        <span class="badge bg-${row.status === 'present' ? 'success' : 'danger'}">
                            ${row.status.toUpperCase()}
                        </span>
                    </td>
                </tr>`;
        });
    }

    submitAttendanceBtn.onclick = () => {

        const value = classSelect.value;
        if (!value) return;

        const [class_id, section_id] = value.split("|");

        const records = [];
        document.querySelectorAll(".status").forEach(el => {
            records.push({
                student_id: el.dataset.id,
                status: el.value
            });
        });

        if (records.length === 0) {
            alert("No students to submit");
            return;
        }

        apiRequest("POST", "/teacher/attendance", {
            class_id,
            section_id,
            attendance_date: attendanceDate.value,
            records
        })
        .done(() => {
            alert("Attendance submitted successfully");
            submitAttendanceBtn.disabled = true;
            submitAttendanceBtn.innerText = "Attendance Submitted";
        })
        .fail(err => {
            alert(err.responseJSON?.message || "Attendance submission failed");
        });
    };

    document.getElementById("logoutBtn")?.addEventListener("click", function () {
        localStorage.clear();
        window.location.href = "/login";
    });

});
</script>
@endpush