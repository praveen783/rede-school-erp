@extends('layouts.teacher')

@section('title', 'Enter Marks')

@section('content')

<!-- FILTERS -->
<div class="card mb-4">
    <div class="card-body row">

        <div class="col-md-3">
            <label>Class & Section</label>
            <select id="classSelect" class="form-control">
                <option value="">Select</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Exam</label>
            <select id="examSelect" class="form-control">
                <option value="">Select</option>
            </select>
        </div>

        <div class="col-md-3">
            <label>Subject</label>
            <select id="subjectSelect" class="form-control">
                <option value="">Select</option>
            </select>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button id="loadStudentsBtn" class="btn btn-primary w-100">
                Load Students
            </button>
        </div>

    </div>
</div>

<!-- STUDENTS TABLE -->
<div class="card">
    <div class="card-body">

        <table class="table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Marks</th>
                </tr>
            </thead>

            <tbody id="studentsTable">
                <tr>
                    <td colspan="2" class="text-center text-muted">
                        Select class, exam and subject
                    </td>
                </tr>
            </tbody>
        </table>

        <button id="submitMarksBtn"
                class="btn btn-success mt-3"
                disabled>
            Submit Marks
        </button>

    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user || user.role !== "teacher") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    const classSelect = document.getElementById("classSelect");
    const examSelect = document.getElementById("examSelect");
    const subjectSelect = document.getElementById("subjectSelect");
    const submitMarksBtn = document.getElementById("submitMarksBtn");

    let selectedExamSubjects = [];

    // Load header profile
    apiRequest("GET", "/me")
        .done(res => {
            document.getElementById("headerUserName").innerText = res.name;
            document.getElementById("headerUserRole").innerText = "Teacher";
        });

    document.getElementById("logoutBtn")?.addEventListener("click", function () {
        localStorage.clear();
        window.location.href = "/login";
    });

    /* ==============================
       LOAD TEACHER CLASSES
    ============================== */
    apiRequest("GET", "/teacher/classes")
        .done(res => {

            classSelect.innerHTML = `<option value="">Select</option>`;

            res.forEach(item => {

                const opt = document.createElement("option");

                opt.value = `${item.class_id}|${item.section_id}`;
                opt.text  = `${item.class_name} - ${item.section_name}`;

                classSelect.appendChild(opt);

            });
        });


    /* ==============================
       LOAD EXAMS WHEN CLASS CHANGES
    ============================== */
    classSelect.addEventListener("change", function () {

        const value = this.value;

        examSelect.innerHTML = `<option value="">Loading exams...</option>`;
        subjectSelect.innerHTML = `<option value="">Select subject</option>`;

        if (!value) return;

        const [class_id, section_id] = value.split("|");

        apiRequest("GET", "/teacher/exams", {
            class_id,
            section_id
        })
        .done(res => {

            examSelect.innerHTML = `<option value="">Select exam</option>`;

            res.forEach(item => {

                const opt = document.createElement("option");

                opt.value = item.id;
                opt.textContent = item.name;

                examSelect.appendChild(opt);

            });

        })
        .fail(() => {

            examSelect.innerHTML = `<option value="">Failed to load exams</option>`;

        });

    });


    /* ==============================
       LOAD SUBJECTS WHEN EXAM CHANGES
    ============================== */
    examSelect.addEventListener("change", function () {

        const examId = this.value;
        const classValue = classSelect.value;

        subjectSelect.innerHTML = `<option value="">Loading subjects...</option>`;

        if (!examId || !classValue) return;

        const [class_id, section_id] = classValue.split("|");

        apiRequest("GET", `/teacher/exams/${examId}/subjects`, {
            class_id,
            section_id
        })
        .done(res => {

            subjectSelect.innerHTML = `<option value="">Select subject</option>`;

            selectedExamSubjects = res;

            res.forEach(item => {

                const opt = document.createElement("option");

                opt.value = item.id;
                opt.textContent = `${item.name} (Max:${item.max_marks}, Pass:${item.pass_marks})`;

                subjectSelect.appendChild(opt);

            });

        })
        .fail(() => {

            subjectSelect.innerHTML = `<option value="">Failed to load subjects</option>`;

        });

    });


    /* ==============================
       LOAD STUDENTS
    ============================== */
    document.getElementById("loadStudentsBtn")
        .addEventListener("click", function () {

            const classValue = classSelect.value;
            const examId = examSelect.value;
            const subjectId = subjectSelect.value;

            if (!classValue || !examId || !subjectId) {
                alert("Please select class, exam and subject");
                return;
            }

            const [class_id, section_id] = classValue.split("|");

            apiRequest("GET", `/teacher/exams/${examId}/marks-sheet`, {
                class_id,
                section_id,
                subject_id: subjectId
            })
            .done(res => {

                const tbody = document.getElementById("studentsTable");
                tbody.innerHTML = "";

                const students = res.students || [];
                const maxMarks = res.max_marks;
                const passMarks = res.pass_marks;

                students.forEach(stu => {

                    const value = stu.marks_obtained ?? "";

                    tbody.innerHTML += `
                        <tr>

                            <td>${stu.name}</td>

                            <td>

                                <input type="number"
                                    class="form-control marks-input"
                                    data-student-id="${stu.student_id}"
                                    value="${value}"
                                    max="${maxMarks}"
                                    min="0"
                                    placeholder="0 - ${maxMarks}">

                                <small class="text-muted">
                                    Max: ${maxMarks} | Pass: ${passMarks}
                                </small>

                            </td>

                        </tr>
                    `;
                });

                submitMarksBtn.disabled = false;

            });

        });

    /* ==============================
       SUBMIT MARKS
    ============================== */
    submitMarksBtn.addEventListener("click", function () {

        const classValue = classSelect.value;
        const examId = examSelect.value;
        const subjectId = subjectSelect.value;

        const [class_id, section_id] = classValue.split("|");

        const marks = [];

        document.querySelectorAll(".marks-input")
        .forEach(input => {

            const value = input.value.trim();
            const isAbsent = value === "";

            marks.push({

                student_id: Number(input.dataset.studentId),
                marks_obtained: isAbsent ? null : Number(value),
                is_absent: isAbsent

            });

        });

        apiRequest("POST", "/teacher/marks", {

            exam_id: examId,
            class_id,
            section_id,
            subject_id: subjectId,
            marks

        })
        .done(() => {

            alert("Marks submitted successfully");

            submitMarksBtn.disabled = true;

        })
        .fail(err => {

            alert(err.responseJSON?.message || "Failed to submit marks");

        });

    });

});
</script>
@endpush