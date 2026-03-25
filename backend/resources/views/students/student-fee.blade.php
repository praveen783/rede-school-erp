@extends('layouts.student')

@section('title', 'Student | Fee')

@section('content')



        <!-- Page Header -->
        <div class="row page-titles mx-0">
            <div class="col-sm-6 p-md-0">
                <div class="welcome-text">
                    <h4>Fee</h4>
                    <span>View your fee details and payment history</span>
                </div>
            </div>
        </div>

        <!-- Active Fee Card -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Current Fee Details
                        </h4>

                        <p class="mt-3 mb-2">
                            Fee is active for the current academic session.
                        </p>

                        <div id="currentFeeBlock" class="mt-3">
                            <span class="text-muted"></span>
                        </div>

                        <button
                            class="btn btn-primary btn-sm"
                            onclick="viewFeeStructure()">
                            View Fee Structure
                        </button>

                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="row">

            <!-- Previous Fees -->
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="card-title">Previous Fees</h4>
                        <p class="mt-2">
                            View fee details from previous academic years.
                        </p>
                        <a href="{{ url('/student/previous-fees') }}" 
                        class="btn btn-outline-primary btn-sm">
                            View
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h4 class="card-title">Payment History</h4>
                        <p class="mt-2">
                            View all your fee payments and receipts.
                        </p>
                        <a href="{{ url('/student/payment-history') }}" 
                        class="btn btn-outline-primary btn-sm">
                            View
                        </a>
                    </div>
                </div>
            </div>

        </div>

   

@endsection


@push('scripts')
<script>

/* -------------------------------------------------
   View Fee Structure Button Handler
------------------------------------------------- */
function viewFeeStructure() {
    window.location.href = "/student/fee-structure";
}


/* -------------------------------------------------
   Page Load
------------------------------------------------- */
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user") || "{}");

    if (!token || !user || user.role !== "student") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    /* ------------------------------
       Load Header Profile
    ------------------------------ */
    apiRequest("GET", "/me")
    .done(function(res){
        document.getElementById("headerUserName").innerText =
            res.name || "-";

        document.getElementById("headerUserRole").innerText =
            "Student";
    });

    /* ------------------------------
       Logout
    ------------------------------ */
    document.getElementById("logoutBtn")
    .addEventListener("click", function(){
        localStorage.clear();
        window.location.href = "/login";
    });

    /* ------------------------------
       Optional Fee Check
    ------------------------------ */
    apiRequest("GET", "/student/fees")
    .done(function(res){
        const allFees =
            Array.isArray(res.fees)
            ? res.fees
            : [];

        if (allFees.length === 0){
            console.warn("No fees assigned to this student");
        }
    })
    .fail(function(){
        console.error("Failed to fetch fees");
    });

});

</script>
@endpush