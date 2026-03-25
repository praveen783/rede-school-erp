@extends('layouts.student')

@section('title', 'Hall Admit Card')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        My Admit Cards
    </h2>
    <p class="text-muted mb-0">
        Download your hall tickets for upcoming exams
    </p>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered" id="admitCardTable">
                <thead class="table-light">
                    <tr>
                        <th>Exam Name</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody id="admitCardTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">
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

    // ===============================
    // LOAD HEADER PROFILE
    // ===============================
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

    // ===============================
    // LOGOUT
    // ===============================
    document.getElementById("logoutBtn")
        .addEventListener("click", function () {
            localStorage.clear();
            window.location.href = "{{ url('/login') }}";
        });

    // ===============================
    // LOAD STUDENT ADMIT CARDS
    // ===============================
    loadStudentAdmitCards();

    function loadStudentAdmitCards() {

        const tbody = document.getElementById("admitCardTableBody");

        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-muted">
                    Loading...
                </td>
            </tr>
        `;

        apiRequest("GET", "/student/admit-cards")
            .done(res => {

                const cards = res.admit_cards || [];
                tbody.innerHTML = "";

                if (cards.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No admit cards available
                            </td>
                        </tr>
                    `;
                    return;
                }

                cards.forEach(card => {

                    const statusBadge = `
                        <span class="badge bg-success">
                            ${card.status}
                        </span>
                    `;

                    const actionBtn = card.can_download
                        ? `
                            <button class="btn btn-sm btn-primary"
                                onclick="downloadAdmitCard(${card.exam_id})">
                                Download
                            </button>
                          `
                        : `
                            <button class="btn btn-sm btn-secondary" disabled>
                                Not Released
                            </button>
                          `;

                    tbody.innerHTML += `
                        <tr>
                            <td>
                                <strong>${card.exam_name}</strong><br>
                                <small class="text-muted">
                                    Hall Ticket: ${card.hall_ticket_no}
                                </small>
                            </td>
                            <td>${card.duration}</td>
                            <td>${statusBadge}</td>
                            <td>${actionBtn}</td>
                        </tr>
                    `;
                });
            })
            .fail(() => {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-danger text-center">
                            Failed to load admit cards
                        </td>
                    </tr>
                `;
            });
    }

    // ===============================
    // DOWNLOAD ADMIT CARD PDF
    // ===============================
    window.downloadAdmitCard = function (examId) {

        const token = localStorage.getItem("auth_token");

        fetch(`${API_BASE_URL}/student/exams/${examId}/admit-card/pdf`, {
            headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/pdf"
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error("Admit card not available");
            }
            return response.blob();
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `AdmitCard_${examId}.pdf`;
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        })
        .catch(err => {
            alert(err.message);
        });
    };

});
</script>
@endpush