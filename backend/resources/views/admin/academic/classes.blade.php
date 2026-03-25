@extends('layouts.admin')

@section('title', 'Classes & Sections')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Classes & Sections</h2>
    <p class="mb-0 text-muted">
        Manage school classes and sections
    </p>
</div>

<div class="card">
    <div class="card-body">

        <!-- Top Action -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Class List</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                + Add Class
            </button>
        </div>

        <!-- Classes Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Class Name</th>
                        <th>Sections</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody id="classTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Loading classes...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="classForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- Class Name -->
                    <div class="mb-3">
                        <label class="form-label">Class Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <!-- Sections -->
                    <div class="mb-3">
                        <label class="form-label d-flex justify-content-between align-items-center">
                            <span>Sections</span>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                onclick="addSectionInput()">
                                + Add Section
                            </button>
                        </label>

                        <div id="sectionInputs">

                            <!-- Default Section Input -->
                            <div class="input-group mb-2 section-row">
                                <input type="text"
                                    class="form-control section-input"
                                    placeholder="Section Name (e.g., A)"
                                    required>
                                <button type="button"
                                    class="btn btn-outline-danger"
                                    onclick="removeSectionInput(this)">
                                    ×
                                </button>
                            </div>

                        </div>

                        <small class="text-muted">
                            At least one section is required
                        </small>
                    </div>

                    <div id="classFormError" class="text-danger small"></div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Create</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
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

    // ===============================
    // LOAD LOGGED-IN USER
    // ===============================
    apiRequest("GET", "/me")
        .done(function (res) {

            const roleMap = {
                super_admin: "Super Admin",
                school_admin: "School Admin"
            };

            document.getElementById("headerUserName").innerText =
                res.name ?? "Admin";

            document.getElementById("headerUserRole").innerText =
                roleMap[res.role] ?? res.role;

        });

    loadClasses();

    // ===============================
    // LOAD CLASSES USING /class-sections
    // ===============================
    function loadClasses() {

        apiRequest("GET", "/class-sections")
            .done(function (response) {

                const data = response.data ?? response ?? [];

                if (!data.length) {
                    document.getElementById("classTableBody").innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No classes found
                            </td>
                        </tr>
                    `;
                    return;
                }

                // Group by class
                const grouped = {};

                data.forEach(item => {

                    if (!grouped[item.class_id]) {
                        grouped[item.class_id] = {
                            class_name: item.class_name,
                            sections: []
                        };
                    }

                    grouped[item.class_id].sections.push(item.section_name);
                });

                let rows = "";

                Object.keys(grouped).forEach(classId => {

                    const cls = grouped[classId];

                    rows += `
                        <tr>
                            <td>${cls.class_name}</td>
                            <td>
                                ${cls.sections.length} Section(s)
                                <br>
                                <small class="text-muted">
                                    ${cls.sections.join(", ")}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info"
                                    onclick="viewSections(${classId})">
                                    View
                                </button>
                            </td>
                        </tr>
                    `;
                });

                document.getElementById("classTableBody").innerHTML = rows;

            })
            .fail(function () {
                alert("Failed to load classes");
            });
    }

    // ===============================
    // CREATE CLASS WITH SECTIONS
    // ===============================
    document.getElementById("classForm")
        .addEventListener("submit", function (e) {

            e.preventDefault();

            const className = this.name.value.trim();

            const sectionInputs =
                document.querySelectorAll(".section-input");

            const sections = [];

            sectionInputs.forEach(input => {
                if (input.value.trim() !== "") {
                    sections.push(input.value.trim());
                }
            });

            if (!className) {
                alert("Class name is required");
                return;
            }

            if (!sections.length) {
                alert("At least one section is required");
                return;
            }

            const payload = {
                name: className,
                sections: sections
            };

            apiRequest("POST", "/classes", payload)
                .done(function () {

                    document.getElementById("classForm").reset();

                    bootstrap.Modal.getInstance(
                        document.getElementById("addClassModal")
                    ).hide();

                    loadClasses();
                })
                .fail(function (xhr) {

                    const error =
                        xhr.responseJSON?.message ??
                        "Failed to create class";

                    alert(error);
                });
        });

    // ===============================
    // VIEW SECTIONS (FILTERED FROM /class-sections)
    // ===============================
    window.viewSections = function (classId) {

        apiRequest("GET", "/class-sections")
            .done(function (response) {

                const data = response.data ?? response ?? [];

                const sections = data
                    .filter(item => item.class_id == classId)
                    .map(item => item.section_name);

                if (!sections.length) {
                    alert("No sections found");
                    return;
                }

                alert("Sections: " + sections.join(", "));
            })
            .fail(function () {
                alert("Failed to load sections");
            });
    };
    // ===============================
    // ADD SECTION INPUT
    // ===============================
    window.addSectionInput = function () {

        const container = document.getElementById("sectionInputs");

        const row = document.createElement("div");
        row.className = "input-group mb-2 section-row";

        row.innerHTML = `
            <input type="text"
                class="form-control section-input"
                placeholder="Section Name (e.g., A)"
                required>
            <button type="button"
                class="btn btn-outline-danger"
                onclick="removeSectionInput(this)">
                ×
            </button>
        `;

        container.appendChild(row);
    };

    // ===============================
    // REMOVE SECTION INPUT
    // ===============================
    window.removeSectionInput = function (btn) {

        const rows = document.querySelectorAll(".section-row");

        if (rows.length === 1) {
            alert("At least one section is required");
            return;
        }

        btn.closest(".section-row").remove();
    };

});
</script>

@endpush