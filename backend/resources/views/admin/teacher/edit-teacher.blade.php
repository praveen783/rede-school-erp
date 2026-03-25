<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<head>	
	<title>Owlio Laravel | Dashboard</title>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="author" content="DexignZone">
	<meta name="robots" content="">
	<meta name="csrf-token" content="7r9S4OG6empHntcP96fML3XOHxxVRRnoFgVf21eo">
	<meta name="keywords" content="bootstrap, courses, education admin template, educational, instructors, learning, learning admin, learning admin theme, learning application, lessons, lms admin template, lms rails, quizzes ui, school admin">
	<meta name="description" content="Some description for the page"/>
	<meta property="og:title" content="Owlio - School Admission Admin Dashboard">
	<meta property="og:description" content="Owlio Laravel | Dashboard" />
	<meta property="og:image" content="../../social-image.png">
	<meta name="format-detection" content="telephone=no">

	<!-- Mobile Specific -->
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon.png') }}">
    <link href="{{ asset('vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        /* =====================================================
           EDIT TEACHER FORM — DARK MODE AWARE STYLES
        ===================================================== */
        .at-page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; }
        .at-page-header .at-breadcrumb { font-size:13px; color:var(--text-muted,#6c757d); }
        .at-page-header .at-breadcrumb a { color:#1e33f2; text-decoration:none; }
        .at-page-header .at-breadcrumb a:hover { text-decoration:underline; }

        #editTeacherCard { border-radius:14px; border:none; box-shadow:0 2px 16px rgba(0,0,0,.08); }
        #editTeacherCard .card-header { border-radius:14px 14px 0 0; padding:1.1rem 1.5rem; display:flex; align-items:center; gap:.75rem; border-bottom:1px solid rgba(128,128,128,.15); }
        #editTeacherCard .card-header .at-icon { width:36px; height:36px; border-radius:8px; background:linear-gradient(135deg,#1e33f2,#1ee5f2); display:flex; align-items:center; justify-content:center; color:#fff; font-size:16px; flex-shrink:0; }
        #editTeacherCard .card-header .card-title { margin:0; font-size:16px; font-weight:700; }

        .at-section-label { font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#1e33f2; padding:.25rem 0 .5rem; border-bottom:2px solid #1e33f220; margin-bottom:1rem; }

        .at-form-grid { display:grid; grid-template-columns:1fr 1fr; gap:0 1.25rem; }
        .at-form-grid .at-col-full { grid-column:1/-1; }
        @media (max-width:640px) { .at-form-grid { grid-template-columns:1fr; } .at-form-grid .at-col-full { grid-column:1; } }

        #editTeacherForm label.form-label { font-size:13px; font-weight:600; margin-bottom:.35rem; display:block; }
        #editTeacherForm .form-control { border-radius:8px; font-size:14px; border:1.5px solid #d0d5dd; padding:9px 13px; background-color:#ffffff; color:#1d2939; transition:border-color .2s,box-shadow .2s,background-color .2s; }
        #editTeacherForm .form-control::placeholder { color:#aab2bd; }
        #editTeacherForm .form-control:focus { border-color:#1e33f2; box-shadow:0 0 0 3px rgba(30,51,242,.12); outline:none; background-color:#ffffff; color:#1d2939; }
        #editTeacherForm textarea.form-control { resize:vertical; min-height:90px; }

        [data-theme="dark"] #editTeacherForm .form-control,
        .dark-mode #editTeacherForm .form-control,
        body.dark #editTeacherForm .form-control { background-color:#1e2139 !important; border-color:#3a3f5c !important; color:#e4e6f0 !important; }
        [data-theme="dark"] #editTeacherForm .form-control::placeholder,
        .dark-mode #editTeacherForm .form-control::placeholder,
        body.dark #editTeacherForm .form-control::placeholder { color:#6b7280 !important; }
        [data-theme="dark"] #editTeacherForm .form-control:focus,
        .dark-mode #editTeacherForm .form-control:focus,
        body.dark #editTeacherForm .form-control:focus { border-color:#4f6bff !important; box-shadow:0 0 0 3px rgba(79,107,255,.18) !important; background-color:#252945 !important; color:#f0f2ff !important; }
        [data-theme="dark"] #editTeacherForm select option,
        .dark-mode #editTeacherForm select option,
        body.dark #editTeacherForm select option { background-color:#1e2139; color:#e4e6f0; }

        .at-form-actions { display:flex; align-items:center; gap:.75rem; padding-top:1.25rem; border-top:1px solid rgba(128,128,128,.12); margin-top:.5rem; }
        .at-form-actions .btn-primary { padding:9px 28px; font-weight:600; border-radius:8px; font-size:14px; background:linear-gradient(135deg,#1e33f2,#1ee5f2); border:none; box-shadow:0 3px 10px rgba(30,51,242,.25); }
        .at-form-actions .btn-light { padding:9px 22px; font-size:14px; font-weight:500; border-radius:8px; border:1.5px solid #d0d5dd; }
        [data-theme="dark"] .at-form-actions .btn-light,
        .dark-mode .at-form-actions .btn-light,
        body.dark .at-form-actions .btn-light { border-color:#3a3f5c; color:#c9cde8; background-color:#1e2139; }
        .at-req { color:#f04438; }

        /* Repeater Cards */
        .at-repeater-card { border:1.5px solid #e4e7ec; border-radius:10px; padding:1.1rem 1.25rem 0.75rem; margin-bottom:1rem; position:relative; background-color:#f9fafb; }
        [data-theme="dark"] .at-repeater-card,
        .dark-mode .at-repeater-card,
        body.dark .at-repeater-card { background-color:#191c34; border-color:#2e3154; }
        .at-repeater-card .at-remove-btn { position:absolute; top:.6rem; right:.75rem; background:none; border:none; color:#f04438; font-size:15px; cursor:pointer; padding:2px 6px; border-radius:5px; }
        .at-repeater-card .at-remove-btn:hover { background:rgba(240,68,56,.08); }
        .at-repeater-card .at-card-title { font-size:12px; font-weight:700; color:#1e33f2; text-transform:uppercase; letter-spacing:.05em; margin-bottom:.85rem; }
        .at-add-btn { background:rgba(30,51,242,.08); color:#1e33f2; border:1.5px dashed #1e33f2; border-radius:7px; font-size:12px; font-weight:600; padding:4px 14px; }
        .at-add-btn:hover { background:rgba(30,51,242,.15); color:#1e33f2; }
        [data-theme="dark"] .at-add-btn,
        .dark-mode .at-add-btn,
        body.dark .at-add-btn { background:rgba(79,107,255,.12); border-color:#4f6bff; color:#8fa3ff; }
        [data-theme="dark"] .at-repeater-card .form-control,
        .dark-mode .at-repeater-card .form-control,
        body.dark .at-repeater-card .form-control { background-color:#1e2139 !important; border-color:#3a3f5c !important; color:#e4e6f0 !important; }
        [data-theme="dark"] .at-repeater-card .form-control::placeholder,
        .dark-mode .at-repeater-card .form-control::placeholder,
        body.dark .at-repeater-card .form-control::placeholder { color:#6b7280 !important; }
        [data-theme="dark"] .at-repeater-card .form-control:focus,
        .dark-mode .at-repeater-card .form-control:focus,
        body.dark .at-repeater-card .form-control:focus { border-color:#4f6bff !important; box-shadow:0 0 0 3px rgba(79,107,255,.18) !important; background-color:#252945 !important; color:#f0f2ff !important; }
    </style>

</head>
<body>

    <!--*******************
        Preloader start
    ********************-->
    <div id="preloader">
        <div class="sk-three-bounce">
            <div class="sk-child sk-bounce1"></div>
            <div class="sk-child sk-bounce2"></div>
            <div class="sk-child sk-bounce3"></div>
        </div>
    </div>
    <!--*******************
        Preloader end
    ********************-->

    <!--**********************************
        Main wrapper start
    ***********************************-->
    <div id="main-wrapper">

        <!--**********************************
            Nav header start
        ***********************************-->
        <div class="nav-header">
            <a href="{{ url('/admin/dashboard') }}" class="brand-logo">
                <svg class="logo-abbr" width="54" height="54" viewBox="0 0 54 54" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect class="svg-logo-rect"  width="54" height="54" rx="27" fill="url(#paint0_linear)"/>
					<path  d="M23.7487 23.6736C23.7487 25.0896 22.5961 26.2416 21.1793 26.2416C19.764 26.2416 18.6124 25.0896 18.6124 23.6736C18.6124 22.2567 19.764 21.1041 21.1793 21.1041C22.5961 21.1041 23.7487 22.2567 23.7487 23.6736ZM32.8168 21.1042C31.4015 21.1042 30.2499 22.2569 30.2499 23.6737C30.2499 25.0897 31.4015 26.2417 32.8168 26.2417C34.2336 26.2417 35.3862 25.0897 35.3862 23.6737C35.3862 22.2569 34.2336 21.1042 32.8168 21.1042ZM26.0079 36.8042L26.0286 42.6348C24.5259 42.6975 23.4593 42.5364 23.4593 42.5364V36.8055C23.4593 36.2557 23.013 35.8094 22.4632 35.8094C21.9133 35.8094 21.4671 36.2557 21.4671 36.8055V42.0574C18.1887 40.9111 15.8626 38.1857 15.852 35.0374V27.7139C14.9984 26.5905 14.491 25.1903 14.491 23.6736C14.491 22.3807 14.8599 21.1726 15.4973 20.1483L15.4931 12.3799C15.6563 11.1516 16.7925 11.3617 16.7925 11.3617L23.1379 13.9239C24.3426 13.4452 25.6554 13.1819 27.0287 13.1819C28.3907 13.1819 29.6932 13.4411 30.8898 13.9127L37.2075 11.3617C37.2075 11.3617 38.3438 11.1516 38.5069 12.3799L38.5028 20.1486C39.1402 21.1729 39.5091 22.3809 39.5091 23.6736C39.5091 25.1893 39.0022 26.5886 38.1495 27.7117V35.1389C38.155 36.9361 37.4102 38.6757 36.0524 40.0375C36.0524 40.0375 34.7582 41.4527 32.533 42.1947V36.8055C32.533 36.2557 32.0874 35.8094 31.5369 35.8094C30.9871 35.8094 30.5408 36.2557 30.5408 36.8055V42.605C29.8565 42.6794 28.0202 42.6348 28.0202 42.6348L28.0001 36.8068C28.0008 36.257 27.5552 35.8101 27.0054 35.8094C27.0053 35.8094 26.1004 35.8061 26.0079 36.8042ZM25.8788 23.6736C25.8788 21.0829 23.7706 18.9752 21.1793 18.9752C18.5898 18.9752 16.4831 21.0829 16.4831 23.6736C16.4831 26.2642 18.5898 28.3719 21.1793 28.3719C23.7706 28.3719 25.8788 26.2642 25.8788 23.6736ZM27.8489 32.902L30.6503 30.0032C29.097 29.4697 27.8002 28.3799 26.999 26.9729C26.207 28.364 24.9304 29.4448 23.4006 29.9846L26.5748 32.9355C26.5748 32.9355 27.1871 33.4418 27.8489 32.902ZM37.5169 23.6736C37.5169 21.0829 35.4097 18.9752 32.8196 18.9752C30.2278 18.9752 28.1192 21.0829 28.1192 23.6736C28.1192 26.2642 30.2278 28.3719 32.8196 28.3719C35.4097 28.3719 37.5169 26.2642 37.5169 23.6736Z" fill="white"/>
					<defs>
					<linearGradient id="paint0_linear" x1="27" y1="0" x2="45.6923" y2="64.9038" gradientUnits="userSpaceOnUse">
					<stop offset="0" stop-color="#1E33F2"/>
					<stop offset="1" stop-color="#1EE5F2"/>
					</linearGradient>
					</defs>
				</svg>
				<svg class="brand-title" width="88" height="26" viewBox="0 0 88 26" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path class="svg-logo-title" d="M8.98401 25.3839C7.29868 25.3839 5.78401 25.0106 4.44001 24.2639C3.09601 23.5173 2.02935 22.4933 1.24001 21.1919C0.472013 19.8906 0.0880127 18.4293 0.0880127 16.8079C0.0880127 15.1653 0.472013 13.7039 1.24001 12.4239C2.02935 11.1226 3.09601 10.0986 4.44001 9.35195C5.78401 8.58395 7.29868 8.19995 8.98401 8.19995C10.6693 8.19995 12.1733 8.58395 13.496 9.35195C14.84 10.0986 15.896 11.1226 16.664 12.4239C17.4533 13.7039 17.848 15.1653 17.848 16.8079C17.848 18.4293 17.4533 19.8906 16.664 21.1919C15.896 22.4933 14.84 23.5173 13.496 24.2639C12.152 25.0106 10.648 25.3839 8.98401 25.3839ZM8.98401 21.0639C9.77335 21.0639 10.456 20.872 11.032 20.4879C11.608 20.1039 12.056 19.5919 12.376 18.9519C12.696 18.3119 12.856 17.5866 12.856 16.7759C12.856 15.9866 12.696 15.2719 12.376 14.6319C12.056 13.9919 11.608 13.4799 11.032 13.0959C10.456 12.7119 9.77335 12.5199 8.98401 12.5199C8.19468 12.5199 7.50135 12.7119 6.90401 13.0959C6.32801 13.4799 5.88001 13.9919 5.56001 14.6319C5.24001 15.2719 5.08001 15.9866 5.08001 16.7759C5.08001 17.5866 5.24001 18.3119 5.56001 18.9519C5.88001 19.5919 6.32801 20.1039 6.90401 20.4879C7.50135 20.872 8.19468 21.0639 8.98401 21.0639Z" fill="#40415C"/>
					<path  class="svg-logo-title" d="M27.1615 25.3839C26.0308 25.3839 24.9748 25.1599 23.9935 24.7119C23.0335 24.2639 22.2548 23.5813 21.6575 22.6639C21.0815 21.7466 20.7935 20.5946 20.7935 19.2079V8.58395H25.7535V19.8479C25.7535 20.1039 25.8175 20.3386 25.9455 20.5519C26.0735 20.7653 26.2442 20.9359 26.4575 21.0639C26.6708 21.1919 26.9055 21.2559 27.1615 21.2559C27.4175 21.2559 27.6522 21.1919 27.8655 21.0639C28.0788 20.9359 28.2495 20.7653 28.3775 20.5519C28.5055 20.3386 28.5695 20.1039 28.5695 19.8479V14.3759C28.5695 13.0533 28.8468 11.9333 29.4015 11.0159C29.9562 10.0986 30.7135 9.40528 31.6735 8.93595C32.6548 8.44528 33.7535 8.19995 34.9695 8.19995C36.1855 8.19995 37.2735 8.44528 38.2335 8.93595C39.1935 9.40528 39.9508 10.0986 40.5055 11.0159C41.0602 11.9333 41.3375 13.0533 41.3375 14.3759V19.8479C41.3375 20.1039 41.4015 20.3386 41.5295 20.5519C41.6575 20.7653 41.8282 20.9359 42.0415 21.0639C42.2762 21.1919 42.5215 21.2559 42.7775 21.2559C43.0335 21.2559 43.2682 21.1919 43.4815 21.0639C43.6948 20.9359 43.8655 20.7653 43.9935 20.5519C44.1215 20.3386 44.1855 20.1039 44.1855 19.8479V8.58395H49.1455V19.2079C49.1455 20.5946 48.8468 21.7466 48.2495 22.6639C47.6735 23.5813 46.8948 24.2639 45.9135 24.7119C44.9535 25.1599 43.9082 25.3839 42.7775 25.3839C41.6468 25.3839 40.5908 25.1599 39.6095 24.7119C38.6495 24.2639 37.8708 23.5813 37.2735 22.6639C36.6762 21.7466 36.3775 20.5946 36.3775 19.2079V13.7679C36.3775 13.4906 36.3135 13.2453 36.1855 13.0319C36.0575 12.8186 35.8868 12.6479 35.6735 12.5199C35.4602 12.3919 35.2255 12.3279 34.9695 12.3279C34.7135 12.3279 34.4682 12.3919 34.2335 12.5199C34.0202 12.6479 33.8495 12.8186 33.7215 13.0319C33.5935 13.2453 33.5295 13.4906 33.5295 13.7679V19.2079C33.5295 20.5946 33.2308 21.7466 32.6335 22.6639C32.0575 23.5813 31.2788 24.2639 30.2975 24.7119C29.3375 25.1599 28.2922 25.3839 27.1615 25.3839Z" fill="#40415C"/>
					<path  class="svg-logo-title" d="M52.856 24.9999V1.63995H57.816V24.9999H52.856Z" fill="#40415C"/>
					<path  class="svg-logo-title" d="M61.481 24.9999V8.58395H66.473V24.9999H61.481ZM63.977 6.72795C63.1877 6.72795 62.505 6.43995 61.929 5.86395C61.353 5.28795 61.065 4.60528 61.065 3.81595C61.065 3.02661 61.353 2.34395 61.929 1.76795C62.505 1.17061 63.1877 0.871948 63.977 0.871948C64.7663 0.871948 65.449 1.17061 66.025 1.76795C66.601 2.34395 66.889 3.02661 66.889 3.81595C66.889 4.60528 66.601 5.28795 66.025 5.86395C65.449 6.43995 64.7663 6.72795 63.977 6.72795Z" fill="#40415C"/>
					<path  class="svg-logo-title" d="M78.234 25.3839C76.5487 25.3839 75.034 25.0106 73.69 24.2639C72.346 23.5173 71.2794 22.4933 70.49 21.1919C69.722 19.8906 69.338 18.4293 69.338 16.8079C69.338 15.1653 69.722 13.7039 70.49 12.4239C71.2794 11.1226 72.346 10.0986 73.69 9.35195C75.034 8.58395 76.5487 8.19995 78.234 8.19995C79.9193 8.19995 81.4233 8.58395 82.746 9.35195C84.09 10.0986 85.146 11.1226 85.914 12.4239C86.7034 13.7039 87.098 15.1653 87.098 16.8079C87.098 18.4293 86.7034 19.8906 85.914 21.1919C85.146 22.4933 84.09 23.5173 82.746 24.2639C81.402 25.0106 79.898 25.3839 78.234 25.3839ZM78.234 21.0639C79.0233 21.0639 79.706 20.872 80.282 20.4879C80.858 20.1039 81.306 19.5919 81.626 18.9519C81.946 18.3119 82.106 17.5866 82.106 16.7759C82.106 15.9866 81.946 15.2719 81.626 14.6319C81.306 13.9919 80.858 13.4799 80.282 13.0959C79.706 12.7119 79.0233 12.5199 78.234 12.5199C77.4447 12.5199 76.7514 12.7119 76.154 13.0959C75.578 13.4799 75.13 13.9919 74.81 14.6319C74.49 15.2719 74.33 15.9866 74.33 16.7759C74.33 17.5866 74.49 18.3119 74.81 18.9519C75.13 19.5919 75.578 20.1039 76.154 20.4879C76.7514 20.872 77.4447 21.0639 78.234 21.0639Z" fill="#40415C"/>
				</svg>
            </a>

            <div class="nav-control">
                <div class="hamburger">
                    <span class="line"></span><span class="line"></span><span class="line"></span>
                </div>
            </div>
        </div>
        <!--**********************************
            Nav header end
        ***********************************-->
		

<!--**********************************
    Header start
***********************************-->
<div class="header">
    <div class="header-content">
        <nav class="navbar navbar-expand">
            <div class="collapse navbar-collapse justify-content-between">
                <div class="header-left">
                    <div class="dashboard_bar">
                        <div class="input-group search-area d-lg-inline-flex d-none me-5">
                          <span class="input-group-text" id="header-search">
                                <a href="javascript:void(0);">
                                    <i class="flaticon-381-search-2"></i>
                                </a>
                          </span>
                          <input type="text" class="form-control" placeholder="Search here" aria-label="Username" aria-describedby="header-search">
                        </div>

                    </div>
                </div>
                <ul class="navbar-nav header-right">
                    <li class="nav-item dropdown notification_dropdown">
                          <a class="nav-link bell dz-theme-mode" href="javascript:void(0);">
                            <i id="icon-light" class="fas fa-sun"></i>
                             <i id="icon-dark" class="fas fa-moon"></i>
                                    
                          </a>
                    </li>
                    
                    <li class="nav-item dropdown header-profile">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="header-info">
                                <span id="headerUserName">--</span>
                                <small id="headerUserRole">--</small>
                            </div>
                            <i class="fa fa-caret-down ms-3 me-2" aria-hidden="true"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="app-profile.html" class="dropdown-item ai-icon">
                                <span class="ms-2">Profile</span>
                            </a>

                            <a href="email-inbox.html" class="dropdown-item ai-icon">
                                <span class="ms-2">Inbox</span>
                            </a>

                            <a href="#" id="logoutBtn" class="dropdown-item ai-icon">
                                <span class="ms-2">Logout</span>
                            </a>
                        </div>
                    </li>

                    <li class="nav-item lenguage-btn">
                        <select class="form-control btn-head default-select me-3">
                            <option>EN</option>
                            <option>SP</option>
                            <option>GER</option>
                            <option>FREN</option>
                        </select>
                    </li>	
                </ul>
            </div>
        </nav>
    </div>
</div> 
        <!--**********************************
            Header end ti-comment-alt
        ***********************************-->

        <!--**********************************
            Sidebar start
        ***********************************-->

    <div class="deznav">
        <div class="deznav-scroll">
            <ul class="metismenu" id="menu">
                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-025-dashboard"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                    <ul aria-expanded="false">
                        <li><a href="{{ url('/admin/students') }}">Students</a></li>
                        <li><a href="{{ url('/admin/teachers') }}">Teacher</a></li>
                        <li><a href="{{ url('/admin/parents') }}">Parents</a></li>
                        <li><a href="{{ url('/admin/accountant/dashboard') }}">Accountant</a></li>
                    </ul>
                </li>

                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="flaticon-025-dashboard"></i>
                            <span class="nav-text">Academic</span>
                        </a>
                        <ul aria-expanded="false"> 
                            <li><a href="{{ url('/admin/academic/classes') }}">Classes</a></li>
                            <li><a href="{{ url('/admin/academic/subjects') }}">Subjects</a></li>
                            <li><a href="{{ url('/admin/academic/teacher/assignment') }}">Assignments</a></li>
                            <li><a href="{{ url('/admin/academic/attendance') }}">Attendance</a></li>
                            <li><a href="{{ url('/admin/academic/syllabus') }}">Syllabus</a></li>
                            <li><a href="{{ url('/admin/academic/academic-years') }}">Academic Years</a></li>
                            <li><a href="{{ url('/admin/academic/timetable') }}">Timetable</a></li>
                        </ul>
                </li>

                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="flaticon-025-dashboard"></i>
                            <span class="nav-text">Examinations</span>
                        </a>
                        <ul aria-expanded="false">
                            
                            <li><a href="{{ url('/admin/examinations/exams') }}">Exams</a></li>
                            <li><a href="{{ url('/admin/examinations/admit-cards') }}">Admit Cards</a></li>
                            <li><a href="{{ url('/admin/examinations/results') }}">Results</a></li>
                            <li><a href="{{ url('/admin/examinations/exams/1/marks-entry') }}">Marks Entry</a></li>
                            <li><a href="{{ url('/admin/examinations/report-cards') }}">Report Cards</a></li>
                            <li><a href="{{ url('/admin/examinations/promotions') }}">Promotions</a></li>
                            

                        </ul>

                </li>
                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                            <i class="flaticon-025-dashboard"></i>
                            <span class="nav-text">Fee</span>
                        </a>
                        <ul aria-expanded="false">
                            
                            <li><a href="{{ url('/admin/fee/fee-heads') }}">Fee Heads</a></li>
                            <li><a href="{{ url('/admin/fee/fee-structure') }}">Fee Structure</a></li>
                            <li><a href="{{ url('/admin/fee/fee-assign-adhoc') }}">Assign Special Fee</a></li>
                            <li><a href="{{ url('/admin/fee/fee-payment') }}">Fee Payment</a></li>
                            <li><a href="{{ url('/admin/fee/fee-receipts') }}">Fee Receipts</a></li>
                            <li><a href="{{ url('/admin/fee/fee-reminder') }}">Fee Reminder</a></li>
                            <li><a href="{{ url('/admin/fee/fee-report') }}">Fee Report</a></li>
                                        

                        </ul>

                    </li>
                <li><a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">
                        <i class="flaticon-022-copy"></i>
                        <span class="nav-text">Pages</span>
                    </a>
                    <<ul aria-expanded="false">
                            <li><a href="{{ url('/pages/register') }}">Register</a></li>
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <li><a class="has-arrow" href="javascript:void()" aria-expanded="false">Error</a>
                                <ul aria-expanded="false">
                                    <li><a href="{{ url('/error/400') }}">Error 400</a></li>
                                    <li><a href="{{ url('/error/403') }}">Error 403</a></li>
                                    <li><a href="{{ url('/error/404') }}">Error 404</a></li>
                                    <li><a href="{{ url('/error/500') }}">Error 500</a></li>
                                    <li><a href="{{ url('/error/503') }}">Error 503</a></li>
                                </ul>
                            </li>
                            <li><a href="{{ url('/pages/lock-screen') }}">Lock Screen</a></li>
                        </ul>
                </li>
            </ul>
            <div class="drum-box">
                <img src="public/images/ellipse5.png" alt="">
                <p class="fs-18 font-w500 mb-4">Auto Generate Admission Report</p>
                <a class="" href="javascript:void(0);"><i class="fa fa-long-arrow-right"></i>
                </a>
            </div>
            <div class="copyright">
                <p><strong>Owlio School Admission Admin </strong> © 2023 All Rights Reserved</p>
                <p class="fs-12">Made with <span class="heart"></span> by DexignZone</p>
            </div>
        </div>
    </div>		
        <!--**********************************
            Sidebar end
		***********************************-->

        <!-- !--********************************** -->
            <!-- Content body start here  -->
        <!-- !--********************************** -->

        <div class="content-body">
        <div class="container-fluid">

            <div class="row">
    <div class="col-xl-9 col-lg-10 col-md-12 mx-auto">

        <!-- Page Header -->
        <div class="at-page-header">
            <div>
                <h4 class="mb-1" style="font-weight:700;">Edit Teacher</h4>
                <div class="at-breadcrumb">
                    <a href="{{ url('/admin/dashboard') }}">Dashboard</a>
                    <span class="mx-1">/</span>
                    <a href="{{ url('/admin/teachers') }}">Teachers</a>
                    <span class="mx-1">/</span>
                    <span>Edit</span>
                </div>
            </div>
        </div>

        <div class="card" id="editTeacherCard">
            <div class="card-header">
                <div class="at-icon"><i class="fa fa-user-edit"></i></div>
                <h4 class="card-title">Teacher Information</h4>
            </div>

            <div class="card-body" style="padding:1.75rem;">
                <form id="editTeacherForm">

                    <!-- ===== SECTION: Basic Info ===== -->
                    <div class="at-section-label">Basic Information</div>
                    <div class="at-form-grid mb-3">
                        <div class="mb-3">
                            <label class="form-label">Teacher Name <span class="at-req">*</span></label>
                            <input type="text" class="form-control" id="teacherName" placeholder="Full name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Employee Code <span class="at-req">*</span></label>
                            <input type="text" class="form-control" id="employeeCode" placeholder="e.g. EMP001" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="teacherEmail" placeholder="teacher@school.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="teacherPhone" placeholder="+91 00000 00000">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-control" id="teacherGender">
                                <option value="">— Select gender —</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Status</label>
                            <select class="form-control" id="teacherStatus">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <!-- ===== SECTION: Personal Details ===== -->
                    <div class="at-section-label">Personal Details</div>
                    <div class="at-form-grid mb-3">
                        <div class="mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="dateOfBirth">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date of Joining</label>
                            <input type="date" class="form-control" id="dateOfJoining">
                        </div>
                        <div class="mb-3 at-col-full">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="teacherAddress" rows="3" placeholder="Street, City, State, PIN"></textarea>
                        </div>
                    </div>

                    <!-- ===== SECTION: Teaching Subjects ===== -->
                    <div class="at-section-label">Teaching Subjects</div>
                    <div class="at-form-grid mb-3">
                        <div class="mb-3">
                            <label class="form-label">Primary Subject</label>
                            <input type="text" class="form-control" id="primarySubject" placeholder="e.g. Mathematics">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Secondary Subject</label>
                            <input type="text" class="form-control" id="secondarySubject" placeholder="e.g. Science">
                        </div>
                        <div class="mb-3 at-col-full">
                            <label class="form-label">Assigned Subjects <span class="at-req">*</span></label>
                            <select id="subjectsSelect" class="form-control default-select" multiple data-live-search="true" title="Select Subjects"></select>
                        </div>
                    </div>

                    <!-- ===== SECTION: Education Qualifications ===== -->
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="at-section-label mb-0" style="flex:1;">Education Qualifications</div>
                        <button type="button" class="btn btn-sm at-add-btn" id="addEducationBtn">
                            <i class="fa fa-plus me-1"></i> Add Degree
                        </button>
                    </div>
                    <div id="educationsList"></div>

                    <!-- ===== SECTION: Work Experience ===== -->
                    <div class="d-flex align-items-center justify-content-between mb-2 mt-3">
                        <div class="at-section-label mb-0" style="flex:1;">Work Experience</div>
                        <button type="button" class="btn btn-sm at-add-btn" id="addExperienceBtn">
                            <i class="fa fa-plus me-1"></i> Add Experience
                        </button>
                    </div>
                    <div id="experiencesList"></div>

                    <!-- ===== ACTION BUTTONS ===== -->
                    <div class="at-form-actions mt-3">
                        <button type="submit" id="updateTeacherBtn" class="btn btn-primary">
                            <i class="fa fa-save me-2"></i> Save Changes
                        </button>
                        <a href="{{ url('/admin/teachers') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

        </div>
    </div>

    <!-- Content body start here  -->
    
        <!-- 			
		**********************************
		Footer start
		*********************************** -->
		
	<div class="footer">
		<div class="copyright">
			<p>Copyright © Designed &amp; Developed by <a href="http://dexignzone.com/" target="_blank">DexignZone</a> 2023</p>
		</div>
    </div>

        <!--**********************************
            Footer end
        ***********************************-->

		

	</div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <!--**********************************
        Scripts
    ***********************************-->

     <!-- Scripts -->
    <script src="{{ asset('vendor/global/global.min.js') }}" type="text/javascript"></script>

	<script src="{{ asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}" type="text/javascript"></script>

	<script src="{{ asset('vendor/bootstrap-datetimepicker/js/moment.js') }}" type="text/javascript"></script>

	<script src="{{ asset('vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') }}" type="text/javascript"></script>

	<script src="{{ asset('vendor/peity/jquery.peity.min.js') }}" type="text/javascript"></script>

	<script src="{{ asset('vendor/apexchart/apexchart.js') }}" type="text/javascript"></script>

	<script src="{{ asset('js/dashboard/dashboard-1.js') }}" type="text/javascript"></script>

	<script src="{{ asset('js/custom.min.js') }}" type="text/javascript"></script>

	<script src="{{ asset('js/deznav-init.js') }}" type="text/javascript"></script>

	<script src="{{ asset('ajax/api.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const token = localStorage.getItem("auth_token");
    const user  = JSON.parse(localStorage.getItem("user"));

    if (!token || !user) {
        window.location.href = "{{ url('/login') }}";
        return;
    }

    // Load header user info
    apiRequest("GET", "/me")
        .done(function (res) {
            document.getElementById("headerUserName").innerText = res.name ?? "Admin";
            const roleMap = { super_admin:"Super Admin", school_admin:"School Admin", teacher:"Teacher" };
            document.getElementById("headerUserRole").innerText = roleMap[res.role] ?? res.role;
        })
        .fail(function () {
            alert("Session expired");
            window.location.href = "{{ url('/login') }}";
        });

    // Get teacher ID from URL
    const pathParts = window.location.pathname.split("/");
    const teacherId = pathParts[pathParts.indexOf("teachers") + 1];
    if (!teacherId) {
        alert("Invalid teacher ID");
        window.location.href = "{{ url('/admin/teachers') }}";
        return;
    }

    // Attach repeater button listeners
    document.getElementById("addEducationBtn").addEventListener("click", function () { addEducationRow(); });
    document.getElementById("addExperienceBtn").addEventListener("click", function () { addExperienceRow(); });

    // Load subjects then teacher
    loadSubjects().then(function () { loadTeacher(teacherId); });

    // Logout
    document.getElementById("logoutBtn").addEventListener("click", function (e) {
        e.preventDefault();
        apiRequest("POST", "/logout").always(function () {
            localStorage.clear();
            window.location.href = "{{ route('login') }}";
        });
    });

    // ================================
    // FORM SUBMIT
    // ================================
    document.getElementById("editTeacherForm").addEventListener("submit", function (e) {
        e.preventDefault();

        const btn = document.getElementById("updateTeacherBtn");

        // Collect educations
        const educations = [];
        document.querySelectorAll("#educationsList .at-repeater-card").forEach(function (card) {
            const degree = card.querySelector(".edu-degree").value.trim();
            const institution = card.querySelector(".edu-institution").value.trim();
            if (!degree || !institution) return;
            educations.push({
                degree:              degree,
                field_of_study:      card.querySelector(".edu-field").value.trim() || null,
                institution:         institution,
                board_or_university: card.querySelector(".edu-board").value.trim() || null,
                passing_year:        card.querySelector(".edu-year").value || null,
                result:              card.querySelector(".edu-result").value || null,
                percentage:          card.querySelector(".edu-percentage").value || null,
                grade:               card.querySelector(".edu-grade").value.trim() || null,
            });
        });

        // Collect experiences
        const experiences = [];
        document.querySelectorAll("#experiencesList .at-repeater-card").forEach(function (card) {
            const org   = card.querySelector(".exp-org").value.trim();
            const desig = card.querySelector(".exp-desig").value.trim();
            if (!org || !desig) return;
            const isCurr = card.querySelector(".exp-current").checked;
            experiences.push({
                organization:     org,
                designation:      desig,
                department:       card.querySelector(".exp-dept").value.trim() || null,
                from_date:        card.querySelector(".exp-from").value || null,
                to_date:          isCurr ? null : (card.querySelector(".exp-to").value || null),
                is_current:       isCurr ? 1 : 0,
                responsibilities: card.querySelector(".exp-resp").value.trim() || null,
                leaving_reason:   isCurr ? null : (card.querySelector(".exp-reason").value.trim() || null),
            });
        });

        const payload = {
            name:              document.getElementById("teacherName").value.trim(),
            employee_code:     document.getElementById("employeeCode").value.trim(),
            email:             document.getElementById("teacherEmail").value.trim(),
            phone:             document.getElementById("teacherPhone").value.trim(),
            gender:            document.getElementById("teacherGender").value,
            date_of_joining:   document.getElementById("dateOfJoining").value || null,
            date_of_birth:     document.getElementById("dateOfBirth").value || null,
            address:           document.getElementById("teacherAddress").value.trim() || null,
            primary_subject:   document.getElementById("primarySubject").value.trim() || null,
            secondary_subject: document.getElementById("secondarySubject").value.trim() || null,
            subjects:          $('#subjectsSelect').val() || [],
            is_active:         document.getElementById("teacherStatus").value,
            educations:        educations,
            experiences:       experiences,
        };

        if (!payload.name || !payload.employee_code) {
            alert("Name and Employee Code are required");
            return;
        }
        if (payload.subjects.length === 0) {
            alert("Please select at least one subject");
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Saving...';

        apiRequest("PUT", `/teachers/${teacherId}`, payload)
            .done(function () {
                alert("Teacher updated successfully");
                window.location.href = "{{ url('/admin/teachers') }}";
            })
            .fail(function (err) {
                console.error(err);
                alert(err.responseJSON?.message || "Update failed");
            })
            .always(function () {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-2"></i> Save Changes';
            });
    });

});

// ================================
// LOAD SUBJECTS
// ================================
function loadSubjects() {
    return apiRequest("GET", "/subjects")
        .done(function (res) {
            const select   = document.getElementById("subjectsSelect");
            select.innerHTML = "";
            const subjects = res.data ?? res;
            if (!subjects.length) {
                select.innerHTML = `<option disabled>No subjects available</option>`;
                $('.default-select').selectpicker('refresh');
                return;
            }
            subjects.forEach(function (subject) {
                const opt = document.createElement("option");
                opt.value = subject.id;
                opt.textContent = subject.name;
                select.appendChild(opt);
            });
            $('.default-select').selectpicker('refresh');
        })
        .fail(function () { alert("Failed to load subjects"); });
}

// ================================
// LOAD TEACHER DATA (populates form + repeaters)
// ================================
function loadTeacher(id) {
    apiRequest("GET", `/teachers/${id}`)
        .done(function (teacher) {
            document.getElementById("teacherName").value      = teacher.name ?? "";
            document.getElementById("employeeCode").value     = teacher.employee_code ?? "";
            document.getElementById("teacherEmail").value     = teacher.email ?? "";
            document.getElementById("teacherPhone").value     = teacher.phone ?? "";
            document.getElementById("teacherGender").value    = teacher.gender ?? "";
            document.getElementById("dateOfJoining").value    = teacher.date_of_joining ?? "";
            document.getElementById("dateOfBirth").value      = teacher.date_of_birth ?? "";
            document.getElementById("primarySubject").value   = teacher.primary_subject ?? "";
            document.getElementById("secondarySubject").value = teacher.secondary_subject ?? "";
            document.getElementById("teacherAddress").value   = teacher.address ?? "";
            document.getElementById("teacherStatus").value    = teacher.is_active ? "1" : "0";

            // Subjects
            if (teacher.subjects) {
                $('#subjectsSelect').val(teacher.subjects.map(s => s.id));
                $('.default-select').selectpicker('refresh');
            }

            // Education repeater
            document.getElementById("educationsList").innerHTML = "";
            (teacher.educations || []).forEach(function (edu) {
                addEducationRow(edu);
            });

            // Experience repeater
            document.getElementById("experiencesList").innerHTML = "";
            (teacher.experiences || []).forEach(function (exp) {
                addExperienceRow(exp);
            });
        })
        .fail(function () {
            alert("Failed to load teacher");
            window.location.href = "{{ url('/admin/teachers') }}";
        });
}

// ================================
// EDUCATION REPEATER
// ================================
let eduCount = 0;
function addEducationRow(data) {
    eduCount++;
    const idx = eduCount;
    const d = data || {};
    const degreeOptions = ["SSC / 10th","HSC / 12th","Diploma","B.A","B.Sc","B.Com","B.Tech / B.E","B.Ed","M.A","M.Sc","M.Com","M.Tech / M.E","M.Ed","MBA","Ph.D","Other"];
    const resultOptions = ["Distinction","First Class","Second Class","Pass Class"];
    const degOpts = degreeOptions.map(v => `<option value="${v}" ${d.degree===v?'selected':''}>${v}</option>`).join('');
    const resOpts = resultOptions.map(v => `<option value="${v}" ${d.result===v?'selected':''}>${v}</option>`).join('');
    const html = `
    <div class="at-repeater-card" id="edu-row-${idx}">
        <button type="button" class="at-remove-btn" onclick="removeRow('edu-row-${idx}')"><i class="fa fa-times"></i></button>
        <div class="at-card-title">Degree #${idx}</div>
        <div class="at-form-grid">
            <div class="mb-3">
                <label class="form-label">Degree / Certificate <span class="at-req">*</span></label>
                <select class="form-control edu-degree"><option value="">— Select degree —</option>${degOpts}</select>
            </div>
            <div class="mb-3">
                <label class="form-label">Field of Study / Specialization</label>
                <input type="text" class="form-control edu-field" placeholder="e.g. Mathematics" value="${d.field_of_study||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Institution / College Name <span class="at-req">*</span></label>
                <input type="text" class="form-control edu-institution" placeholder="e.g. Delhi University" value="${d.institution||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Board / University</label>
                <input type="text" class="form-control edu-board" placeholder="e.g. Mumbai University" value="${d.board_or_university||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Passing Year</label>
                <input type="number" class="form-control edu-year" min="1950" max="2100" placeholder="e.g. 2018" value="${d.passing_year||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Result / Class</label>
                <select class="form-control edu-result"><option value="">— Select result —</option>${resOpts}</select>
            </div>
            <div class="mb-3">
                <label class="form-label">Percentage (%)</label>
                <input type="number" class="form-control edu-percentage" min="0" max="100" step="0.01" placeholder="e.g. 78.50" value="${d.percentage||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Grade</label>
                <input type="text" class="form-control edu-grade" placeholder="e.g. A+, O, B" value="${d.grade||''}">
            </div>
        </div>
    </div>`;
    document.getElementById("educationsList").insertAdjacentHTML("beforeend", html);
}

// ================================
// EXPERIENCE REPEATER
// ================================
let expCount = 0;
function addExperienceRow(data) {
    expCount++;
    const idx = expCount;
    const d = data || {};
    const isCurrent = d.is_current ? 'checked' : '';
    const toDisabled = d.is_current ? 'disabled' : '';
    const html = `
    <div class="at-repeater-card" id="exp-row-${idx}">
        <button type="button" class="at-remove-btn" onclick="removeRow('exp-row-${idx}')"><i class="fa fa-times"></i></button>
        <div class="at-card-title">Experience #${idx}</div>
        <div class="at-form-grid">
            <div class="mb-3">
                <label class="form-label">Organization / School Name <span class="at-req">*</span></label>
                <input type="text" class="form-control exp-org" placeholder="e.g. Delhi Public School" value="${d.organization||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Designation / Role <span class="at-req">*</span></label>
                <input type="text" class="form-control exp-desig" placeholder="e.g. Senior Teacher, HOD" value="${d.designation||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" class="form-control exp-dept" placeholder="e.g. Science Department" value="${d.department||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control exp-from" value="${d.from_date||''}">
            </div>
            <div class="mb-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control exp-to" value="${d.to_date||''}" ${toDisabled}>
            </div>
            <div class="mb-3 d-flex align-items-center gap-2 pt-4">
                <input type="checkbox" class="exp-current form-check-input mt-0" id="expCurrent${idx}" ${isCurrent}
                    onchange="toggleToDate(this, ${idx})">
                <label class="form-check-label mb-0" for="expCurrent${idx}" style="font-weight:600;font-size:13px;">Currently Working Here</label>
            </div>
            <div class="mb-3 at-col-full">
                <label class="form-label">Key Responsibilities</label>
                <textarea class="form-control exp-resp" rows="2" placeholder="Brief description...">${d.responsibilities||''}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Reason for Leaving</label>
                <input type="text" class="form-control exp-reason" placeholder="e.g. Better opportunity" value="${d.leaving_reason||''}" ${isCurrent?'disabled':''}>
            </div>
        </div>
    </div>`;
    document.getElementById("experiencesList").insertAdjacentHTML("beforeend", html);
}

// ================================
// REMOVE ROW
// ================================
function removeRow(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
}

// ================================
// TOGGLE TO DATE
// ================================
function toggleToDate(checkbox, idx) {
    const card   = checkbox.closest(".at-repeater-card");
    const toDate = card.querySelector(".exp-to");
    const reason = card.querySelector(".exp-reason");
    if (checkbox.checked) {
        toDate.value = "";
        toDate.disabled = true;
        if (reason) { reason.value = ""; reason.disabled = true; }
    } else {
        toDate.disabled = false;
        if (reason) reason.disabled = false;
    }
}
</script>
    


</body>

</html>

