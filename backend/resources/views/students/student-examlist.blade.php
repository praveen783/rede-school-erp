@extends('layouts.student')

@section('title', 'My Exams')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">My Exams</h2>
    <p class="text-muted mb-0">
        View all exams and their current status
    </p>
</div>

<div id="examListContainer">
    <!-- Exams will load here -->
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

    // Logout
    document.getElementById("logoutBtn")
        .addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "{{ url('/login') }}";
        });

    const examListContainer = document.getElementById("examListContainer");

    // ================================
    // LOAD STUDENT EXAM LIST
    // ================================
    apiRequest("GET", "/student/exams/list")
        .done(res => {

            const examsByYear = res.exams_by_academic_year || {};
            examListContainer.innerHTML = "";

            if (Object.keys(examsByYear).length === 0) {
                examListContainer.innerHTML = `
                    <div class="alert alert-info">
                        No exams available.
                    </div>
                `;
                return;
            }

            Object.keys(examsByYear).forEach(year => {

                let html = `
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">${year}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Exam</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                `;

                examsByYear[year].forEach(exam => {

                    let badgeClass = "bg-secondary";

                    if (exam.status === "Published") badgeClass = "bg-info";
                    if (exam.status === "Result Published") badgeClass = "bg-success";

                    html += `
                        <tr>
                            <td>${exam.exam_name}</td>
                            <td>${exam.start_date}</td>
                            <td>
                                <span class="badge ${badgeClass}">
                                    ${exam.status}
                                </span>
                            </td>
                            <td class="text-end">
                                ${
                                    exam.can_view_result
                                    ? `<button class="btn btn-sm btn-primary view-result-btn"
                                               data-exam-id="${exam.exam_id}">
                                           View Result
                                       </button>`
                                    : `<span class="text-muted">—</span>`
                                }
                            </td>
                        </tr>
                    `;
                });

                html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;

                examListContainer.insertAdjacentHTML("beforeend", html);
            });

            // ================================
            // VIEW RESULT REDIRECT
            // ================================
            document.querySelectorAll(".view-result-btn").forEach(btn => {
                btn.addEventListener("click", function () {
                    const examId = this.dataset.examId;

                    // Laravel route
                    window.location.href =
                        "{{ url('/student/component-wise') }}?exam_id=" + examId;
                });
            });

        })
        .fail(() => {
            examListContainer.innerHTML = `
                <div class="alert alert-danger">
                    Unable to load exams
                </div>
            `;
        });

});
</script>
@endpush