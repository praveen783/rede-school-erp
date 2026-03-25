@extends('layouts.admin')

@section('title', 'Create Syllabus')

@section('content')

<div id="alertContainer"></div>
<div class="form-head mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="text-black font-w700 mb-1">Create New Syllabus</h2>
        <p class="mb-0 text-muted">Setup syllabus basic information</p>
    </div>

    <a href="{{ url('admin/academic/syllabus') }}" 
       class="btn btn-secondary">
        ← Back to List
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">

        <div class="row mb-3">

            <div class="col-md-4">
                <label class="form-label">Class</label>
                <select id="classSelect" class="form-control">
                    <option value="">Select Class</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Subject</label>
                <select id="subjectSelect" class="form-control">
                    <option value="">Select Subject</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Board</label>
                <select id="boardSelect" class="form-control">
                    <option value="">Select Board</option>
                </select>
            </div>

        </div>

        <div class="mb-3">
            <label class="form-label">Syllabus Title</label>
            <input type="text" 
                   id="syllabusTitle" 
                   class="form-control"
                   placeholder="Enter syllabus title">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea id="syllabusDescription"
                      class="form-control"
                      rows="3"
                      placeholder="Short description"></textarea>
        </div>

        <button class="btn btn-primary" id="saveSyllabusBtn">
            Save & Continue
        </button>

    </div>
</div>

@endsection


@push('scripts')
<script>

document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }
    // Load logged-in user
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

        })
        .fail(function () {
            console.error("Failed to load profile");
        });

    loadClasses();
    loadSubjects();
    loadBoards();

    
    // ================================
    // LOAD CLASSES (From Class Sections)
    // ================================
    function loadClasses() {

        apiRequest("GET", "/class-sections")
            .done(function (data) {

                const grouped = {};
                const select = document.getElementById("classSelect");

                data.forEach(item => {
                    if (!grouped[item.class_id]) {
                        grouped[item.class_id] = item.class_name;
                    }
                });

                select.innerHTML =
                    '<option value="">Select Class</option>';

                Object.keys(grouped).forEach(id => {
                    select.insertAdjacentHTML("beforeend", `
                        <option value="${id}">
                            ${grouped[id]}
                        </option>
                    `);
                });
            });
    }

    // ================================
    // LOAD SUBJECTS
    // ================================
    function loadSubjects() {
        apiRequest("GET", "/subjects")
            .done(function (subjects) {
                const select = document.getElementById("subjectSelect");

                subjects.forEach(sub => {
                    select.insertAdjacentHTML("beforeend",
                        `<option value="${sub.id}">${sub.name}</option>`
                    );
                });
            });
    }

    // ================================
    // LOAD BOARDS
    // ===============================

    function loadBoards() {
        apiRequest("GET", "/boards")
            .done(function (response) {

                const select = document.getElementById("boardSelect");
                select.innerHTML = '<option value="">Select Board</option>';

                response.data.forEach(board => {
                    select.insertAdjacentHTML("beforeend",
                        `<option value="${board.id}">${board.name}</option>`
                    );
                });
            });
    }
    
    // ================================
    // SAVE SYLLABUS
    // ================================
    document.getElementById("saveSyllabusBtn")
        .addEventListener("click", function () {

            const btn = this;

            const classId = document.getElementById("classSelect").value;
            const subjectId = document.getElementById("subjectSelect").value;
            const boardId = document.getElementById("boardSelect").value;
            const title = document.getElementById("syllabusTitle").value;
            const description = document.getElementById("syllabusDescription").value;

            if (!classId || !subjectId || !boardId || !title) {
                alert("All required fields must be filled");
                return;
            }

            // Disable button
            btn.disabled = true;
            btn.innerText = "Saving...";

            apiRequest("POST", "/syllabus", {
                class_id: classId,
                subject_id: subjectId,
                board_id: boardId,
                title: title,
                description: description
            })
            .done(function (response) {

                const syllabusId = response.data.id;

                // Show success alert
                document.getElementById("alertContainer").innerHTML = `
                    <div class="alert alert-success">
                        ✅ Syllabus created successfully! Redirecting to manage page...
                    </div>
                `;

                // Redirect after delay
                setTimeout(function () {
                    window.location.href =
                        "{{ url('admin/academic/syllabus') }}/" + syllabusId + "/manage";
                }, 1200);

            })
            .fail(function (err) {

                btn.disabled = false;
                btn.innerText = "Save & Continue";

                document.getElementById("alertContainer").innerHTML = `
                    <div class="alert alert-danger">
                        ❌ ${err.responseJSON?.message ?? "Something went wrong"}
                    </div>
                `;
            });

        });

});
</script>

@endpush