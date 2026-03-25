@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')

@section('content')

<div class="row">

    <div class="col-xl-4 col-lg-4 col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 id="classCount">0</h4>
                <p class="mb-0">My Classes</p>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 id="attendanceStatus">--</h4>
                <p class="mb-0">Today's Attendance</p>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4 col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <h4 id="pendingMarks">--</h4>
                <p class="mb-0">Pending Marks</p>
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

    // Frontend Role Guard
    if (!token || !user || user.role !== "teacher") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    // Load teacher profile
    apiRequest("GET", "/me")
        .done(function (res) {
            document.getElementById("headerUserName").innerText = res.name;
            document.getElementById("headerUserRole").innerText = "Teacher";
        });

    // Load classes count
    apiRequest("GET", "/teacher/classes")
        .done(function (res) {
            document.getElementById("classCount").innerText = res.length || 0;
        })
        .fail(function () {
            document.getElementById("classCount").innerText = "0";
        });

    // Temporary placeholders (will connect later)
    document.getElementById("attendanceStatus").innerText = "Check";
    document.getElementById("pendingMarks").innerText = "0";

    // Logout
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "/login";
        });
    }

});
</script>
@endpush