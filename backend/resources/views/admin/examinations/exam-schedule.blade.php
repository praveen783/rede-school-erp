@extends('layouts.admin')

@section('title', 'Exam Schedule')

@section('content')

<div class="card">
    <div class="card-header">
        <h4 id="displayExamName">--</h4>
        <small id="displayClassSection" class="text-muted"></small>
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Exam Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody id="subjectsTable">
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Loading subjects...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end gap-3 mt-4">

            <button id="saveScheduleBtn"
                    class="btn btn-primary px-4">
                Save Exam Schedule
            </button>

            <button id="publishBtn"
                    class="btn btn-success px-4"
                    disabled>
                Publish Admit Cards
            </button>

        </div>

        <div id="statusMsg" class="mt-3"></div>

    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener("DOMContentLoaded", function () {

    const publishBtn = document.getElementById("publishBtn");
    const saveBtn    = document.getElementById("saveScheduleBtn");
    const examNameEl = document.getElementById("displayExamName");
    const classSectionEl = document.getElementById("displayClassSection");
    const tableBody  = document.getElementById("subjectsTable");

    publishBtn.disabled = true;
    
    publishBtn.addEventListener("click", publishAdmitCards);

    // =========================
    // AUTH CHECK
    // =========================
    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }

    // =========================
    // LOAD ADMIN PROFILE
    // =========================
    apiRequest("GET", "/me").done(function (res) {

        const roleMap = {
            super_admin: "Super Admin",
            school_admin: "School Admin"
        };

        document.getElementById("headerUserName").innerText =
            res.name ?? "Admin";

        document.getElementById("headerUserRole").innerText =
            roleMap[res.role] ?? res.role;
    });

    // =========================
    // URL PARAMS
    // =========================
    const examId = @json($id);

    const params    = new URLSearchParams(window.location.search);
    const classId   = params.get("class_id");
    const sectionId = params.get("section_id");

    if (!examId || !classId || !sectionId) {
        alert("Invalid exam context");
        return;
    }

    loadExamDetails();
    loadScheduleData();

    // =========================
    // LOAD EXAM DETAILS
    // =========================
    function loadExamDetails() {

        apiRequest("GET", `/exams/${examId}`)
            .done(res => {

                examNameEl.innerText = res.name ?? "--";

                apiRequest("GET", "/class-sections")
                    .done(list => {

                        const match = list.find(item =>
                            String(item.class_id) === String(classId) &&
                            String(item.section_id) === String(sectionId)
                        );

                        if (match) {
                            classSectionEl.innerText =
                                `${match.class_name} - ${match.section_name}`;
                        } else {
                            classSectionEl.innerText =
                                `Class ${classId} - Section ${sectionId}`;
                        }
                    });
            });
    }

    // =========================
    // LOAD SCHEDULE + SUBJECTS
    // =========================
    function loadScheduleData() {

        apiRequest(
            "GET",
            `/exam-schedules?exam_id=${examId}&class_id=${classId}&section_id=${sectionId}`
        )
        .done(res => {

            const subjects = Array.isArray(res.data) ? res.data : [];

            renderSubjects(subjects);

            // Enable publish if at least one subject has date
            if (subjects.some(s => s.exam_date)) {
                publishBtn.disabled = false;
            }

        })
        .fail(() => {

            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-danger text-center">
                        Failed to load subjects
                    </td>
                </tr>`;
        });
    }

    // =========================
    // RENDER TABLE
    // =========================
    function renderSubjects(subjects) {

        tableBody.innerHTML = "";

        if (!subjects.length) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-muted text-center">
                        No subjects found
                    </td>
                </tr>`;
            return;
        }

        subjects.forEach(subject => {

            tableBody.insertAdjacentHTML("beforeend", `
                <tr data-subject-id="${subject.subject_id}">
                    <td>${subject.subject}</td>
                    <td>
                        <input type="date" class="form-control exam-date"
                               value="${subject.exam_date ?? ""}">
                    </td>
                    <td>
                        <input type="time" class="form-control start-time"
                               value="${subject.start_time ?? ""}">
                    </td>
                    <td>
                        <input type="time" class="form-control end-time"
                               value="${subject.end_time ?? ""}">
                    </td>
                </tr>
            `);
        });
    }

    // =========================
    // SAVE SCHEDULE
    // =========================
    saveBtn.addEventListener("click", function () {

        const schedules = [];

        document.querySelectorAll("#subjectsTable tr").forEach(row => {

            schedules.push({
                subject_id : row.dataset.subjectId,
                exam_date  : row.querySelector(".exam-date").value,
                start_time : row.querySelector(".start-time").value,
                end_time   : row.querySelector(".end-time").value
            });
        });

        apiRequest("POST", "/exam-schedules", {
            exam_id   : examId,
            class_id  : classId,
            section_id: sectionId,
            schedules : schedules
        })
        .done(res => {

            alert(res.message ?? "Exam schedule saved successfully");

            publishBtn.disabled = false;

            // reload to reflect saved data
            loadScheduleData();
        })
        .fail(err => {

            alert(err.responseJSON?.message ?? "Failed to save schedule");
        });
    });

    // =========================
    // PUBLISH ADMIT CARDS
    // =========================
    function publishAdmitCards() {

        apiRequest("PATCH", `/admin/exams/${examId}/admit-card/publish`, {
            class_id: classId,
            section_id: sectionId
        })
        .done(res => {

            document.getElementById("statusMsg").innerHTML =
                `<div class="alert alert-success">${res.message}</div>`;

            publishBtn.disabled = true;
        })
        .fail(err => {

            document.getElementById("statusMsg").innerHTML =
                `<div class="alert alert-danger">
                    ${err.responseJSON?.message ?? "Failed to publish"}
                </div>`;
        });
    }

});
</script>

@endpush