@extends('layouts.admin')

@section('title','Student Profile')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Student Profile</h2>
    <p class="mb-0 text-muted">
        Complete academic and personal information of the student
    </p>
</div>


<div class="row">

    <!-- Student Basic Information -->
    <div class="col-md-3">

        <div class="card">
            <div class="card-header">
                <h4>Student Details</h4>
            </div>

            <div class="card-body">

                <h4 id="studentName">--</h4>

                <p><strong>Admission No :</strong> <span id="admissionNo"></span></p>

                <p><strong>Father Name :</strong> <span id="parentName"></span></p>

                <p><strong>Gender :</strong> <span id="gender"></span></p>

                <p><strong>Category :</strong> <span id="category"></span></p>

                <p><strong>Date of Birth :</strong> <span id="dob"></span></p>

                <p><strong>Date of Joining :</strong> <span id="joining"></span></p>

            </div>
        </div>

    </div>


    <!-- Attendance Card -->
    <div class="col-md-3">

        <div class="card">
            <div class="card-header">
                <h4>Attendance</h4>
            </div>

            <div class="card-body text-center">

                <h5>Attendance %</h5>

                <h2 id="attendancePercent">0%</h2>

            </div>
        </div>

    </div>


    <!-- Total Marks -->
    <div class="col-md-3">

        <div class="card">
            <div class="card-header">
                <h4>Total Marks</h4>
            </div>

            <div class="card-body text-center">

                <h2 id="totalMarks">0</h2>

            </div>
        </div>

    </div>


    <!-- Rank Card -->
    <div class="col-md-3">

        <div class="card">
            <div class="card-header">
                <h4>Class Rank</h4>
            </div>

            <div class="card-body text-center">

                <h2 id="studentRank">--</h2>

                <p>Out of <span id="classSize">0</span> students</p>

            </div>
        </div>

    </div>

</div>



<!-- Marks Trend Chart -->

<div class="card mt-4">

    <div class="card-header">
        <h4>Marks Trend</h4>
    </div>

    <div class="card-body">

        <canvas id="marksChart"></canvas>

    </div>

</div>



<!-- Attendance Trend Chart -->

<div class="card mt-4">

    <div class="card-header">
        <h4>Attendance Trend</h4>
    </div>

    <div class="card-body">

        <div style="height:300px; max-width:700px; margin:auto;">
            <canvas id="attendanceChart"></canvas>
        </div>

    </div>

</div>



<!-- Exam Performance Table -->

<div class="card mt-4">

    <div class="card-header">
        <h4>Exam Performance (Subject Wise)</h4>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Subject</th>
                        <th>Marks</th>
                    </tr>
                </thead>

                <tbody id="marksTableBody">
                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection



@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }


    /* Load logged-in user */

    apiRequest("GET", "/me")
        .done(function (res) {

            document.getElementById("headerUserName").innerText =
                res.name ?? "Admin";

            document.getElementById("headerUserRole").innerText =
                res.role ?? "--";

        });


    const params = new URLSearchParams(window.location.search);
    const studentId = params.get("id");


    apiRequest("GET", "/admin/student-details", {
        student_id: studentId
    })
    .done(function(res){

        const s = res.student;

        /* Basic Details */

        document.getElementById("studentName").innerText = s.name;
        document.getElementById("admissionNo").innerText = s.admission_no ?? "-";
        document.getElementById("parentName").innerText = s.parent_name ?? "-";
        document.getElementById("gender").innerText = s.gender ?? "-";
        document.getElementById("category").innerText = s.category?.name ?? "-";
        document.getElementById("dob").innerText = s.dob ?? "-";
        document.getElementById("joining").innerText = s.date_of_joining ?? "-";


        /* Attendance */

        document.getElementById("attendancePercent").innerText =
            res.attendance_percentage + "%";


        /* Total Marks */

        document.getElementById("totalMarks").innerText =
            res.total_marks;


        /* Rank */

        document.getElementById("studentRank").innerText =
            res.rank;

        document.getElementById("classSize").innerText =
            res.class_total_students;


        /* Marks Table */

        let rows = "";

        res.marks.forEach(function(m){

            rows += `
                <tr>
                    <td>${m.exam}</td>
                    <td>${m.subject}</td>
                    <td>${m.marks}</td>
                </tr>
            `;

        });

        document.getElementById("marksTableBody").innerHTML = rows;


        renderMarksChart(res.marks);
        renderAttendanceChart(res.attendance_trend);

    })
    .fail(function(){

        alert("Failed to load student profile");

    });

});


/* Marks Trend Chart */

function renderMarksChart(marks){

    let labels = [];
    let data = [];

    marks.forEach(m=>{
        labels.push(m.exam + " - " + m.subject);
        data.push(m.marks);
    });

    new Chart(document.getElementById("marksChart"), {

        type: 'line',

        data: {
            labels: labels,
            datasets: [{
                label: "Marks",
                data: data,
                borderColor: "#1E88E5",
                backgroundColor: "rgba(30,136,229,0.2)",
                fill: true
            }]
        }

    });

}


/* Attendance Trend */

function renderAttendanceChart(data){

    const monthNames = [
        "January","February","March","April",
        "May","June","July","August",
        "September","October","November","December"
    ];

    let labels = [];
    let values = [];

    data.forEach(row => {

        labels.push(monthNames[row.month - 1]);

        let percent = (row.present / row.total) * 100;

        values.push(percent.toFixed(2));

    });

    new Chart(document.getElementById("attendanceChart"), {

        type: 'bar',

        data: {
            labels: labels,
            datasets: [{
                label: "Attendance %",
                data: values,
                backgroundColor: "#4CAF50"
            }]
        },

        options:{
            scales:{
                y:{
                    beginAtZero:true,
                    max:100
                }
            }
        }

    });

}

</script>

@endpush