@extends('layouts.student')

@section('title', 'My Profile')

@section('content')

<div class="container-fluid">

    <!-- ===============================
         STUDENT HEADER (PHOTO + NAME)
    ================================ -->
    <div class="card mb-4">
        <div class="card-body d-flex align-items-center">

            <!-- Student Photo -->
            <div class="me-4 text-center">
                <img
                    id="studentPhoto"
                    src="{{ asset('images/no-image.png') }}"
                    alt="Student Photo"
                    style="width:120px;height:150px;object-fit:cover;border-radius:6px;cursor:pointer;border:1px solid #ddd;"
                    title="Click to update photo"
                >
                <input
                    type="file"
                    id="studentPhotoInput"
                    accept="image/png,image/jpeg"
                    style="display:none;"
                >
                <small class="text-muted d-block mt-1">
                    Click image to update
                </small>
            </div>

            <!-- Student Info -->
            <div>
                <h3 id="studentName" class="mb-1">--</h3>
                <p class="mb-0 text-muted">
                    Enrollment No:
                    <span id="studentAdmissionNo">--</span>
                </p>
            </div>

        </div>
    </div>

    <!-- ===============================
         ENROLLED COURSE
    ================================ -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-0">Enrolled Course</h4>
        </div>
        <div class="card-body">
            <p class="mb-2">
                <strong>Class:</strong>
                <span id="studentClass">--</span>
            </p>
            <p class="mb-0">
                <strong>Section:</strong>
                <span id="studentSection">--</span>
            </p>
        </div>
    </div>

    <!-- ===============================
         PERSONAL DETAILS
    ================================ -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-0">Personal Details</h4>
        </div>
        <div class="card-body row">

            <div class="col-md-6 mb-3">
                <strong>Date of Birth:</strong>
                <div id="studentDob">--</div>
            </div>

            <div class="col-md-6 mb-3">
                <strong>Gender:</strong>
                <div id="studentGender">--</div>
            </div>

            <div class="col-md-6 mb-3">
                <strong>Email:</strong>
                <div id="studentEmail">--</div>
            </div>

            <div class="col-md-6 mb-3">
                <strong>Mobile:</strong>
                <div id="studentMobile">--</div>
            </div>

        </div>
    </div>

    <!-- ===============================
         ADDRESS DETAILS
    ================================ -->
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-0">Address</h4>
        </div>
        <div class="card-body">

            <p class="mb-2">
                <strong>Address:</strong>
                <span id="studentAddress">--</span>
            </p>

            <p class="mb-0">
                <strong>State / Pincode:</strong>
                <span id="studentStatePincode">--</span>
            </p>

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
        window.location.href = "/login";
        return;
    }

    // ===============================
    // LOAD STUDENT PROFILE
    // ===============================
    apiRequest("GET", "/me")
        .done(res => {

            document.getElementById("headerUserName").innerText = res.name;

            const student = res.student;
            if (!student) return;

            document.getElementById("studentName").innerText =
                student.name ?? "--";
            document.getElementById("headerUserRole").innerText =
                "Student";

            document.getElementById("studentAdmissionNo").innerText =
                student.admission_no ?? "--";

            document.getElementById("studentClass").innerText =
                student.class?.name ?? "--";

            document.getElementById("studentSection").innerText =
                student.section?.name ?? "--";

            document.getElementById("studentDob").innerText =
                student.dob ?? "--";

            document.getElementById("studentGender").innerText =
                student.gender ?? "--";

            document.getElementById("studentEmail").innerText =
                res.email ?? "--";

            document.getElementById("studentMobile").innerText =
                student.parent_phone ?? "--";

            document.getElementById("studentAddress").innerText =
                student.address ?? "--";

            document.getElementById("studentStatePincode").innerText =
                student.state && student.pincode
                    ? student.state + " / " + student.pincode
                    : "--";

            // PHOTO (cache busting)
            if (student.photo_path) {
                document.getElementById("studentPhoto").src =
                    "/" + student.photo_path + "?t=" + Date.now();
            }
        });

    // ===============================
    // PHOTO UPLOAD
    // ===============================
    const photoImg   = document.getElementById("studentPhoto");
    const photoInput = document.getElementById("studentPhotoInput");

    photoImg.addEventListener("click", () => photoInput.click());

    photoInput.addEventListener("change", function () {

        if (!this.files.length) return;

        const formData = new FormData();
        formData.append("photo", this.files[0]);

        apiRequest("POST", "/student/profile/photo", formData, true)
            .done(res => {
                photoImg.src =
                    "/" + res.photo_path + "?t=" + Date.now();

                alert("Photo updated successfully");
            })
            .fail(() => alert("Photo upload failed"));
    });

});
</script>
@endpush