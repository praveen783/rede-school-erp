@extends('layouts.student')

@section('title', 'Results')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">
        Exam Results
    </h2>
    <p class="text-muted mb-0">
        View your published exam results
    </p>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Exam</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody id="resultsTableBody">
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            Loading results...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>
</div>

@endsection


@push('scripts')
<script>

document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user || user.role !== "student") {
        localStorage.clear();
        window.location.href = "{{ url('/login') }}";
        return;
    }

    /* =========================
       LOAD HEADER PROFILE
    ========================= */

    apiRequest("GET", "/me")
    .done(function (res) {

        document.getElementById("headerUserName").innerText =
            res.name ?? "Student";

        document.getElementById("headerUserRole").innerText =
            "Student";

    });

    /* =========================
       LOAD EXAM RESULTS LIST
    ========================= */

    loadResults();

});


function loadResults()
{

    apiRequest("GET","/student/results")

    .done(function(res){

        const tbody = document.getElementById("resultsTableBody");

        let rows = "";

        if(!res || res.length === 0){

            rows = `
                <tr>
                    <td colspan="4" class="text-center text-muted">
                        No results published yet
                    </td>
                </tr>
            `;

        } else {

            res.forEach(exam => {

                rows += `
                    <tr>

                        <td>
                            ${exam.name}
                        </td>

                        <td>
                            ${exam.start_date} → ${exam.end_date}
                        </td>

                        <td>
                            <span class="badge bg-success">
                                Published
                            </span>
                        </td>

                        <td>

                            <a href="{{ url('/student/results') }}/${exam.id}"
                                class="btn btn-primary btn-sm">
                                View Result
                            </a>
                        </td>

                    </tr>
                `;

            });

        }

        tbody.innerHTML = rows;

    })

    .fail(function(){

        document.getElementById("resultsTableBody").innerHTML = `
            <tr>
                <td colspan="4" class="text-center text-danger">
                    Failed to load results
                </td>
            </tr>
        `;

    });

}


/* =========================
   LOGOUT
========================= */

document.getElementById("logoutBtn")
.addEventListener("click", function () {

    localStorage.clear();
    window.location.href = "{{ url('/login') }}";

});

</script>
@endpush