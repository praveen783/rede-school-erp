@extends('layouts.admin')

@section('title', 'Attendance Monitor')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Attendance Monitor</h2>
    <p class="mb-0 text-muted">
        Monitor daily class-wise attendance status
    </p>
</div>

<div class="card">
    <div class="card-body">

        <!-- Date Filter -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Select Date</label>
                <input type="date" id="monitorDate" class="form-control">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button id="loadMonitorBtn" class="btn btn-primary w-100">
                    Load Status
                </button>
            </div>
        </div>

        <!-- Monitor Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Marked By</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
                <tbody id="monitorTableBody">
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            Select a date to view attendance status
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

    const dateInput = document.getElementById("monitorDate");
    const tableBody = document.getElementById("monitorTableBody");

    // Auto set today
    dateInput.value = new Date().toISOString().split('T')[0];

    document.getElementById("loadMonitorBtn")
        .addEventListener("click", function () {

        const date = dateInput.value;

        if (!date) {
            alert("Please select a date");
            return;
        }

        if (new Date(date) > new Date()) {
            alert("Future monitoring not allowed");
            return;
        }

        apiRequest("GET",
            `/admin/attendance-monitor?attendance_date=${date}`
        )
        .done(function (data) {

            tableBody.innerHTML = "";

            if (!data.length) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No data available
                        </td>
                    </tr>
                `;
                return;
            }

            data.forEach(row => {

                let badge = '';
                let roleText = row.marked_role ?? '-';

                if (row.status === "marked") {
                    badge = `<span class="badge bg-success">Marked</span>`;
                } else {
                    badge = `<span class="badge bg-danger">Not Marked</span>`;
                }

                const actionBtn = `
                    <a href="/admin/academic/attendance/manage?class_id=${row.class_id}&section_id=${row.section_id}&attendance_date=${date}"
                    class="btn btn-sm btn-outline-primary">
                    ${row.status === "marked" ? "View" : "Mark"}
                    </a>
                `;

                const tr = `
                    <tr>
                        <td>${row.class_name}</td>
                        <td>${row.section_name}</td>
                        <td>${badge}</td>
                        <td>${roleText ?? '-'}</td>
                        <td>${actionBtn}</td>
                    </tr>
                `;

                tableBody.insertAdjacentHTML("beforeend", tr);
            });

        })
        .fail(function (err) {
            alert(err.responseJSON?.message ?? "Failed to load data");
        });
    });

});
</script>
@endpush