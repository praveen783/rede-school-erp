@extends('layouts.student')

@section('title', 'Pass / Fail Summary')

@section('content')

<div class="container-fluid">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1">Pass / Fail Summary</h4>
            <p class="text-muted mb-0">
                Overall exam result status
            </p>
        </div>
    </div>

    <!-- Exam Selection -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Exam</label>
                    <select class="form-select" id="examSelect">
                        <option value="">Select Exam</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" id="viewSummaryBtn" disabled>
                        View Summary
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4 d-none" id="summaryCards">
        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h6>Total Subjects</h6>
                    <h3 id="totalSubjects">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h6>Passed</h6>
                    <h3 class="text-success" id="passCount">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h6>Failed</h6>
                    <h3 class="text-danger" id="failCount">0</h3>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <h6>Pending</h6>
                    <h3 class="text-warning" id="pendingCount">0</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Result -->
    <div class="card d-none" id="overallResultCard">
        <div class="card-body text-center">
            <h5>Overall Result</h5>
            <span class="badge fs-5 px-4 py-2" id="overallResultBadge">
                --
            </span>
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
        window.location.href = "/login";
        return;
    }

    // Header Profile
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

    // Logout
    document.getElementById("logoutBtn").addEventListener("click", function () {
        localStorage.clear();
        window.location.href = "/login";
    });

    const examSelect     = document.getElementById("examSelect");
    const viewSummaryBtn = document.getElementById("viewSummaryBtn");

    const summaryCards      = document.getElementById("summaryCards");
    const overallResultCard = document.getElementById("overallResultCard");

    const totalSubjectsEl = document.getElementById("totalSubjects");
    const passCountEl     = document.getElementById("passCount");
    const failCountEl     = document.getElementById("failCount");
    const pendingCountEl  = document.getElementById("pendingCount");
    const overallBadge    = document.getElementById("overallResultBadge");

    // Load Exams
    apiRequest("GET", "/student/exams")
        .done(response => {

            const examsByYear = response.exams_by_academic_year;

            Object.keys(examsByYear).forEach(year => {

                const optGroup = document.createElement("optgroup");
                optGroup.label = year;

                examsByYear[year].forEach(exam => {
                    const option = document.createElement("option");
                    option.value = exam.exam_id;
                    option.textContent = exam.exam_name;
                    optGroup.appendChild(option);
                });

                examSelect.appendChild(optGroup);
            });
        });

    // Enable button only when exam selected
    examSelect.addEventListener("change", function () {
        viewSummaryBtn.disabled = !this.value;
        summaryCards.classList.add("d-none");
        overallResultCard.classList.add("d-none");
    });

    viewSummaryBtn.addEventListener("click", function () {

        const examId = examSelect.value;
        if (!examId) return;

        viewSummaryBtn.disabled = true;
        viewSummaryBtn.innerText = "Loading...";

        apiRequest("GET", "/student/exams/summary")
            .done(res => {

                const examSummary = res.exams.find(e => e.exam_id == examId);

                if (!examSummary) {
                    alert("Summary not available for selected exam");
                    return;
                }

                totalSubjectsEl.innerText = examSummary.total_subjects;
                passCountEl.innerText     = examSummary.passed;
                failCountEl.innerText     = examSummary.failed;
                pendingCountEl.innerText  = examSummary.pending;

                summaryCards.classList.remove("d-none");
                overallResultCard.classList.remove("d-none");

                if (examSummary.pending > 0) {
                    overallBadge.className = "badge bg-warning fs-5 px-4 py-2";
                    overallBadge.innerText = "Pending";
                } 
                else if (examSummary.failed > 0) {
                    overallBadge.className = "badge bg-danger fs-5 px-4 py-2";
                    overallBadge.innerText = "Fail";
                } 
                else {
                    overallBadge.className = "badge bg-success fs-5 px-4 py-2";
                    overallBadge.innerText = "Pass";
                }
            })
            .always(() => {
                viewSummaryBtn.disabled = false;
                viewSummaryBtn.innerText = "View Summary";
            });
    });

});
</script>
@endpush