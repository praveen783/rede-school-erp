@extends('layouts.admin')

@section('title', 'Syllabus Management')

@section('content')

<div class="form-head mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="text-black font-w700 mb-1">Syllabus Management</h2>
        <p class="mb-0 text-muted">Manage class-wise syllabus</p>
    </div>

    <a href="{{ url('admin/academic/syllabus/create') }}" 
       class="btn btn-primary">
        + Create Syllabus
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">

        <!-- Class Filter -->
        <div class="row mb-4">
            <div class="col-md-4">
                <label class="form-label">Filter by Class</label>
                <select id="classFilter" class="form-control">
                    <option value="">All Classes</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Board</th>
                        <th>Units</th>
                        <th>Resources</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody id="syllabusTableBody">
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            Loading syllabus...
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

    loadClasses();
    loadSyllabus();

    // ============================
    // LOAD CLASSES (From Class Sections)
    // ============================
    function loadClasses() {

        apiRequest("GET", "/class-sections")
            .done(function (data) {

                const grouped = {};
                const select = document.getElementById("classFilter");

                data.forEach(item => {
                    if (!grouped[item.class_id]) {
                        grouped[item.class_id] = item.class_name;
                    }
                });

                select.innerHTML =
                    '<option value="">All Classes</option>';

                Object.keys(grouped).forEach(id => {
                    select.insertAdjacentHTML("beforeend", `
                        <option value="${id}">
                            ${grouped[id]}
                        </option>
                    `);
                });
            });
    }

    function loadSyllabus(classId = '') {

        let url = "/syllabus";
        if (classId) {
            url += `?class_id=${classId}`;
        }

        apiRequest("GET", url)
            .done(function (response) {

                const data = response.data;
                const tbody = document.getElementById("syllabusTableBody");
                tbody.innerHTML = "";

                if (!data || data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No syllabus found
                            </td>
                        </tr>
                    `;
                    return;
                }

                data.forEach(item => {

                    const row = `
                        <tr>
                            <td>${item.class?.name ?? '-'}</td>
                            <td>${item.subject?.name ?? '-'}</td>
                            <td>${item.board?.name ?? '-'}</td>
                            <td>${item.units_count}</td>
                            <td>${item.resources_count}</td>
                            <td>
                                <a href="{{ url('admin/academic/syllabus') }}/${item.id}/manage"
                                   class="btn btn-sm btn-primary">
                                   Manage
                                </a>
                            </td>
                        </tr>
                    `;

                    tbody.insertAdjacentHTML("beforeend", row);
                });
            });
    }

    document.getElementById("classFilter")
        .addEventListener("change", function () {
            loadSyllabus(this.value);
        });

});
</script>
@endpush