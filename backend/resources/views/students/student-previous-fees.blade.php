@extends('layouts.student')

@section('title', 'Previous Fees')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        Previous Fees
    </h2>
    <p class="text-muted mb-0">
        View fee details from previous academic years
    </p>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered" id="previousFeesTable">
                <thead class="table-light">
                    <tr>
                        <th>Academic Year</th>
                        <th>Class</th>
                        <th>Fee Name</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody id="previousFeesTableBody">
                    <tr>
                        <td colspan="8" class="text-center text-muted">
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
            document.getElementById("headerUserName").innerText =
                res.name ?? "Student";
            document.getElementById("headerUserRole").innerText =
                "Student";
        });

    // Logout
    document.getElementById("logoutBtn")
        .addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "{{ url('/login') }}";
        });

    loadPreviousFees();

    function loadPreviousFees() {

        const tbody = document.getElementById("previousFeesTableBody");

        apiRequest("GET", "/student/previous-fees")
            .done(res => {

                const fees = res.previous_fees || [];
                tbody.innerHTML = "";

                if (fees.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No previous academic records found
                            </td>
                        </tr>
                    `;
                    return;
                }

                fees.forEach(fee => {

                    let statusBadge = '';

                    if (fee.status === 'PAID') {
                        statusBadge =
                            '<span class="badge bg-success">PAID</span>';
                    } else if (fee.status === 'PARTIAL') {
                        statusBadge =
                            '<span class="badge bg-warning text-dark">PARTIAL</span>';
                    } else {
                        statusBadge =
                            '<span class="badge bg-danger">UNPAID</span>';
                    }

                    tbody.innerHTML += `
                        <tr>
                            <td>${fee.academic_year}</td>
                            <td>${fee.class_name}</td>
                            <td>${fee.fee_name}</td>
                            <td>₹ ${fee.total_amount}</td>
                            <td>₹ ${fee.paid_amount}</td>
                            <td>₹ ${fee.due_amount}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <a href="{{ url('/student/previous-fees') }}/${fee.assignment_id}"
                                class="btn btn-sm btn-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    `;
                });

            })
            .fail(() => {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-danger text-center">
                            Failed to load previous fees
                        </td>
                    </tr>
                `;
            });
    }

});
</script>
@endpush