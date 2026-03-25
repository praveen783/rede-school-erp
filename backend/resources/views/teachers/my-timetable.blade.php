@extends('layouts.teacher')

@section('title', 'My Timetable')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">My Timetable</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="timetableGrid">
                        <thead>
                            <tr>
                                <th>Day / Period</th>
                                <th colspan="1" id="periodHeaders">Loading...</th>
                            </tr>
                        </thead>
                        <tbody id="timetableBody">
                            <tr>
                                <td colspan="10" class="text-center">Loading timetable...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    // Frontend Role Guard
    if (!token || !user || user.role !== "teacher") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    // Load header profile
    apiRequest("GET", "/me")
        .done(res => {
            document.getElementById("headerUserName").innerText = res.name;
            document.getElementById("headerUserRole").innerText = "Teacher";
        });

    // Load timetable
    apiRequest("GET", "/timetable/my-teacher")
        .done(res => {
            renderTimetable(res);
        })
        .fail(() => {
            const tbody = document.getElementById("timetableBody");
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-danger text-center">
                        Failed to load timetable.
                    </td>
                </tr>`;
        });

    function renderTimetable(data) {
        const periods = data.periods || [];
        const days = data.days || [];
        const entries = data.entries || [];

        // Map day -> periodId -> entry
        const grid = {};
        entries.forEach(entry => {
            if (!grid[entry.day_of_week]) {
                grid[entry.day_of_week] = {};
            }
            grid[entry.day_of_week][entry.period_id] = entry;
        });

        // Build header row
        const headerRow = document.querySelector("#timetableGrid thead tr");
        headerRow.innerHTML = `<th>Day / Period</th>`;
        periods.forEach(period => {
            const timeLabel = period.start_time && period.end_time
                ? ` (${period.start_time} - ${period.end_time})`
                : "";
            headerRow.innerHTML += `
                <th>${period.name}${timeLabel}</th>`;
        });

        // Build body rows
        const tbody = document.getElementById("timetableBody");
        tbody.innerHTML = "";

        days.forEach(day => {
            const row = document.createElement("tr");
            const dayCell = document.createElement("td");
            dayCell.innerText = day;
            row.appendChild(dayCell);

            periods.forEach(period => {
                const cell = document.createElement("td");

                const entry = grid[day] ? grid[day][period.id] : null;

                if (entry) {
                    const className = entry.timetable?.schoolClass?.name || "";
                    const sectionName = entry.timetable?.section?.name || "";
                    const subjectName = entry.subject?.name || "";

                    cell.innerHTML = `
                        <div><strong>${className}${sectionName ? " " + sectionName : ""}</strong></div>
                        <div>${subjectName}</div>
                    `;
                } else {
                    cell.innerHTML = `<div class="text-muted">-</div>`;
                }

                row.appendChild(cell);
            });

            tbody.appendChild(row);
        });
    }

    // Logout
    document.getElementById("logoutBtn")?.addEventListener("click", function () {
        localStorage.clear();
        window.location.href = "/login";
    });

});
</script>
@endpush
