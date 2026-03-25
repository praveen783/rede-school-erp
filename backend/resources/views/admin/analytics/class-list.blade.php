@extends('layouts.admin')

@section('title','Class Analytics')

@section('content')

<div class="form-head mb-4">
    <h2 class="text-black font-w700 mb-1">Class Analytics</h2>
    <p class="mb-0 text-muted">
        Select class and section to view analytics dashboard
    </p>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-bordered table-striped">

                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Section</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>

                <tbody id="classAnalyticsTableBody">
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            Loading classes...
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

    if (!token || !user) {
        window.location.href = "/login";
        return;
    }

    const tableBody = document.getElementById("classAnalyticsTableBody");

    
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
					school_admin: "School Admin",
					teacher: "Teacher"
				};

				document.getElementById("headerUserRole").innerText =
					roleMap[userRole] ?? userRole;

				console.log("Logged-in user loaded:", res);
			})
			.fail(function (err) {
				console.error("Failed to load user profile", err);
			});


    let activeAcademicYear = null;



    /* ===============================
       LOAD ACTIVE ACADEMIC YEAR
    =============================== */

    apiRequest("GET","/academic-years")
        .done(function(years){

            const active = years.find(y => y.is_active == 1);

            if(active){
                activeAcademicYear = active.id;
            }

            loadClasses();
        });


    /* ===============================
       LOAD CLASSES
    =============================== */

    function loadClasses(){

        apiRequest("GET","/classes",{school_id:user.school_id})
        .done(function(res){

            const classes = res.data ?? res;

            if(classes.length === 0){

                tableBody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center text-muted">
                            No classes found
                        </td>
                    </tr>
                `;

                return;
            }

            let rows = "";

            classes.forEach(function(cls){

                apiRequest("GET","/sections",{class_id:cls.id})
                .done(function(secRes){

                    const sections = secRes.data ?? secRes;

                    sections.forEach(function(section){

                        rows += `
                        <tr>
                            <td>${cls.name}</td>
                            <td>${section.name}</td>

                            <td>
                                <a class="btn btn-primary btn-sm"
                                href="{{ url('/admin/class-analytics') }}?class_id=${cls.id}&section_id=${section.id}&academic_year_id=${activeAcademicYear}">
                                    View Analytics
                                </a>
                            </td>
                        </tr>
                        `;

                        tableBody.innerHTML = rows;

                    });

                });

            });

        })
        .fail(function(){

            tableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center text-danger">
                        Failed to load classes
                    </td>
                </tr>
            `;

        });

    }

});

</script>

@endpush