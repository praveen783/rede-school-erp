@extends('layouts.admin')

@section('title','Result Sheet')

@section('content')

<!-- <div class="content-body">
<div class="container-fluid"> -->

<div class="form-head mb-4">
<h2 class="text-black font-w700">Exam Result Sheet</h2>
</div>

<div class="card">
<div class="card-body">


<div class="table-responsive">

<table class="table table-bordered table-striped">

<thead id="resultHead"></thead>

<tbody id="resultBody">
<tr>
<td colspan="10" class="text-center text-muted">
Loading results...
</td>
</tr>
</tbody>

</table>

</div>

</div>
</div>

<!-- </div>
</div> -->

@endsection


@push('scripts')
<script>

document.addEventListener("DOMContentLoaded", function(){

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    const examId = {{ $id }};

    console.log("Exam ID from blade:", examId);

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

    loadResults(examId);

});


function loadResults(examId)
{

    console.log("API URL:", `/admin/results/${examId}`);

    apiRequest("GET", `/admin/results/${examId}`)
    .done(function(data){

        const head = document.getElementById("resultHead");
        const body = document.getElementById("resultBody");

        // ======================
        // BUILD TABLE HEADER
        // ======================

        let headerRow = `
        <tr>
            <th>Rank</th>
            <th>Admission No</th>
            <th>Student</th>
        `;

        data.subjects.forEach(subject=>{
            headerRow += `<th>${subject.subject_name}</th>`;
        });

        headerRow += `
            <th>Total</th>
            <th>Result</th>
        </tr>
        `;

        head.innerHTML = headerRow;

        // ======================
        // BUILD TABLE BODY
        // ======================

        body.innerHTML = "";

        data.students.forEach(student=>{

            let row = `
            <tr>
                <td>${student.rank}</td>
                <td>${student.admission_no ?? "-"}</td>
                <td>${student.name}</td>
            `;

            data.subjects.forEach(subject=>{

                const mark = student.marks[subject.subject_id] ?? "-";

                row += `<td>${mark}</td>`;

            });

            row += `
                <td>${student.total}</td>

                <td>
                    ${
                        student.result === "Pass"
                        ? '<span class="badge bg-success">Pass</span>'
                        : '<span class="badge bg-danger">Fail</span>'
                    }
                </td>

            </tr>
            `;

            body.innerHTML += row;

        });

    })

    .fail(function(err){

        console.error("Result API failed:", err);

        document.getElementById("resultBody").innerHTML = `
        <tr>
            <td colspan="10" class="text-center text-danger">
                Failed to load results
            </td>
        </tr>
        `;

    });

}

</script>
@endpush