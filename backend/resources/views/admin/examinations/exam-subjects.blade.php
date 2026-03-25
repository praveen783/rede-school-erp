@extends('layouts.admin')

@section('title', 'Assign Exam Subjects')

@section('content')

<!-- PAGE HEADER -->
<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Assign Subjects</h2>
    <p class="mb-0 text-muted">
        Configure subjects, max marks and pass marks for this exam
    </p>
</div>

<!-- EXAM INFO -->
<div class="alert alert-info mb-4">
    <strong>Exam:</strong> <span id="examNameEl">--</span><br>
    <strong>Class & Section:</strong> <span id="classSectionEl">--</span>
</div>

<!-- SUBJECTS TABLE -->
<div class="card">
    <div class="card-body">

        <div id="mappingStatus" class="alert alert-info d-none"></div>
        <div id="lockAlert" class="alert alert-warning d-none"></div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th width="60">Select</th>
                    <th>Subject</th>
                    <th width="150">Max Marks</th>
                    <th width="150">Pass Marks</th>
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

        <div class="text-end mt-3">
            <button id="saveSubjectsBtn" class="btn btn-primary">
                Save Subjects
            </button>
        </div>

    </div>
</div>

@endsection
@push('scripts')

<script>
    document.addEventListener("DOMContentLoaded", function () {

        // ======================================================
        // AUTH CHECK
        // ======================================================
        const token = localStorage.getItem("auth_token");
        const user  = JSON.parse(localStorage.getItem("user"));

        if (!token || !user) {
            alert("Session expired. Please login again.");
            window.location.href = "/login";
            return;
        }

        // ================================
        // LOAD ADMIN DETAILS (/me)
        // ================================
        apiRequest("GET", "/me")
            .done(function (res) {

                const userName = res.name ?? "Admin";
                const userRole = res.role ?? "--";

                document.getElementById("headerUserName").innerText = userName;

                const roleMap = {
                    super_admin: "Super Admin",
                    school_admin: "School Admin"
                    };

                document.getElementById("headerUserRole").innerText =
                    roleMap[userRole] ?? userRole;

                    console.log("Logged-in user loaded:", res);
                })
                .fail(function (err) {
                    console.error("Failed to load user profile", err);
                });

       // ======================================================
        // READ IDs (CORRECTED)
        // ======================================================

        // examId comes from Laravel route
        const examId = @json($examId ?? null);

        // classId + sectionId come from query string
        const params = new URLSearchParams(window.location.search);
        const classId = params.get("class_id");
        const sectionId = params.get("section_id");

        if (!examId || !classId || !sectionId) {
            alert("Invalid exam or class.");
            window.location.href = "{{ url('/admin/examinations/exams') }}";
            return;
        }
        
        // =====================================================
        // DOM ELEMENTS
        // ======================================================
        const examNameEl = document.getElementById("examNameEl");
        const classSectionEl = document.getElementById("classSectionEl");
        const tableBody      = document.getElementById("subjectsTable");
        const saveBtn        = document.getElementById("saveSubjectsBtn");

        let mappedSubjects = [];

        // ======================================================
        // LOAD INITIAL DATA
        // ======================================================
        loadExamDetails();

        loadMappedSubjects().then(() => {
            loadClassSubjects();
        });


        // ======================================================
        // LOAD EXAM DETAILS
        // ======================================================
        function loadExamDetails() {
            apiRequest("GET", `/exams/${examId}`)
                .done(res => {

                    examNameEl.innerText = res.name ?? "--";

                    const examClassId = res.class_id;
                    const examSectionId = res.section_id;

                    // Now fetch real names
                    loadClassSectionName(examClassId, examSectionId);

                })
                .fail(() => {
                    examNameEl.innerText = "--";
                });
        }

        function loadClassSectionName(classId, sectionId) {

            apiRequest("GET", "/class-sections")
                .done(list => {

                    if (!Array.isArray(list)) {
                        classSectionEl.innerText =
                            `Class ${classId} - Section ${sectionId}`;
                        return;
                    }

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

                })
                .fail(() => {
                    classSectionEl.innerText =
                        `Class ${classId} - Section ${sectionId}`;
                });
        }

        // ======================================================
        // LOAD ALREADY MAPPED SUBJECTS
        // ======================================================
        function loadMappedSubjects() {
            return apiRequest("GET", `/exams/${examId}/subjects`)
                .done(res => {

                    mappedSubjects = Array.isArray(res.subjects)
                        ? res.subjects
                        : [];

                    const statusBox = document.getElementById("mappingStatus");
                    const lockAlert = document.getElementById("lockAlert");

                    // 🔐 LOCK STATE
                    if (res.locked) {
                        lockAlert.classList.remove("d-none");
                        lockAlert.innerHTML = `<strong>Locked:</strong> ${res.message}`;
                        saveBtn.disabled = true;
                    }

                    // 📌 Already mapped subjects info
                    if (mappedSubjects.length > 0) {
                        statusBox.classList.remove("d-none");
                        statusBox.innerHTML = `
                            <strong>${mappedSubjects.length} subjects already assigned.</strong>
                            You may update marks or modify selection.
                        `;
                    } else {
                        statusBox.classList.add("d-none");
                    }

                })
                .fail(() => {
                    mappedSubjects = [];
                });
        }


        // ======================================================
        // LOAD CLASS-WISE SUBJECTS
        // ======================================================
        function loadClassSubjects() {
            apiRequest("GET", `/classes/${classId}/subjects`)
                .done(renderSubjects)
                .fail(() => {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-danger">
                                Failed to load subjects
                            </td>
                        </tr>`;
                });
        }

        // ======================================================
        // RENDER SUBJECT TABLE
        // ======================================================
        function renderSubjects(subjects) {

            tableBody.innerHTML = "";

            console.log("Class subjects:", subjects);
            console.log("Mapped subjects:", mappedSubjects);

            console.log("mappedSubjects type:", typeof mappedSubjects);
            console.log("mappedSubjects value:", mappedSubjects);
            console.log("isArray:", Array.isArray(mappedSubjects));


            const statusBox = document.getElementById("mappingStatus");

            // Show mapping status if subjects already mapped
            // Always check existence before using classList
            if (statusBox) {
                if (mappedSubjects.length > 0) {
                    statusBox.classList.remove("d-none");
                    statusBox.innerHTML = `
                        <strong>Subjects already mapped.</strong>
                        You can update marks until results are published.
                    `;
                } else {
                    statusBox.classList.add("d-none");
                }
            }

            if (!subjects || subjects.length === 0) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No subjects assigned to this class
                        </td>
                    </tr>`;
                return;
            }

            subjects.forEach(subject => {

                // Match with exam_subjects table
                const mapped = mappedSubjects.find(
                    ms => ms.subject_id === subject.id
                );

                const isChecked = !!mapped;
                const maxMarks  = mapped ? mapped.max_marks : "";
                const passMarks = mapped ? mapped.pass_marks : "";

                const row = `
                    <tr>
                        <td class="text-center">
                            <input type="checkbox"
                                class="form-check-input subject-check"
                                data-subject-id="${subject.id}"
                                ${isChecked ? "checked" : ""}>
                        </td>

                        <td>${subject.name}</td>

                        <td>
                            <input type="number"
                                class="form-control form-control-sm max-marks"
                                min="1"
                                value="${maxMarks}"
                                ${isChecked ? "" : "disabled"}>
                        </td>

                        <td>
                            <input type="number"
                                class="form-control form-control-sm pass-marks"
                                min="0"
                                value="${passMarks}"
                                ${isChecked ? "" : "disabled"}>
                        </td>
                    </tr>
                `;

                tableBody.insertAdjacentHTML("beforeend", row);
            });

            bindCheckboxEvents();
        }


        // ======================================================
        // ENABLE / DISABLE MARKS INPUTS
        // ======================================================
        function bindCheckboxEvents() {

            document.querySelectorAll(".subject-check").forEach(cb => {

                cb.addEventListener("change", function () {

                    const row = cb.closest("tr");
                    const maxInput  = row.querySelector(".max-marks");
                    const passInput = row.querySelector(".pass-marks");

                    // Enable / Disable inputs
                    maxInput.disabled  = !cb.checked;
                    passInput.disabled = !cb.checked;

                    if (!cb.checked) {
                        maxInput.value  = "";
                        passInput.value = "";
                    }

                    // 🔔 Mark row as modified
                    row.classList.add("table-warning");
                });

            });

            // 🎯 Detect marks modification
            document.querySelectorAll(".max-marks, .pass-marks")
                .forEach(input => {

                    input.addEventListener("input", function () {
                        const row = input.closest("tr");
                        row.classList.add("table-warning");
                    });

                });
        }


        // ======================================================
        // SAVE SUBJECTS
        // ======================================================
        saveBtn.addEventListener("click", function () {

            const subjects = [];
            let hasChanges = false;

            document.querySelectorAll(".subject-check:checked").forEach(cb => {

                const row = cb.closest("tr");
                const subjectId = cb.dataset.subjectId;
                const maxMarks  = row.querySelector(".max-marks").value;
                const passMarks = row.querySelector(".pass-marks").value;

                if (!maxMarks || !passMarks) {
                    alert("Please enter max & pass marks for all selected subjects");
                    return;
                }

                if (parseInt(passMarks) > parseInt(maxMarks)) {
                    alert("Pass marks cannot exceed max marks");
                    return;
                }

                subjects.push({
                    subject_id: subjectId,
                    max_marks: parseInt(maxMarks),
                    pass_marks: parseInt(passMarks)
                });
            });

            if (subjects.length === 0) {
                alert("Select at least one subject");
                return;
            }

            // 🧠 Detect modification
            if (mappedSubjects.length > 0) {
                if (!confirm("Existing subjects will be updated. Continue?")) {
                    return;
                }
            }

            apiRequest("POST", `/exams/${examId}/subjects`, {
                subjects: subjects
            })
            .done(() => {
                alert("Subjects updated successfully");
                location.reload(); // stay on same page and reflect changes
            })
            .fail(err => {
                alert(err.responseJSON?.message || "Failed to save subjects");
            });
        });

    });
</script>

@endpush

