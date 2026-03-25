@extends('layouts.admin')

@section('title', 'Exam Results')

@section('content')

<!-- <div class="content-body">
    <div class="container-fluid"> -->

        <!-- Page Header -->
        <div class="form-head mb-4">
            <h2 class="text-black font-w700 mb-1">Exam Results</h2>
            <p class="mb-0 text-muted">
                View, verify and publish exam results
            </p>
        </div>

        <!-- Filter Card -->
        <div class="card mb-4">
            <div class="card-body">

                <div class="row">

                    <div class="col-md-4">
                        <label class="form-label">Class</label>
                        <select id="classSelect" class="form-control">
                            <option value="">Select Class</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Section</label>
                        <select id="sectionSelect" class="form-control">
                            <option value="">Select Section</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button id="loadResultsBtn" class="btn btn-primary">
                            Load Results
                        </button>
                    </div>

                </div>

            </div>
        </div>

        <!-- Results Table -->
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Duration</th>
                                <th>Marks Status</th>
                                <th>Result Status</th>
                                <th width="220">Actions</th>
                            </tr>
                        </thead>

                        <tbody id="resultsTable">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Select class and section to view results
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
    // DOM ELEMENTS
    // =========================
    const classSelect   = document.getElementById("classSelect");
    const sectionSelect = document.getElementById("sectionSelect");
    const resultsTable  = document.getElementById("resultsTable");


    // =========================
    // LOAD CLASSES
    // =========================
    apiRequest("GET", "/classes").done(function(res){

        classSelect.innerHTML = `<option value="">Select Class</option>`;

        res.data.forEach(cls => {
            classSelect.innerHTML +=
                `<option value="${cls.id}">${cls.name}</option>`;
        });

    });


    // =========================
    // LOAD SECTIONS WHEN CLASS CHANGES
    // =========================
    classSelect.addEventListener("change", function(){

        const classId = this.value;

        sectionSelect.innerHTML =
            `<option value="">Select Section</option>`;

        if(!classId) return;

        apiRequest("GET", `/sections?class_id=${classId}`)
        .done(function(res){

            res.forEach(section => {

                sectionSelect.innerHTML += `
                    <option value="${section.id}">
                        ${section.name}
                    </option>
                `;

            });

        });

    });

    // =========================
    // LOAD RESULTS
    // =========================
    document.getElementById("loadResultsBtn")
    .addEventListener("click", loadResults);


    function loadResults(){

        const classId   = classSelect.value;
        const sectionId = sectionSelect.value;

        if(!classId || !sectionId){
            alert("Please select class and section");
            return;
        }

        resultsTable.innerHTML =
            `<tr>
                <td colspan="5" class="text-center">
                    Loading...
                </td>
            </tr>`;

        apiRequest("GET",
            `/admin/results/${classId}/${sectionId}`
        )
        .done(function(exams){

            if(!exams.length){
                resultsTable.innerHTML =
                    `<tr>
                        <td colspan="5" class="text-center text-muted">
                            No exams found
                        </td>
                     </tr>`;
                return;
            }

            resultsTable.innerHTML = "";

            exams.forEach(exam => {

                let marksBadge =
                    exam.marks_status === "Completed"
                    ? `<span class="badge bg-success">
                        Completed
                       </span>`
                    : `<span class="badge bg-warning">
                        Pending
                       </span>`;

                let resultBadge =
                    exam.is_result_published
                    ? `<span class="badge bg-primary">
                        Published
                       </span>`
                    : `<span class="badge bg-secondary">
                        Not Published
                       </span>`;

                let publishBtn = "";

                if(
                    exam.marks_status === "Completed" &&
                    !exam.is_result_published
                ){
                    publishBtn =
                        `<button class="btn btn-success btn-sm ms-1"
                         onclick="publishResults(${exam.exam_id})">
                         Publish
                         </button>`;
                }

                resultsTable.innerHTML += `
                    <tr>
                        <td>${exam.exam_name}</td>
                        <td>${exam.start_date} → ${exam.end_date}</td>
                        <td>${marksBadge}</td>
                        <td>${resultBadge}</td>
                        <td>
                            <a href="/admin/examinations/exams/${exam.exam_id}/results"
                                class="btn btn-info btn-sm">
                                View Results
                            </a>
                            ${publishBtn}

                        </td>
                    </tr>
                `;
            });

        });

    }


});


// =========================
// PUBLISH RESULTS
// =========================
function publishResults(examId){

    if(!confirm("Publish results for this exam?")) return;

    apiRequest("POST",
        `/admin/exams/${examId}/publish-results`
    )
    .done(function(res){

        alert(res.message ?? "Results published");

        location.reload();
    });
}

</script>
@endpush