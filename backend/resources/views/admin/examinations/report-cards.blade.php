@extends('layouts.admin')

@section('title', 'Report Cards')

@section('content')

<div class="content-body">
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="form-head mb-4">
            <h2 class="text-black font-w700 mb-1">Report Cards</h2>
            <p class="mb-0 text-muted">
                Generate and download student report cards
            </p>
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-body">

                <div class="text-center text-muted py-5">
                    <h4>Report Cards Module</h4>
                    <p>
                        This section will allow admin to generate 
                        printable report cards after results are finalized.
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