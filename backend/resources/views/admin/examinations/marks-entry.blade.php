@extends('layouts.admin')

@section('title', 'Marks Entry')

@section('content')

<div class="content-body">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="form-head mb-4">
            <h2 class="text-black font-w700 mb-1">Marks Entry</h2>
            <p class="mb-0 text-muted">
                Enter and manage student marks for examinations
            </p>
        </div>

        <!-- Marks Entry Card -->
        <div class="card">
            <div class="card-body">

                <div class="text-center text-muted py-5">
                    <h4>Marks Entry Module</h4>
                    <p>This section will allow teachers/admin to enter exam marks.</p>
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