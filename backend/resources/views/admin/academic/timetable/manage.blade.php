@extends('layouts.admin')

@section('title', 'Manage Timetable')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700">Manage Timetable</h2>
</div>

{{-- 🔹 Timetable Info Header --}}
<div class="card shadow-sm mb-4">
    <div class="card-body" id="timetableHeader">
        Loading timetable details...
    </div>
</div>

{{-- 🔹 Timetable Grid --}}
<div class="card shadow-sm">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle">
                <thead id="timetableHead"></thead>
                <tbody id="timetableBody"></tbody>
            </table>
        </div>

    </div>
</div>

@include('admin.academic.timetable._partials.modal')

@endsection


@push('scripts')

<script>

const timetableId = "{{ $id }}";
let assignmentCache = null;

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
        });

    loadTimetable();
    loadAssignments();
});


// ================= LOAD TIMETABLE =================
function loadTimetable() {

    apiRequest("GET", `/timetables/${timetableId}`)
        .done(function (res) {

            const timetable = res.timetable;

            // 🔹 Show Class & Section at top
            document.getElementById("timetableHeader").innerHTML = `
                <h5 class="mb-1">
                    ${timetable.academic_year?.name ?? ''} |
                    ${timetable.school_class?.name ?? ''} 
                    ${timetable.section?.name ?? ''}
                </h5>
                <small class="text-muted">
                    Timetable ID: ${timetable.id}
                </small>
            `;

            renderGrid(res.periods, res.days, timetable.entries);
        })
        .fail(function () {
            alert("Failed to load timetable");
        });
}


// ================= RENDER GRID =================
function renderGrid(periods, days, entries) {

    const head = document.getElementById("timetableHead");
    const body = document.getElementById("timetableBody");

    head.innerHTML = "";
    body.innerHTML = "";

    // ===== HEADER =====
    let headRow = `<tr><th width="150">Day / Period</th>`;

    periods.forEach(p => {
        headRow += `
            <th>
                ${p.name}<br>
                <small>${p.start_time} - ${p.end_time}</small>
            </th>
        `;
    });

    headRow += `</tr>`;
    head.innerHTML = headRow;

    // ===== BODY =====
    days.forEach(day => {

        let row = `<tr><td><strong>${day}</strong></td>`;

        periods.forEach(period => {

            const entry = entries.find(e =>
                e.day_of_week === day &&
                e.period_id === period.id
            );

            if (entry) {
                row += `
                    <td class="bg-light">
                        <div><strong>${entry.subject?.name ?? ''}</strong></div>
                        <small>${entry.teacher?.name ?? ''}</small>

                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="openAssignModal('${day}', ${period.id}, ${entry.id})">
                                Edit
                            </button>
                        </div>
                    </td>
                `;
            } else {
                row += `
                    <td class="text-center text-primary fw-bold"
                        style="cursor:pointer; font-size:18px"
                        onclick="openAssignModal('${day}', ${period.id}, null)">
                        +
                    </td>
                `;
            }
        });

        row += `</tr>`;
        body.insertAdjacentHTML("beforeend", row);
    });
}

// ================= LOAD ASSIGNMENTS =================
function loadAssignments() {

    apiRequest("GET", `/timetables/${timetableId}`)
        .done(function (res) {

            const timetable = res.timetable;

            apiRequest("GET",
                `/timetables/class-data?academic_year_id=${timetable.academic_year_id}&class_id=${timetable.class_id}&section_id=${timetable.section_id}`)
                .done(function (data) {
                    assignmentCache = data;
                });
        });
}


// ================= OPEN MODAL =================
function openAssignModal(day, periodId, entryId = null) {

    if (!assignmentCache) {
        alert("Loading subject data...");
        return;
    }

    document.getElementById("modalDay").value = day;
    document.getElementById("modalPeriod").value = periodId;
    document.getElementById("modalEntryId").value = entryId ?? "";

    const subjectSelect = document.getElementById("modalSubject");
    const teacherSelect = document.getElementById("modalTeacher");

    subjectSelect.innerHTML = "";
    teacherSelect.innerHTML = '<option value="">Select Teacher</option>';

    // Load subjects
    assignmentCache.subjects.forEach(sub => {
        subjectSelect.insertAdjacentHTML("beforeend",
            `<option value="${sub.id}">${sub.name}</option>`
        );
    });

    function loadTeachersBySubject(subjectId) {

        teacherSelect.innerHTML = '<option value="">Select Teacher</option>';

        const teachers =
            assignmentCache.teachers_by_subject[subjectId] || [];

        teachers.forEach(teacher => {
            teacherSelect.insertAdjacentHTML("beforeend",
                `<option value="${teacher.id}">
                    ${teacher.name}
                </option>`
            );
        });
    }

    subjectSelect.addEventListener("change", function () {
        loadTeachersBySubject(this.value);
    });

    if (entryId) {

        apiRequest("GET", `/timetables/${timetableId}`)
            .done(function (res) {

                const entry =
                    res.timetable.entries.find(e => e.id == entryId);

                if (entry) {
                    subjectSelect.value = entry.subject_id;
                    loadTeachersBySubject(entry.subject_id);
                    teacherSelect.value = entry.teacher_id;
                }
            });
    }

    new bootstrap.Modal(
        document.getElementById("assignModal")
    ).show();
}

function saveEntry() {

    const entryId = document.getElementById("modalEntryId").value;
    const day = document.getElementById("modalDay").value;
    const periodId = document.getElementById("modalPeriod").value;
    const subjectId = document.getElementById("modalSubject").value;
    const teacherId = document.getElementById("modalTeacher").value;

    const payload = {
        day_of_week: day,
        period_id: periodId,
        subject_id: subjectId,
        teacher_id: teacherId
    };

    if (entryId) {

        // UPDATE
        apiRequest("PUT", `/timetables/entries/${entryId}`, payload)
            .done(function () {

                bootstrap.Modal.getInstance(
                    document.getElementById("assignModal")
                ).hide();

                loadTimetable();
            });

    } else {

        // CREATE
        apiRequest("POST", `/timetables/${timetableId}/entries`, payload)
            .done(function () {

                bootstrap.Modal.getInstance(
                    document.getElementById("assignModal")
                ).hide();

                loadTimetable();
            });
    }
}

</script>

@endpush