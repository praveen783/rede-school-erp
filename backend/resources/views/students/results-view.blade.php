@extends('layouts.student')

@section('title', 'Result Sheet')

@section('content')

    <div class="form-head mb-4">
        <h2 class="text-black font-w700">
            Exam Result
        </h2>
        <div class="row mt-3 mb-3">

            <div class="col-md-4">
                <strong>Class</strong>
                <p id="className">-</p>
            </div>

            <div class="col-md-4">
                <strong>Section</strong>
                <p id="sectionName">-</p>
            </div>

            <div class="col-md-4">
                <strong>Exam</strong>
                <p id="examName">-</p>
            </div>

        </div>
    </div>

    <div class="card">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>
                            <th>Subject</th>
                            <th>Marks Obtained</th>
                            <th>Max Marks</th>
                            <th>Pass Marks</th>
                        </tr>

                    </thead>

                    <tbody id="resultBody">

                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Loading result...
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>


            <hr>


            <div class="row mt-4">

                <div class="col-md-3">
                    <strong>Total Marks</strong>
                    <p id="totalMarks">-</p>
                </div>

                <div class="col-md-3">
                    <strong>Maximum Marks</strong>
                    <p id="maxMarks">-</p>
                </div>

                <div class="col-md-3">
                    <strong>Percentage</strong>
                    <p id="percentage">-</p>
                </div>

                <div class="col-md-3">
                    <strong>Rank</strong>
                    <p id="rank">-</p>
                </div>

            </div>


            <div class="row mt-3">

                <div class="col-md-6">
                    <strong>Result Status</strong>
                    <p id="resultStatus">-</p>
                </div>

                <div class="col-md-6 text-end">

                    <button id="downloadMarksheetBtn" class="btn btn-primary">

                        Download Marksheet

                    </button>

                </div>

            </div>


        </div>
    </div>

@endsection





    <!-- <script>

        document.addEventListener("DOMContentLoaded", function () {

            const token = localStorage.getItem("auth_token");
            const user = JSON.parse(localStorage.getItem("user"));

            if (!token || !user || user.role !== "student") {
                localStorage.clear();
                window.location.href = "/login";
                return;
            }


            const examId = {{ $id }};


            /* =========================
            LOAD HEADER PROFILE
            ========================= */

            apiRequest("GET", "/me")
                .done(function (res) {

                    document.getElementById("headerUserName").innerText =
                        res.name ?? "Student";

                    document.getElementById("headerUserRole").innerText =
                        "Student";

                });



            /* =========================
            LOAD RESULT DATA
            ========================= */

            loadResult(examId);


        });
        /* =========================
       MARKSHEET DOWNLOAD
       ========================= */

        document.getElementById("downloadMarksheetBtn")
            .addEventListener("click", function () {

                const examId = {{ $id }};

                window.open(
                    `/student/results/${examId}/marksheet?token=${token}`
                );

            });



        function loadResult(examId) {

            apiRequest("GET", `/student/results/${examId}`)

                .done(function (data) {

                    const tbody = document.getElementById("resultBody");
                    document.getElementById("className").innerText =
                        data.class_name;

                    document.getElementById("sectionName").innerText =
                        data.section_name;

                    document.getElementById("examName").innerText =
                        data.exam;

                    tbody.innerHTML = "";


                    /* =========================
                    SUBJECT MARKS
                    ========================= */

                    data.subjects.forEach(subject => {

                        tbody.innerHTML += `

                <tr>

                <td>${subject.subject}</td>

                <td>
                ${subject.marks_obtained ?? "-"}
                </td>

                <td>
                ${subject.max_marks}
                </td>

                <td>
                ${subject.pass_marks}
                </td>

                </tr>

            `;

                    });


                    /* =========================
                    SUMMARY DATA
                    ========================= */

                    document.getElementById("totalMarks").innerText =
                        data.total_marks;

                    document.getElementById("maxMarks").innerText =
                        data.max_total;

                    document.getElementById("percentage").innerText =
                        data.percentage + " %";

                    document.getElementById("rank").innerText =
                        data.rank ?? "-";


                    /* =========================
                    RESULT STATUS
                    ========================= */

                    const status = data.result;

                    const badge =
                        status === "Pass"
                            ? '<span class="badge bg-success">Pass</span>'
                            : '<span class="badge bg-danger">Fail</span>';

                    document.getElementById("resultStatus").innerHTML =
                        badge;

                })

                .fail(function () {

                    document.getElementById("resultBody").innerHTML = `
        <tr>
        <td colspan="4" class="text-center text-danger">
        Failed to load result
        </td>
        </tr>
        `;
    });

}

</script> -->

<!-- @push('scripts') -->
@push('scripts')
<script>

document.addEventListener("DOMContentLoaded", function(){

    /* =========================
    AUTH DATA
    ========================= */

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if(!token || !user || user.role !== "student")
    {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    const examId = {{ $id }};

    /* =========================
    LOAD HEADER PROFILE
    ========================= */

    apiRequest("GET","/me")
    .done(function(res){

        document.getElementById("headerUserName").innerText =
        res.name ?? "Student";

        document.getElementById("headerUserRole").innerText =
        "Student";

    });


    /* =========================
    LOAD RESULT DATA
    ========================= */

    loadResult(examId);


    /* =========================
    MARKSHEET DOWNLOAD
    ========================= */

    const downloadBtn = document.getElementById("downloadMarksheetBtn");

    if(downloadBtn){
        downloadBtn.addEventListener("click",function(){

            window.open(
            `/student/results/${examId}/marksheet?token=${token}`
            );

        });
    }

});


/* =========================
LOAD RESULT FUNCTION
========================= */

function loadResult(examId)
{

    apiRequest("GET",`/student/results/${examId}`)

    .done(function(data){

        const tbody = document.getElementById("resultBody");

        document.getElementById("className").innerText =
        data.class_name;

        document.getElementById("sectionName").innerText =
        data.section_name;

        document.getElementById("examName").innerText =
        data.exam;

        tbody.innerHTML = "";


        /* SUBJECT MARKS */

        data.subjects.forEach(subject => {

            tbody.innerHTML += `
            <tr>
                <td>${subject.subject}</td>
                <td>${subject.marks ?? "-"}</td>
                <td>${subject.max_marks}</td>
                <td>${subject.pass_marks}</td>
            </tr>
            `;

        });


        /* SUMMARY */

        document.getElementById("totalMarks").innerText =
        data.total_marks;

        document.getElementById("maxMarks").innerText =
        data.max_total;

        document.getElementById("percentage").innerText =
        data.percentage + " %";

        document.getElementById("rank").innerText =
        data.rank ?? "-";


        /* RESULT STATUS */

        const badge =
        data.result === "Pass"
        ? '<span class="badge bg-success">Pass</span>'
        : '<span class="badge bg-danger">Fail</span>';

        document.getElementById("resultStatus").innerHTML =
        badge;

    })

    .fail(function(){

        document.getElementById("resultBody").innerHTML = `
        <tr>
            <td colspan="4" class="text-center text-danger">
            Failed to load result
            </td>
        </tr>
        `;

    });

}

</script>

@endpush
