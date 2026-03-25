@extends('layouts.teacher')

@section('title', 'My Profile')

@section('content')

<div class="row">

    <!-- Profile Card -->
    <div class="col-xl-4 col-lg-5">
        <div class="card">
            <div class="card-body text-center">

                <img id="teacherPhoto"
                     src="{{ asset('images/default-user.png') }}"
                     class="rounded-circle mb-3"
                     width="120"
                     height="120"
                     style="object-fit: cover;">

                <h4 id="teacherName">--</h4>
                <p class="text-muted mb-1" id="teacherRole">Teacher</p>

                <input type="file"
                       id="photoInput"
                       class="form-control mt-3"
                       accept="image/*">

                <button id="uploadPhotoBtn"
                        class="btn btn-primary mt-2 w-100">
                    Update Photo
                </button>

            </div>
        </div>
    </div>

    <!-- Profile Details -->
    <div class="col-xl-8 col-lg-7">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Profile Details</h4>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Full Name</label>
                        <input type="text" id="profileName" class="form-control" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="text" id="profileEmail" class="form-control" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Phone</label>
                        <input type="text" id="profilePhone" class="form-control" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Employee ID</label>
                        <input type="text" id="profileEmployeeId" class="form-control" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Gender</label>
                        <input type="text" id="profileGender" class="form-control" readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Date of Joining</label>
                        <input type="text" id="profileJoiningDate" class="form-control" readonly>
                    </div>

                    <div class="col-12 mb-3">
                        <label>Address</label>
                        <textarea id="profileAddress"
                                  class="form-control"
                                  rows="3"
                                  readonly></textarea>
                    </div>

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

    if (!token || !user || user.role !== "teacher") {
        localStorage.clear();
        window.location.href = "/login";
        return;
    }

    // Load header info
    apiRequest("GET", "/me")
        .done(res => {
            document.getElementById("headerUserName").innerText = res.name;
            document.getElementById("headerUserRole").innerText = "Teacher";
        });

    // Logout
    document.getElementById("logoutBtn")?.addEventListener("click", function () {
        localStorage.clear();
        window.location.href = "/login";
    });

    /* ==============================
       LOAD TEACHER PROFILE
    ============================== */
    apiRequest("GET", "/teacher/me")
        .done(res => {

            document.getElementById("teacherName").innerText = res.name;
            document.getElementById("profileName").value = res.name;
            document.getElementById("profileEmail").value = res.email || "-";
            document.getElementById("profilePhone").value = res.phone || "-";
            document.getElementById("profileEmployeeId").value = res.employee_id || "-";
            document.getElementById("profileGender").value = res.gender || "-";
            document.getElementById("profileJoiningDate").value = res.joining_date || "-";
            document.getElementById("profileAddress").value = res.address || "-";

            if (res.photo) {
                document.getElementById("teacherPhoto").src =
                    res.photo + "?t=" + new Date().getTime(); // cache bust
            }
        })
        .fail(() => {
            alert("Failed to load profile");
        });

    /* ==============================
       UPDATE PHOTO
    ============================== */
    document.getElementById("uploadPhotoBtn").addEventListener("click", function () {

        const fileInput = document.getElementById("photoInput");
        const file = fileInput.files[0];

        if (!file) {
            alert("Please select a photo");
            return;
        }

        const formData = new FormData();
        formData.append("photo", file);

        $.ajax({
            url: "/teacher/profile/photo",
            type: "POST",
            headers: {
                "Authorization": "Bearer " + token
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                alert("Photo updated successfully");
                fileInput.value = "";
                apiRequest("GET", "/teacher/me").done(res => {
                    if (res.photo) {
                        document.getElementById("teacherPhoto").src =
                            res.photo + "?t=" + new Date().getTime();
                    }
                });
            },
            error: function (err) {
                alert(err.responseJSON?.message || "Photo upload failed");
            }
        });

    });

});
</script>
@endpush