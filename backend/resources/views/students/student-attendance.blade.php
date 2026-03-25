@extends('layouts.student')

@section('title', 'My Attendance')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        My Attendance
    </h2>
    <p class="text-muted mb-0">
        View and filter your attendance records
    </p>
</div>

<!-- ================= FILTERS ================= -->
<div class="row mb-4">
    <div class="col-md-3">
        <label class="form-label">Month</label>
        <select class="form-control" id="filterMonth">
            <option value="">All Months</option>
            @for ($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}">
                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Year</label>
        <select class="form-control" id="filterYear">
            <option value="2026">2026</option>
            <option value="2025">2025</option>
        </select>
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-primary w-100" id="applyFilterBtn">
            Apply Filters
        </button>
    </div>
</div>

<!-- ================= SUMMARY CARDS ================= -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h4 id="totalDays">--</h4>
                <p class="mb-0">Total Days</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body text-success">
                <h4 id="presentDays">--</h4>
                <p class="mb-0">Present</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body text-danger">
                <h4 id="absentDays">--</h4>
                <p class="mb-0">Absent</p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body text-primary">
                <h4 id="attendancePercent">--%</h4>
                <p class="mb-0">Attendance %</p>
            </div>
        </div>
    </div>
</div>

<!-- ================= ATTENDANCE TABLE ================= -->
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Attendance Details</h4>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
            <table class="table table-striped mb-0">
                <thead class="table-dark sticky-top">
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="attendanceTable">
                    <tr>
                        <td colspan="2" class="text-center text-muted">
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

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user || user.role !== "student") {
        localStorage.clear();
        window.location.href = "{{ url('/login') }}";
        return;
    }

    // Load header profile
    apiRequest("GET", "/me")
		.done(function (res) {

			const userName = res.name ?? "Admin";
			const userRole = res.role ?? "--";

			document.getElementById("headerUserName").innerText = userName;

			const roleMap = {
				
				student: "Student",
			};

			document.getElementById("headerUserRole").innerText =
				roleMap[userRole] ?? userRole;

				console.log("Logged-in user loaded:", res);
			})

			.fail(function (err) {
				console.error("Failed to load user profile", err);
			});

    let attendanceData = [];

    // ================= FETCH ATTENDANCE =================
    apiRequest("GET", "/student/attendance")
        .done(function (res) {
            attendanceData = res.attendance || [];
            renderAttendance(attendanceData);
        })
        .fail(function () {
            alert("Failed to load attendance data");
        });

    // ================= APPLY FILTER =================
    document.getElementById("applyFilterBtn")
        .addEventListener("click", function () {

        const month = document.getElementById("filterMonth").value;
        const year  = document.getElementById("filterYear").value;

        let filtered = attendanceData.filter(row => {

            const date = new Date(row.attendance_date);
            const rowMonth = date.getMonth() + 1;
            const rowYear  = date.getFullYear();

            if (month && rowMonth != month) return false;
            if (year && rowYear != year) return false;

            return true;
        });

        renderAttendance(filtered);
    });

    // ================= RENDER FUNCTION =================
    function renderAttendance(data) {

        const tbody = document.getElementById("attendanceTable");
        tbody.innerHTML = "";

        let total = data.length;
        let present = 0;

        if (!total) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        No attendance records found
                    </td>
                </tr>
            `;
        }

        data.forEach(row => {

            if (row.status === "present") present++;

            const badge = row.status === "present"
                ? `<span class="badge bg-success">Present</span>`
                : `<span class="badge bg-danger">Absent</span>`;

            tbody.innerHTML += `
                <tr>
                    <td>${row.attendance_date}</td>
                    <td>${badge}</td>
                </tr>
            `;
        });

        const absent = total - present;
        const percentage = total > 0
            ? Math.round((present / total) * 100)
            : 0;

        document.getElementById("totalDays").innerText = total;
        document.getElementById("presentDays").innerText = present;
        document.getElementById("absentDays").innerText = absent;
        document.getElementById("attendancePercent").innerText = percentage + "%";
    }

    // Logout
    document.getElementById("logoutBtn")
        .addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "{{ url('/login') }}";
        });

});
</script>
@endpush