@extends('layouts.admin')

@section('title', 'Student Promotions')

@section('content')

<div class="content-body">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="form-head mb-4">
            <h2 class="text-black font-w700 mb-1">Student Promotions</h2>
            <p class="mb-0 text-muted">
                Promote students to the next class after final results
            </p>
        </div>

        <!-- Promotions Card -->
        <div class="card">
            <div class="card-body">

                <div class="text-center text-muted py-5">
                    <h4>Promotions Module</h4>
                    <p>
                        This section will allow admin to promote students 
                        based on exam results and academic year.
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const publishBtn = document.getElementById("publishBtn");
    publishBtn.disabled = true;
    publishBtn.addEventListener("click", publishAdmitCards);

    // =========================
    // AUTH
    // =========================
    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }

    // =========================
    // LOAD ADMIN PROFILE
    // =========================
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

    

});
</script>
@endpush