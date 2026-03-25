@extends('layouts.admin')

@section('title', 'Academic Years')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Academic Years</h2>
    <p class="mb-0 text-muted">
        Manage school academic years
    </p>
</div>

<div class="card">
    <div class="card-body">

        <!-- Top Action Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Academic Year List</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAcademicYearModal">
                + Add Academic Year
            </button>
        </div>

        <!-- Academic Year Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody id="academicYearTableBody">
                    <!-- Dynamic Data -->
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Add Academic Year Modal -->
<div class="modal fade" id="addAcademicYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="academicYearForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add Academic Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Year Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>

                    <div id="formError" class="text-danger small"></div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }

    // Load logged-in user
    apiRequest("GET", "/me")
        .done(function (res) {

            const userName = res.name ?? "Admin";
            const userRole = res.role ?? "--";

            document.getElementById("headerUserName").innerText = userName;

            const roleMap = {
                super_admin: "Super Admin",
                school_admin: "School Admin"
            };

            document.getElementById("headerUserRole").innerText =
                roleMap[userRole] ?? userRole;

        })
        .fail(function () {
            console.error("Failed to load profile");
        });

    // Load Academic Years
    loadAcademicYears();

    function loadAcademicYears() {
        apiRequest("GET", "/academic-years")
            .done(function (years) {

                let rows = "";

                if (years.length === 0) {
                    rows = `
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                No academic years found
                            </td>
                        </tr>
                    `;
                } else {
                    years.forEach(function (year) {

                        const statusBadge = year.is_active
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Archived</span>';

                        let actionButton = '';

                        if (year.is_active) {
                            actionButton = `
                                <button class="btn btn-sm btn-danger"
                                    onclick="closeAcademicYear(${year.id})">
                                    Close
                                </button>
                            `;
                        } else {
                            actionButton = `
                                <button class="btn btn-sm btn-success"
                                    onclick="activateAcademicYear(${year.id})">
                                    Activate
                                </button>
                            `;
                        }

                        rows += `
                            <tr>
                                <td>${year.name}</td>
                                <td>${year.start_date}</td>
                                <td>${year.end_date}</td>
                                <td>${statusBadge}</td>
                                <td>${actionButton}</td>
                            </tr>
                        `;
                    });
                }

                document.getElementById("academicYearTableBody").innerHTML = rows;

            })
            .fail(function () {
                alert("Failed to load academic years");
            });
    }

    // Create Academic Year
    document.getElementById("academicYearForm")
        .addEventListener("submit", function (e) {

            e.preventDefault();

            const formData = {
                name: this.name.value,
                start_date: this.start_date.value,
                end_date: this.end_date.value
            };

            apiRequest("POST", `/schools/${user.school_id}/academic-years`, formData)
                .done(function () {

                    document.getElementById("academicYearForm").reset();
                    document.getElementById("formError").innerText = "";

                    bootstrap.Modal.getInstance(
                        document.getElementById('addAcademicYearModal')
                    ).hide();

                    loadAcademicYears();
                })
                .fail(function (xhr) {
                    const error =
                        xhr.responseJSON?.message ??
                        "Failed to create academic year";

                    document.getElementById("formError").innerText = error;
                });
        });

    window.activateAcademicYear = function (id) {

        if (!confirm("Activate this academic year? Current active year will be archived.")) {
            return;
        }

        apiRequest("PATCH", `/academic-years/${id}/activate`)
            .done(function () {
                loadAcademicYears();
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON?.message ?? "Failed to activate academic year");
            });
    };

    // Close Academic Year
    window.closeAcademicYear = function (id) {

        if (!confirm("Are you sure you want to close this academic year?")) {
            return;
        }

        apiRequest("PATCH", `/academic-years/${id}/close`)
            .done(function () {
                loadAcademicYears();
            })
            .fail(function (xhr) {
                alert(xhr.responseJSON?.message ??
                    "Failed to close academic year");
            });
    };

});
</script>

@endpush