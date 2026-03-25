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
           TEACHER PROFILE — DISPLAY STYLES + DARK MODE
        ===================================================== */
        .tp-profile-card { border-radius:14px; border:none; box-shadow:0 2px 16px rgba(0,0,0,.08); margin-bottom:1.5rem; }
        .tp-profile-card .card-header { border-radius:14px 14px 0 0; padding:1rem 1.5rem; display:flex; align-items:center; gap:.75rem; border-bottom:1px solid rgba(128,128,128,.15); }
        .tp-profile-card .card-header .at-icon { width:34px; height:34px; border-radius:8px; background:linear-gradient(135deg,#1e33f2,#1ee5f2); display:flex; align-items:center; justify-content:center; color:#fff; font-size:14px; flex-shrink:0; }
        .tp-profile-card .card-header .card-title { margin:0; font-size:15px; font-weight:700; }

        .tp-info-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:0 1.5rem; }
        .tp-info-grid .tp-col-full { grid-column:1/-1; }
        @media (max-width:640px) { .tp-info-grid { grid-template-columns:1fr; } .tp-info-grid .tp-col-full { grid-column:1; } }

        .tp-info-item { margin-bottom:1rem; }
        .tp-info-item .tp-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:.2rem; }
        .tp-info-item .tp-value { font-size:14px; font-weight:500; color:#1d2939; }
        [data-theme="dark"] .tp-info-item .tp-value,
        .dark-mode .tp-info-item .tp-value,
        body.dark .tp-info-item .tp-value { color:#e4e6f0; }

        /* Education / Experience timeline cards */
        .tp-timeline-card { border:1.5px solid #e4e7ec; border-radius:10px; padding:1rem 1.25rem; margin-bottom:.85rem; background:#f9fafb; position:relative; }
        [data-theme="dark"] .tp-timeline-card,
        .dark-mode .tp-timeline-card,
        body.dark .tp-timeline-card { background:#191c34; border-color:#2e3154; }
        .tp-timeline-card .tc-title { font-size:14px; font-weight:700; color:#1d2939; margin-bottom:.2rem; }
        [data-theme="dark"] .tp-timeline-card .tc-title,
        .dark-mode .tp-timeline-card .tc-title,
        body.dark .tp-timeline-card .tc-title { color:#e4e6f0; }
        .tp-timeline-card .tc-subtitle { font-size:13px; color:#4b5563; margin-bottom:.4rem; }
        [data-theme="dark"] .tp-timeline-card .tc-subtitle,
        .dark-mode .tp-timeline-card .tc-subtitle,
        body.dark .tp-timeline-card .tc-subtitle { color:#9ca3af; }
        .tp-timeline-card .tc-meta { font-size:12px; color:#6b7280; }
        .tp-timeline-card .tc-badge { position:absolute; top:.9rem; right:1rem; font-size:11px; padding:2px 10px; border-radius:20px; font-weight:600; }
        .tc-badge-current { background:rgba(16,185,129,.12); color:#059669; }
        .tc-badge-past { background:rgba(107,114,128,.1); color:#6b7280; }
        .tp-empty-state { text-align:center; color:#9ca3af; padding:1.5rem 0; font-size:13px; }

        /* Action bar */
        .tp-action-bar { display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem; }
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

						<li><a href="{{ url('/admin/academic/academic-years') }}">Academic Years</a></li>
						<li><a href="{{ url('/admin/academic/classes') }}">Classes</a></li>
						<li><a href="{{ url('/admin/academic/subjects') }}">Subjects</a></li>
						<li><a href="{{ url('/admin/academic/teacher-allocation') }}">Teacher Allocation</a></li>
						<!-- <li><a href="{{ url('/admin/academic/teacher-assignments') }}">Assignments</a></li> -->
						<li><a href="{{ url('/admin/academic/attendance') }}">Attendance</a></li>
						<li><a href="{{ url('/admin/academic/timetable/periods') }}">Periods</span></a></li>
						<li><a href="{{ url('/admin/academic/syllabus') }}">Syllabus</a></li>
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
                        <li>
                            <a href="{{ url('/admin/examinations/exams') }}">
                                Marks Entry
                            </a>
                        </li>                        
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
                <ul aria-expanded="false">
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
            <img src="{{ asset('images/ellipse5.png') }}" alt="">
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



        <!--**********************************
            Content body start
			***********************************-->
        <!-- !--********************************** -->
    <div class="content-body">
        <div class="container-fluid">

            <div class="row justify-content-center">
                <div class="col-xl-8">

                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Teacher Profile</h4>

                            <div>
                                <button
                                    id="createLoginBtn"
                                    class="btn btn-sm btn-primary me-2"
                                    
                                >
                                    Create Login
                                </button>

                                <a href="{{ url('/admin/teachers') }}" class="btn btn-sm btn-light">
                                    ← Back
                                </a>
                            </div>
                        </div>


                        <div class="card-body">

                            <div class="text-center mb-4">
                                <img src="{{ asset('images/avatar/1.png') }}"
                                    class="rounded-circle"
                                    width="70"
                                    alt="Teacher">

                                <h3 id="teacherName">—</h3>
                                <span id="teacherStatus" class="badge"></span>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Employee Code:</strong>
                                    <div id="employeeCode">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Gender:</strong>
                                    <div id="teacherGender">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Email:</strong>
                                    <div id="teacherEmail">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Phone:</strong>
                                    <div id="teacherPhone">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Date of Joining:</strong>
                                    <div id="dateOfJoining">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Date of Birth:</strong>
                                    <div id="dateOfBirth">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Qualification:</strong>
                                    <div id="qualification">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Experience:</strong>
                                    <div id="experienceYears">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Primary Subject:</strong>
                                    <div id="primarySubject">—</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Secondary Subject:</strong>
                                    <div id="secondarySubject">—</div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <strong>Address:</strong>
                                    <div id="teacherAddress">—</div>
                                </div>
                            </div>

                            <hr>

                            <strong>Subjects:</strong>
                            <div id="teacherSubjects" class="mt-2"></div>

                            <hr>
                            <strong>Education Qualifications:</strong>
                            <div id="teacherEducationsSection" class="mt-2"><span class="text-muted">Loading...</span></div>

                            <hr>
                            <strong>Work Experience:</strong>
                            <div id="teacherExperiencesSection" class="mt-2"><span class="text-muted">Loading...</span></div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>


        <!--**********************************
            Content body end
			***********************************-->
        <!-- !--********************************** -->
        
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
document.addEventListener("DOMContentLoaded", function ()
{
    const token =
        localStorage.getItem("auth_token");

    if (!token)
    {
        window.location.href = "/login";
        return;
    }

    let currentUserRole = null;

    // ✅ FIXED: get teacher ID from Blade
    const teacherId = {{ $id }};

    if (!teacherId)
    {
        alert("Invalid teacher");

        window.location.href =
            "{{ url('/admin/teachers') }}";

        return;
    }

    let currentTeacherId = teacherId;

    const createLoginBtn =
        document.getElementById("createLoginBtn");


    // ================================
    // Bind click ONCE
    // ================================
    if (createLoginBtn)
    {
        createLoginBtn.addEventListener("click",
        function ()
        {
            if (!currentTeacherId)
            {
                alert("Invalid teacher");
                return;
            }

            createTeacherLogin(currentTeacherId);
        });
    }


    // ================================
    // LOAD LOGGED-IN USER FIRST
    // ================================
    apiRequest("GET", "/me")

    .done(function(res)
    {
        currentUserRole = res.role;

        document.getElementById("headerUserName").innerText =
            res.name ?? "Admin";

        const roleMap =
        {
            super_admin: "Super Admin",
            school_admin: "School Admin",
            teacher: "Teacher"
        };

        document.getElementById("headerUserRole").innerText =
            roleMap[res.role] ?? res.role;


        // Now safe to load teacher
        loadTeacherProfile(currentTeacherId);
    })

    .fail(function()
    {
        alert("Session expired");

        window.location.href =
            "{{ url('/login') }}";
    });

});


// ================================
// LOAD TEACHER PROFILE
// ================================
function loadTeacherProfile(id)
{
    apiRequest("GET", `/teachers/${id}`)

    .done(function(teacher)
    {
        document.getElementById("teacherName").innerText =
            teacher.name ?? "-";

        document.getElementById("employeeCode").innerText =
            teacher.employee_code ?? "-";

        document.getElementById("teacherEmail").innerText =
            teacher.email ?? "—";

        document.getElementById("teacherPhone").innerText =
            teacher.phone ?? "—";

        document.getElementById("teacherGender").innerText =
            teacher.gender ?? "—";

        document.getElementById("dateOfJoining").innerText =
            teacher.date_of_joining ?? "—";

        document.getElementById("dateOfBirth").innerText =
            teacher.date_of_birth ?? "—";

        document.getElementById("qualification").innerText =
            teacher.qualification ?? "—";

        document.getElementById("experienceYears").innerText =
            teacher.experience_years !== null && teacher.experience_years !== undefined
                ? teacher.experience_years + " year(s)"
                : "—";

        document.getElementById("primarySubject").innerText =
            teacher.primary_subject ?? "—";

        document.getElementById("secondarySubject").innerText =
            teacher.secondary_subject ?? "—";

        document.getElementById("teacherAddress").innerText =
            teacher.address ?? "—";


        // STATUS BADGE
        const statusBadge =
            document.getElementById("teacherStatus");

        if (teacher.is_active)
        {
            statusBadge.className =
                "badge badge-success";

            statusBadge.innerText =
                "Active";
        }
        else
        {
            statusBadge.className =
                "badge badge-danger";

            statusBadge.innerText =
                "Inactive";
        }


        // SUBJECTS
        const subjectsDiv =
            document.getElementById("teacherSubjects");

        subjectsDiv.innerHTML = "";

        if (teacher.subjects &&
            teacher.subjects.length > 0)
        {
            teacher.subjects.forEach(sub =>
            {
                subjectsDiv.innerHTML +=
                `
                <span class="badge badge-primary me-1">
                    ${sub.name}
                </span>
                `;
            });
        }
        else
        {
            subjectsDiv.innerHTML =
                `<span class="text-muted">
                    No subjects assigned
                </span>`;
        }


        // EDUCATION
        var eduSection = document.getElementById('teacherEducationsSection');
        if (eduSection) {
            var educations = teacher.educations || [];
            if (educations.length > 0) {
                var eduHtml = '';
                for (var ei = 0; ei < educations.length; ei++) {
                    var edu = educations[ei];
                    var degreeLabel = edu.degree ? edu.degree.replace(/_/g,' ') : '';
                    var yearBadge = edu.passing_year ? edu.passing_year : 'Ongoing';
                    var yearClass = edu.passing_year ? 'tc-badge-past' : 'tc-badge-current';
                    eduHtml += '<div class="tp-timeline-card">';
                    eduHtml += '<span class="tc-badge ' + yearClass + '">' + yearBadge + '</span>';
                    eduHtml += '<div class="tc-title">' + degreeLabel + (edu.field_of_study ? ' &mdash; ' + edu.field_of_study : '') + '</div>';
                    eduHtml += '<div class="tc-subtitle">' + (edu.institution || '') + (edu.board_or_university ? ' &middot; ' + edu.board_or_university : '') + '</div>';
                    eduHtml += '<div class="tc-meta">';
                    if (edu.result) eduHtml += '<span class="badge badge-light me-1">Result: ' + edu.result + '</span>';
                    if (edu.percentage) eduHtml += '<span class="badge badge-light me-1">Percentage: ' + edu.percentage + '%</span>';
                    if (edu.grade) eduHtml += '<span class="badge badge-light me-1">Grade: ' + edu.grade + '</span>';
                    eduHtml += '</div></div>';
                }
                eduSection.innerHTML = eduHtml;
            } else {
                eduSection.innerHTML = '<div class="tp-empty-state">No education records added</div>';
            }
        }

        // EXPERIENCE
        var expSection = document.getElementById('teacherExperiencesSection');
        if (expSection) {
            var experiences = teacher.experiences || [];
            if (experiences.length > 0) {
                var expHtml = '';
                for (var xi = 0; xi < experiences.length; xi++) {
                    var exp = experiences[xi];
                    var fromDate = exp.from_date ? String(exp.from_date).substring(0,10) : '';
                    var toDate = exp.to_date ? String(exp.to_date).substring(0,10) : '';
                    var dateRange = fromDate ? (fromDate + ' → ' + (exp.is_current ? 'Present' : (toDate || ''))) : '';
                    var expBadge = exp.is_current ? 'tc-badge-current' : 'tc-badge-past';
                    var expBadgeText = exp.is_current ? 'Currently Working' : 'Past';
                    expHtml += '<div class="tp-timeline-card">';
                    expHtml += '<span class="tc-badge ' + expBadge + '">' + expBadgeText + '</span>';
                    expHtml += '<div class="tc-title">' + (exp.designation || '') + (exp.organization ? ' at ' + exp.organization : '') + '</div>';
                    if (exp.department) expHtml += '<div class="tc-subtitle">' + exp.department + '</div>';
                    if (dateRange) expHtml += '<div class="tc-meta mb-1">' + dateRange + '</div>';
                    if (exp.responsibilities) expHtml += '<div class="tc-meta"><em>' + exp.responsibilities + '</em></div>';
                    if (exp.leaving_reason && !exp.is_current) expHtml += '<div class="tc-meta mt-1">Leaving Reason: ' + exp.leaving_reason + '</div>';
                    expHtml += '</div>';
                }
                expSection.innerHTML = expHtml;
            } else {
                expSection.innerHTML = '<div class="tp-empty-state">No experience records added</div>';
            }
        }

        // CREATE LOGIN BUTTON VISIBILITY
        const createLoginBtn =
            document.getElementById("createLoginBtn");

        const isAdmin =
            currentUserRole === "super_admin" ||
            currentUserRole === "school_admin";

        if (createLoginBtn &&
            isAdmin &&
            !teacher.user_id)
        {
            createLoginBtn.style.display =
                "inline-block";

            createLoginBtn.disabled = false;
        }
        else if (createLoginBtn)
        {
            createLoginBtn.style.display =
                "none";
        }

    })

    .fail(function()
    {
        alert("Failed to load teacher profile");

        window.location.href =
            "{{ url('/admin/teachers') }}";
    });
}


// ================================
// CREATE LOGIN
// ================================
function createTeacherLogin(teacherId)
{
    if (!confirm(
        "Create login credentials for this teacher?"
    ))
    {
        return;
    }

    const btn =
        document.getElementById("createLoginBtn");

    btn.disabled = true;
    btn.innerText = "Creating...";

    apiRequest("POST",
        `/teachers/${teacherId}/create-login`)

    .done(function(res)
    {
        document.getElementById("loginEmail").value =
            res.email;

        document.getElementById("loginPassword").value =
            res.password;

        new bootstrap.Modal(
            document.getElementById("loginCredentialsModal")
        ).show();

        btn.innerText = "Login Created";
        btn.classList.remove("btn-primary");
        btn.classList.add("btn-success");
    })

    .fail(function(xhr)
    {
        btn.disabled = false;
        btn.innerText = "Create Login";

        alert(
            xhr.responseJSON?.message ??
            "Failed to create login"
        );
    });
}

</script>


<!-- Create Login Credentials Modal -->
<div class="modal fade" id="loginCredentialsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Teacher Login Credentials</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning">
                    <strong>Important:</strong> These credentials will be shown only once.
                    Please copy and share them securely.
                </div>

                <div class="mb-3">
                    <label class="form-label">Login Email</label>
                    <input type="text" id="loginEmail" class="form-control" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Temporary Password</label>
                    <input type="text" id="loginPassword" class="form-control" readonly>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

    


</body>

</html>

