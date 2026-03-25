@extends('layouts.admin')

@section('title', 'Period Management')

@section('content')

<div class="form-head mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="text-black font-w700">Period Management</h2>
        <p class="text-muted mb-0">Manage School Timing Structure</p>
    </div>

    <button class="btn btn-primary" onclick="openPeriodModal()">
        + Add Period
    </button>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
                <tbody id="periodTableBody">
                    <tr>
                        <td colspan="6">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('admin.academic.periods.modal')

@endsection

@push('scripts')
<script>

let periods = [];

document.addEventListener("DOMContentLoaded", function () {
    loadPeriods();
});


// ================= LOAD PERIODS =================
function loadPeriods() {

    apiRequest("GET", "/periods")
        .done(function (data) {

            periods = data;
            const tbody = document.getElementById("periodTableBody");
            tbody.innerHTML = "";

            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="6">No Periods Found</td></tr>`;
                return;
            }

            data.forEach((period, index) => {

                tbody.insertAdjacentHTML("beforeend", `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${period.name}</td>
                        <td>${period.start_time} - ${period.end_time}</td>
                        <td>
                            ${period.is_break
                                ? '<span class="badge bg-warning">Break</span>'
                                : '<span class="badge bg-info">Class</span>'
                            }
                        </td>
                        <td>
                            ${period.is_active
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-secondary">Inactive</span>'
                            }
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="openPeriodModal(${period.id})">
                                Edit
                            </button>

                            <button class="btn btn-sm btn-outline-danger"
                                onclick="deletePeriod(${period.id})">
                                Delete
                            </button>
                        </td>
                    </tr>
                `);
            });
        });
}


// ================= OPEN MODAL =================
function openPeriodModal(id = null) {

    document.getElementById("periodForm").reset();
    document.getElementById("periodId").value = "";

    if (id) {
        const period = periods.find(p => p.id === id);

        document.getElementById("periodId").value = period.id;
        document.getElementById("periodName").value = period.name;
        document.getElementById("startTime").value = period.start_time;
        document.getElementById("endTime").value = period.end_time;
        document.getElementById("periodOrder").value = period.order;
        document.getElementById("isBreak").checked = period.is_break;
        document.getElementById("isActive").checked = period.is_active;
    }

    new bootstrap.Modal(document.getElementById("periodModal")).show();
}


// ================= SAVE PERIOD =================
function savePeriod() {

    const id = document.getElementById("periodId").value;

    const payload = {
        name: document.getElementById("periodName").value,
        start_time: document.getElementById("startTime").value,
        end_time: document.getElementById("endTime").value,
        order: document.getElementById("periodOrder").value,
        is_break: document.getElementById("isBreak").checked,
        is_active: document.getElementById("isActive").checked
    };

    if (id) {
        apiRequest("PUT", `/periods/${id}`, payload)
            .done(() => {
                bootstrap.Modal.getInstance(
                    document.getElementById("periodModal")
                ).hide();
                loadPeriods();
            });
    } else {
        apiRequest("POST", "/periods", payload)
            .done(() => {
                bootstrap.Modal.getInstance(
                    document.getElementById("periodModal")
                ).hide();
                loadPeriods();
            });
    }
}


// ================= DELETE =================
function deletePeriod(id) {

    if (!confirm("Are you sure you want to delete this period?")) return;

    apiRequest("DELETE", `/periods/${id}`)
        .done(() => loadPeriods());
}

</script>
@endpush