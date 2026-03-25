@extends('layouts.teacher')

@section('title', 'My Classes')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">

            <div class="card-header">
                <h4 class="card-title">Assigned Classes</h4>
            </div>

            <div class="card-body">
                <div class="table-responsive">

                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Academic Year</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody id="classesTableBody">
                            <tr>
                                <td colspan="5" class="text-center">
                                    Loading...
                                </td>
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

    // Load teacher classes
    apiRequest("GET", "/teacher/classes")
        .done(res => {

            const tbody = document.getElementById("classesTableBody");
            tbody.innerHTML = "";

            if (!res.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center">
                            No classes assigned
                        </td>
                    </tr>`;
                return;
            }

            res.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td>${item.class_name}</td>
                        <td>${item.section_name}</td>
                        <td>${item.subject_name}</td>
                        <td>${item.academic_year}</td>
                        <td>
                            <span class="badge bg-primary">View</span>
                        </td>
                    </tr>`;
            });
        })
        .fail(() => {
            document.getElementById("classesTableBody").innerHTML = `
                <tr>
                    <td colspan="5" class="text-danger text-center">
                        Failed to load classes
                    </td>
                </tr>`;
        });

    // Logout
    document.getElementById("logoutBtn")?.addEventListener("click", function () {
        localStorage.clear();
        window.location.href = "/login";
    });

});
</script>
@endpush