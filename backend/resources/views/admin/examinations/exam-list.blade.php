@extends('layouts.admin')

@section('content')

<!-- PAGE HEADER -->
<div class="form-head d-flex align-items-center mb-4">
    <div class="me-auto">
        <h2 class="text-black font-w700 mb-0">Examinations</h2>
        <p class="mb-0 text-muted">
            Select a class to manage exams
        </p>
    </div>

    <button class="btn btn-primary" id="btnCreateExam">
        <i class="fa fa-plus me-1"></i> Create Exam
    </button>
</div>

<!-- CLASS LIST TABLE -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Class</th>
                        <th>Section</th>
                        <th class="text-end">Exams</th>
                    </tr>
                </thead>

                <tbody id="classTableBody">
                    <!-- Loaded dynamically -->
                </tbody>

            </table>
        </div>
    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ================================
    // AUTH CHECK
    // ================================
    const token = localStorage.getItem("auth_token");

    if (!token) {
        alert("Session expired. Please login again.");
        window.location.href = "{{ url('/login') }}";
        return;
    }

    // ================================
    // LOAD ADMIN DETAILS (/me)
    // ================================
    apiRequest("GET", "/me")
        .done(function (res) {

            document.getElementById("headerUserName").innerText =
                res.name ?? "Admin";

            const roleMap = {
                super_admin: "Super Admin",
                school_admin: "School Admin"
            };

            document.getElementById("headerUserRole").innerText =
                roleMap[res.role] ?? res.role;

            console.log("Logged-in user loaded:", res);
        })
        .fail(function () {
            console.error("Failed to load user profile");
        });

    // ================================
    // LOAD CLASS + SECTION LIST
    // ================================
    loadClassSections();

    function loadClassSections() {
        apiRequest("GET", "/class-sections")
            .done(function (res) {
                console.log("Class-section API response:", res);
                renderTable(res);
            })
            .fail(function () {
                showErrorRow();
            });
    }

    // ================================
    // RENDER TABLE
    // ================================
    function renderTable(items) {
        const tbody = document.getElementById("classTableBody");
        tbody.innerHTML = "";

        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No classes found
                    </td>
                </tr>
            `;
            return;
        }

        items.forEach(row => {
            const tr = `
                <tr>
                    <td><strong>${row.class_name}</strong></td>
                    <td>${row.section_name}</td>
                    <td class="text-end">
                        <a
                            href="/admin/examinations/class/${row.class_id}/exams?section_id=${row.section_id}"
                            class="btn btn-sm btn-primary"
                        >
                        View Exams
                        </a>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML("beforeend", tr);
        });
    }

    // ================================
    // ERROR STATE
    // ================================
    function showErrorRow() {
        document.getElementById("classTableBody").innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-danger">
                    Failed to load class sections.
                </td>
            </tr>
        `;
    }
    const createBtn = document.getElementById("btnCreateExam");

    if (createBtn) {
        createBtn.addEventListener("click", function () {
            window.location.href = "{{ url('/admin/examinations/exams/create') }}";
        });
    }

});
</script>

@endpush


   



