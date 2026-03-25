@extends('layouts.student')

@section('title', 'Component-wise Result')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        Component-wise Result
    </h2>
    <p class="text-muted mb-0">
        View subject-wise marks for selected examination
    </p>
</div>

<!-- Exam Selection -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Academic Year</label>
                <select class="form-select" id="academicYearSelect">
                    <option value="">Select Academic Year</option>
                </select>
            </div>

            <div class="col-md-5">
                <label class="form-label">Exam</label>
                <select class="form-select" id="examSelect">
                    <option value="">Select Exam</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" id="loadResultBtn">
                    View Result
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Result Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Result Details</h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Subject</th>
                        <th class="text-center">Marks Obtained</th>
                        <th class="text-center">Max Marks</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="resultTableBody">
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Select an exam to view results
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

			const userName = res.name ?? "Admin";
			const userRole = res.role ?? "--";

			document.getElementById("headerUserName").innerText = userName;

			const roleMap = {
				
				student: "Student",
			};

			document.getElementById("headerUserRole").innerText =
				roleMap[userRole] ?? userRole;

				console.log("Logged-in user loaded:", res);
			})

			.fail(function (err) {
				console.error("Failed to load user profile", err);
			});

    const loadBtn    = document.getElementById("loadResultBtn");
    const examSelect = document.getElementById("examSelect");
    const tableBody  = document.getElementById("resultTableBody");

    // ================= LOAD ACADEMIC YEARS =================
    apiRequest("GET", "/academic-years")
        .done(res => {
            const select = document.getElementById("academicYearSelect");
            res.forEach(year => {
                select.innerHTML += `
                    <option value="${year.id}">
                        ${year.name}
                    </option>
                `;
            });
        });

    // ================= LOAD EXAMS =================
    document.getElementById("academicYearSelect")
        .addEventListener("change", function () {

        const yearId = this.value;
        examSelect.innerHTML = `<option value="">Select Exam</option>`;

        if (!yearId) return;

        apiRequest("GET", `/student/academic-years/${yearId}/exams`)
            .done(res => {
                res.exams.forEach(exam => {
                    examSelect.innerHTML += `
                        <option value="${exam.id}">
                            ${exam.name}
                        </option>
                    `;
                });
            });
    });

    // ================= LOAD RESULTS =================
    loadBtn.addEventListener("click", function () {

        const examId = examSelect.value;

        if (!examId) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger py-4">
                        Please select an exam
                    </td>
                </tr>
            `;
            return;
        }

        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    Loading results...
                </td>
            </tr>
        `;

        apiRequest("GET", `/student/exams/${examId}/results`)
            .done(res => {

                if (!res.results || res.results.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No results available
                            </td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = "";

                res.results.forEach((row, index) => {

                    let statusBadge = "";
                    if (row.status === "Pass") {
                        statusBadge = `<span class="badge bg-success">Pass</span>`;
                    } else if (row.status === "Fail") {
                        statusBadge = `<span class="badge bg-danger">Fail</span>`;
                    } else {
                        statusBadge = `<span class="badge bg-warning text-dark">Absent</span>`;
                    }

                    tableBody.innerHTML += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${row.subject}</td>
                            <td class="text-center fw-semibold">
                                ${row.marks_obtained !== null ? row.marks_obtained : "-"}
                            </td>
                            <td class="text-center text-muted">
                                ${row.max_marks}
                            </td>
                            <td class="text-center">
                                ${statusBadge}
                            </td>
                        </tr>
                    `;
                });

            })
            .fail(() => {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center text-danger py-4">
                            Failed to load results
                        </td>
                    </tr>
                `;
            });
    });

    // Logout
    document.getElementById("logoutBtn")
        .addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "{{ url('/login') }}";
        });

});
</script>
@endpush