@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')

<!-- Page Title Section (Like Admin) -->
<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        Student Dashboard
    </h2>
    <p class="text-muted mb-0">
        Overview of your academic performance
    </p>
</div>

<div class="row">

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5>Attendance</h5>
                <h2 id="attendancePercent">--%</h2>
                <small>Current Academic Year</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5>Exams Attempted</h5>
                <h2 id="totalExams">--</h2>
                <small>Current Academic Year</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5>Overall Result</h5>
                <h2 id="overallResult">--</h2>
                <small>Status</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <h5>Fee Status</h5>
                <h2 id="feeStatus">--</h2>
                <small>Current Year</small>
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

    if (!token || !user || user.role !== "student") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

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

    apiRequest("GET", "/student/attendance/summary")
        .done(function (res) {
            document.getElementById("attendancePercent").innerText =
                (res.attendance_percentage ?? 0) + "%";
        });

    document.getElementById("logoutBtn").addEventListener("click", function () {
        localStorage.clear();
        window.location.href = "/login";
    });

});
</script>
@endpush

