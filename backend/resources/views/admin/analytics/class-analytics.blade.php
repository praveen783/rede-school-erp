@extends('layouts.admin')

@section('title', 'Class Analytics')

@section('content')

<style>
.cursor-pointer{
    cursor:pointer;
}
</style>

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Class Analytics</h2>
    <p class="mb-0 text-muted">
        Overview of class performance, attendance and student distribution
    </p>
</div>

<div class="row mb-4">

    <!-- Total Students -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5>Total Students</h5>
                <h3 id="totalStudents">0</h3>
            </div>
        </div>
    </div>

    <!-- Attendance -->
    <div class="col-md-3">
        <div class="card cursor-pointer" data-bs-toggle="modal" data-bs-target="#attendanceModal">
            <div class="card-body text-center">
                <h5>Attendance %</h5>
                <h2 id="attendancePercent">0%</h2>
                <p>
                    Present: <span id="presentCount">0</span> |
                    Absent: <span id="absentCount">0</span>
                </p>
            </div>
        </div>
    </div>



    <!-- Class Average -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5>Class Avg Marks</h5>
                <h2 id="classAverageMarks">0</h2>
            </div>
        </div>
    </div>

      <!-- Top Student -->
    <div class="col-md-2">
        <div class="card">
            <div class="card-body text-center">
                <h5>Top Student</h5>
                <h6 id="topStudentName">--</h6>
                <h4 id="topStudentMarks">0</h4>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <!-- Gender Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Gender Distribution</h4>
            </div>
            <div class="card-body">
                <div style="height:300px; max-width:400px; margin:auto;">
                    <canvas id="genderChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4>Category Distribution</h4>
            </div>
            <div class="card-body">
                <div style="height:300px; max-width:400px; margin:auto;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- Student Table -->

<div class="card mt-4">

    <div class="card-header">
        <h4>Student Basic Details</h4>
    </div>

    <div class="card-body">

        <!-- Search + Filters -->
        <div class="row mb-3">

            <div class="col-md-4">
                <input type="text"
                       id="studentSearch"
                       class="form-control"
                       placeholder="Search by name or admission no">
            </div>

            <div class="col-md-3">
                <select id="genderFilter" class="form-control">
                    <option value="">All Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>

            <div class="col-md-3">
                <select id="categoryFilter" class="form-control">
                    <option value="">All Category</option>
                    <option value="General">General</option>
                    <option value="BC-A">BC-A</option>
                    <option value="BC-B">BC-B</option>
                    <option value="BC-C">BC-C</option>
                    <option value="EWS">EWS</option>
                    <option value="Minority">Minority</option>
                </select>
            </div>

        </div>


        <div class="table-responsive">

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Parent Name</th>
                        <th>Gender</th>
                        <th>Category</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody id="studentTableBody"></tbody>

            </table>

        </div>

    </div>

</div>

<!-- Teachers for this Class -->

<div class="card mt-4">

    <div class="card-header">
        <h4>Teachers for this Class</h4>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Subject</th>
                        <th>Role</th>
                    </tr>
                </thead>

                <tbody id="teacherTableBody"></tbody>

            </table>

        </div>

    </div>

</div>

<!-- Exam Results -->
<div class="card mt-4">

    <div class="card-header">
        <h4>Exam Performance</h4>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Exam</th>
                        <th>Total Marks</th>
                    </tr>
                </thead>

                <tbody id="examResultsBody"></tbody>

            </table>

        </div>

    </div>

</div>


<!-- Attendance Modal -->
<div class="modal fade" id="attendanceModal" tabindex="-1">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Today's Attendance</h5>
<button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="table-responsive">

<table class="table table-bordered">

<thead>
<tr>
<th>Admission No</th>
<th>Student Name</th>
<th>Status</th>
</tr>
</thead>

<tbody id="attendanceStudentTable"></tbody>

</table>

</div>

</div>

</div>

</div>

</div>


@endsection



@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

let globalStudents = [];

document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }

    apiRequest("GET", "/me")
        .done(function (res) {

            document.getElementById("headerUserName").innerText =
                res.name ?? "Admin";

            document.getElementById("headerUserRole").innerText =
                res.role ?? "--";

        });


    const params = new URLSearchParams(window.location.search);

    const classId = params.get("class_id");
    const sectionId = params.get("section_id");
    const academicYearId = params.get("academic_year_id");


    apiRequest("GET", "/admin/class-analytics", {
        class_id: classId,
        section_id: sectionId,
        academic_year_id: academicYearId
    })
    .done(function(res){

        renderSummary(res.summary);
        renderAttendance(res.attendance_summary);
        renderGenderChart(res.gender_stats);
        renderCategoryChart(res.category_stats);
        renderStudents(res.students);
        renderExamResults(res.exam_results);
        renderPerformanceAnalytics(res.exam_results);
        loadAttendanceStudents(res.attendance_students);
        loadTeachers(classId, sectionId);

    });


    document.getElementById("studentSearch")
        .addEventListener("keyup", filterStudents);

    document.getElementById("genderFilter")
        .addEventListener("change", filterStudents);

    document.getElementById("categoryFilter")
        .addEventListener("change", filterStudents);

});


/* Summary */

function renderSummary(summary)
{
    document.getElementById("totalStudents").innerText =
        summary.total_students;
}


/* Attendance */

function renderAttendance(att)
{
    document.getElementById("attendancePercent").innerText =
        att.attendance_percentage + "%";

    document.getElementById("presentCount").innerText =
        att.total_present;

    document.getElementById("absentCount").innerText =
        att.total_absent;
}


/* Gender Chart */

function renderGenderChart(data)
{
    new Chart(document.getElementById("genderChart"), {

        type: 'pie',

        data: {

            labels: ['Male','Female'],

            datasets: [{
                data: [
                    data.male ?? 0,
                    data.female ?? 0
                ],
                backgroundColor: ['#3498db','#e74c3c']
            }]
        },

        options:{
            responsive:true,
            maintainAspectRatio:false
        }

    });
}


/* Category Chart */

function renderCategoryChart(stats)
{
    const labels = stats.map(s => s.category);
    const counts = stats.map(s => s.count);

    new Chart(document.getElementById("categoryChart"), {

        type: 'pie',

        data:{
            labels:labels,
            datasets:[{
                data:counts
            }]
        },

        options:{
            responsive:true,
            maintainAspectRatio:false
        }

    });
}


/* Students Table */

function renderStudents(students)
{
    globalStudents = students;

    let rows = "";

    students.forEach(s => {

        const category = s.category?.name ?? "N/A";

        const status = s.is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';

        rows += `
        <tr>
            <td>${s.admission_no}</td>
           <td>
                <a href="/admin/student-profile?id=${s.id}" 
                class="text-primary fw-bold">
                    ${s.name}
                </a>
            </td>
            <td>${s.parent_name}</td>
            <td>${s.gender}</td>
            <td>${category}</td>
            <td>${status}</td>
        </tr>
        `;
    });

    document.getElementById("studentTableBody").innerHTML = rows;

    
}


/* Attendance Modal Data */

function loadAttendanceStudents(attendanceStudents)
{
    let rows = "";

    attendanceStudents.forEach(student => {

        const badge = student.status === "present"
            ? `<span class="badge bg-success">Present</span>`
            : `<span class="badge bg-danger">Absent</span>`;

        rows += `
        <tr>
            <td>${student.admission_no}</td>
            <td>${student.name}</td>
            <td>${badge}</td>
        </tr>
        `;
    });

    document.getElementById("attendanceStudentTable").innerHTML = rows;
}


/* Student Filters */

function filterStudents(){

    const search = document.getElementById("studentSearch").value.toLowerCase();
    const gender = document.getElementById("genderFilter").value;
    const category = document.getElementById("categoryFilter").value;

    const rows = document.querySelectorAll("#studentTableBody tr");

    rows.forEach(row => {

        const admission = row.children[0].innerText.toLowerCase();
        const name = row.children[1].innerText.toLowerCase();
        const rowGender = row.children[3].innerText.toLowerCase();
        const rowCategory = row.children[4].innerText;

        let show = true;

        if(search && !(admission.includes(search) || name.includes(search)))
            show = false;

        if(gender && rowGender !== gender)
            show = false;

        if(category && rowCategory !== category)
            show = false;

        row.style.display = show ? "" : "none";

    });
}


/* Exam Results */

function renderExamResults(results)
{
    let rows = "";

    results.forEach(r => {

        rows += `
        <tr>
            <td>${r.student}</td>
            <td>${r.exam}</td>
            <td>${r.total_marks}</td>
        </tr>
        `;
    });

    document.getElementById("examResultsBody").innerHTML = rows;
}

function renderPerformanceAnalytics(examResults)
{

    if(!examResults || examResults.length === 0) return;

    /* Top Student */

    let topStudent = examResults.reduce((prev, current) =>
        Number(prev.total_marks) > Number(current.total_marks) ? prev : current
    );

    document.getElementById("topStudentName").innerText =
        topStudent.student;

    document.getElementById("topStudentMarks").innerText =
        topStudent.total_marks;



    /* Class Average */

    let totalMarks = 0;

    examResults.forEach(r => {
        totalMarks += Number(r.total_marks);
    });

    let avg = totalMarks / examResults.length;

    document.getElementById("classAverageMarks").innerText =
        avg.toFixed(2);
}

function loadTeachers(classId, sectionId)
{

    apiRequest("GET", "/admin/teacher-allocations", {
        class_id: classId,
        section_id: sectionId
    })
    .done(function(res){

        let rows = "";

        /* Class Teacher */

        if(res.class_teacher)
        {

            const teacherName = res.class_teacher.teacher?.name ?? "N/A";

            rows += `
            <tr>
                <td>${teacherName}</td>
                <td>Class Teacher</td>
                <td>
                    <span class="badge bg-success">
                        Class Teacher
                    </span>
                </td>
            </tr>
            `;

        }

        /* Subject Teachers */

        if(res.subject_teachers && res.subject_teachers.length > 0)
        {

            res.subject_teachers.forEach(item => {

                const teacherName = item.teacher?.name ?? "N/A";
                const subjectName = item.subject?.name ?? "N/A";

                rows += `
                <tr>
                    <td>${teacherName}</td>
                    <td>${subjectName}</td>
                    <td>
                        <span class="badge bg-primary">
                            Subject Teacher
                        </span>
                    </td>
                </tr>
                `;

            });

        }

        document.getElementById("teacherTableBody").innerHTML = rows;

    });

}

</script>

@endpush