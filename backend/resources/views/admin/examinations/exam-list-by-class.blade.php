@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    <!-- PAGE HEADER -->
    <div class="form-head d-flex align-items-center mb-4">
        <div class="me-auto">
            <h2 class="text-black font-w700 mb-0">Exams</h2>
            <p class="mb-0 text-muted">
                Manage exams for the selected class & section
            </p>
        </div>
    </div>

    <!-- CLASS + SECTION INFO -->
    <div class="alert alert-info mb-4 d-flex align-items-center">
        <i class="fa fa-school me-2"></i>
        <strong>Class:</strong>
        <span id="displayClass" class="ms-1 me-3">--</span>

        <i class="fa fa-layer-group me-2"></i>
        <strong>Section:</strong>
        <span id="displaySection" class="ms-1">--</span>
    </div>

    <!-- EXAMS TABLE -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%">Exam Name</th>
                            <th style="width: 25%">Duration</th>
                            <th style="width: 25%">Status</th>
                            <th class="text-end" style="width: 20%">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="examTableBody">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Loading exams...
                            </td>
                        </tr>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

</div>

@endsection


@section('page-scripts')

@push('scripts')

<script>
document.addEventListener("DOMContentLoaded", function () {

    // =====================================
    // AUTH CHECK
    // =====================================
    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        alert("Session expired. Please login again.");
        window.location.href = "/login";
        return;
    }

    // =====================================
    // LOAD ADMIN PROFILE
    // =====================================
    apiRequest("GET", "/me")
        .done(res => {
            document.getElementById("headerUserName").innerText = res.name ?? "Admin";

            const roleMap = {
                school_admin: "School Admin",
                super_admin: "Super Admin"
            };

            document.getElementById("headerUserRole").innerText =
                roleMap[res.role] ?? res.role;
        });

    // =====================================
    // GET CLASS ID FROM BLADE ROUTE PARAM
    // =====================================
    const classId = @json($classId);

    // =====================================
    // GET SECTION ID FROM QUERY STRING
    // =====================================
    const params     = new URLSearchParams(window.location.search);
    const sectionId  = params.get("section_id");

    if (!classId || !sectionId) {
        alert("Invalid class or section.");
        window.location.href = "{{ url('/admin/examinations/exams') }}";
        return;
    }

    // =====================================
    // LOAD CLASS + SECTION INFO
    // =====================================
    loadClassSectionInfo();

    // =====================================
    // LOAD EXAMS
    // =====================================
    loadExams();

    function loadExams() {
        apiRequest(
            "GET",
            `/class-sections/${classId}/sections/${sectionId}/exams`
        )
        .done(res => {
            const exams = res.data ?? res;
            renderExamTable(exams);
        })
        .fail(showErrorRow);
    }

    // =====================================
    // RENDER TABLE
    // =====================================
    function renderExamTable(exams) {
        const tbody = document.getElementById("examTableBody");
        tbody.innerHTML = "";

        if (!exams || exams.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        No exams found for this class & section
                    </td>
                </tr>`;
            return;
        }

        exams.forEach(exam => {

            const statusBadge = exam.status === "published"
                ? `<span class="badge bg-success">Published</span>`
                : `<span class="badge bg-secondary">Draft</span>`;

            const row = `
                <tr>
                    <td><strong>${exam.name}</strong></td>

                    <td class="text-muted">
                        ${exam.start_date ?? "-"} → ${exam.end_date ?? "-"}
                    </td>

                    <td>${statusBadge}</td>

                    <td class="text-center">

                        <!-- Assign Subjects -->
                        <a href="/admin/examinations/exams/${exam.id}/subjects?class_id=${classId}&section_id=${sectionId}"
                           class="btn btn-sm btn-info me-2">
                            Assign Subjects
                        </a>

                        <!-- Edit Exam -->
                        <a href="/admin/examinations/exams/${exam.id}/edit"
                           class="btn btn-sm btn-warning">
                            Edit
                        </a>

                    </td>
                </tr>
            `;

            tbody.insertAdjacentHTML("beforeend", row);
        });
    }

    // =====================================
    // LOAD CLASS + SECTION NAME
    // =====================================
    function loadClassSectionInfo() {

        apiRequest("GET", "/class-sections")
            .done(res => {

                if (!Array.isArray(res)) {
                    setFallback();
                    return;
                }

                const match = res.find(item =>
                    Number(item.class_id) === Number(classId) &&
                    Number(item.section_id) === Number(sectionId)
                );

                if (match) {
                    document.getElementById("displayClass").innerText =
                        match.class_name;

                    document.getElementById("displaySection").innerText =
                        match.section_name;
                } else {
                    setFallback();
                }

            })
            .fail(setFallback);

        function setFallback() {
            document.getElementById("displayClass").innerText =
                `Class ${classId}`;

            document.getElementById("displaySection").innerText =
                `Section ${sectionId}`;
        }
    }

    // =====================================
    // ERROR HANDLER
    // =====================================
    function showErrorRow() {
        document.getElementById("examTableBody").innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger">
                    Failed to load exams. Please try again.
                </td>
            </tr>`;
    }

});
</script>
   
@endpush


