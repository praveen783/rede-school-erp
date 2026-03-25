<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>	
	<!-- Title -->
	<title>Eduveda | Page Login</title>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignZone">
	<meta name="robots" content="">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="keywords" content="bootstrap, courses, education admin template, educational, instructors, learning, learning admin, learning admin theme, learning application, lessons, lms admin template, lms rails, quizzes ui, school admin">
	<meta name="description" content="Some description for the page"/>
	<meta property="og:title" content="Owlio - School Admission Admin Dashboard">
	<meta property="og:description" content="Owlio Laravel | Page Login" />
	<meta property="og:image" content="../../social-image.png">
	<meta name="format-detection" content="telephone=no">

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Favicons Icon -->
	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon.png') }}">
    <link href="{{ asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>

<body class="vh-100">
      <div class="authincation h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100 align-items-center">
                <div class="col-md-6">
    <div class="authincation-content">
        <div class="row no-gutters">
            <div class="col-xl-12">
                <div class="auth-form">
                    <div class="text-center mb-3">
                        <img src="public/images/logo-full.png" alt="">
                    </div>
                    <h4 class="text-center mb-4">Sign in your account</h4>
                    <form id="loginForm">
                        <input type="hidden" name="_token" value="7r9S4OG6empHntcP96fML3XOHxxVRRnoFgVf21eo">                       
                         <div class="form-group">
                            <label class="mb-1"><strong>Email</strong></label>
                            <input type="email" id="email" class="form-control" placeholder="Email">
                        </div>
                        <div class="form-group">
                            <label class="mb-1"><strong>Password</strong></label>
                            <input type="password" id="password" class="form-control" placeholder="Password">
                        </div>
                        <div class="row d-flex justify-content-between mt-4 mb-2">
                            <div class="form-group">
                               <div class="form-check custom-checkbox ms-1">
                                    <input type="checkbox" class="form-check-input" id="basic_checkbox_1">
                                    <label class="form-check-label" for="basic_checkbox_1">Remember my preference</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <a href="page-forgot-password.html">Forgot Password?</a>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-block">Sign Me In</button>
                        </div>
                    </form>
                    <div class="new-account mt-3">
                        <p>Don't have an account? <a class="text-primary" href="page-register.html">Sign up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>
        </div>
    </div>
<!--**********************************
	Scripts
***********************************-->
    <!-- Required vendors -->



       <script src="{{ asset('vendor/global/global.min.js') }}" type="text/javascript"></script>

        <script src="{{ asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}" type="text/javascript"></script>

        <script src="{{ asset('ajax/api.js') }}"></script>

        <script src="{{ asset('js/custom.min.js') }}" type="text/javascript"></script>

        <script src="{{ asset('js/deznav-init.js') }}" type="text/javascript"></script>

        <!-- ERP API Layer -->
        <!-- Login Logic -->
        <script>
        $(document).ready(function () {

            $("#loginForm").on("submit", function (e) {
                e.preventDefault();
                //alert("LOGIN HANDLER FIRED");

                const email = $("#email").val().trim();
                const password = $("#password").val().trim();

                if (!email || !password) {
                    alert("Email and password are required");
                    return;
                }

                apiRequest("POST", "/login", {
                    email: email,
                    password: password
                })
                .done(function (res) {
                    console.log("LOGIN RESPONSE:", res);

                    localStorage.setItem("auth_token", res.access_token);
                    localStorage.setItem("user", JSON.stringify(res.user));

                    console.log("STORED TOKEN:", localStorage.getItem("auth_token"));

                    const role = res.user.role;

                    switch (role) {
                        case "super_admin":
                        case "school_admin":
                            window.location.href = "/admin/dashboard";
                            break;

                        case "teacher":
                            window.location.href = "/teacher/dashboard";
                            break;

                        case "student":
                            window.location.href = "/student/dashboard";
                            break;

                        case "parent":
                            window.location.href = "dashboard-parent.html";
                            break;

                        case "accountant":
                           window.location.href = "dashboard-accountant.html";
                            break;

                        default:
                            alert("Unauthorized role");
                            localStorage.clear();
                            window.location.href = "/login";
                    }
                })

                .fail(function (err) {
                    console.error(err);
                    alert(err.responseJSON?.message || "Login failed");
                });
            });

        });
        </script>

    </body>

</html>