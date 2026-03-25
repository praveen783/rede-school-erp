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

	<!-- Favicons Icon -->
	<!-- <link rel="icon" type="image/png" sizes="16x16" href="public/images/favicon.png">
    <link href="public/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="public/vendor/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet" type="text/css"/>
    <link href="public/css/style.css" rel="stylesheet" type="text/css"/>
              -->

	<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon.png') }}">

	<link href="{{ asset('vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css"/>

	<link href="{{ asset('vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}" rel="stylesheet" type="text/css"/>

	<link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css"/>

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

							<!-- School Profile (NEW) -->
							<a href="school-profile.html" class="dropdown-item ai-icon">
								<span class="ms-2">School Profile</span>
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
		
        <!--**********************************
        Content body start
        ***********************************-->
        <div class="content-body  ">
			<div class="container-fluid">
				<div class="form-head d-flex flex-wrap mb-sm-4 mb-3 align-items-center">
					<div class="me-auto  d-lg-block mb-3">
						<h2 class="text-black mb-0 font-w700">Admin Dashboard</h2>
						
					</div>
					
				</div>
		<div class="row">
			<div class="col-xl-3 col-xxl-6 col-sm-6">
				<div class="card card-bx">
					<div class="card-body">
						<div class="media align-items-center">
							<div class="media-body me-3">	
								<h2 class="text-black font-w700" id="kpiTotalStudents">--</h2>
								<p class="mb-0 text-black font-w600">total Students</p>
								<!-- <span ><b class="text-success me-1">+0,5%</b>than last month</span> -->
							</div>
							<div class="d-inline-block">
								<svg class="primary-icon" width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M57.4998 47.5001C57.4998 48.1631 57.2364 48.799 56.7676 49.2678C56.2987 49.7367 55.6629 50.0001 54.9998 50.0001H24.9998C24.3368 50.0001 23.7009 49.7367 23.2321 49.2678C22.7632 48.799 22.4998 48.1631 22.4998 47.5001C22.4998 43.5218 24.0802 39.7065 26.8932 36.8935C29.7063 34.0804 33.5216 32.5001 37.4998 32.5001H42.4998C46.4781 32.5001 50.2934 34.0804 53.1064 36.8935C55.9195 39.7065 57.4998 43.5218 57.4998 47.5001ZM39.9998 10.0001C38.022 10.0001 36.0886 10.5866 34.4441 11.6854C32.7996 12.7842 31.5179 14.346 30.761 16.1732C30.0041 18.0005 29.8061 20.0112 30.192 21.951C30.5778 23.8908 31.5302 25.6726 32.9288 27.0711C34.3273 28.4697 36.1091 29.4221 38.0489 29.8079C39.9887 30.1938 41.9994 29.9957 43.8267 29.2389C45.6539 28.482 47.2157 27.2003 48.3145 25.5558C49.4133 23.9113 49.9998 21.9779 49.9998 20.0001C49.9998 17.3479 48.9463 14.8044 47.0709 12.929C45.1955 11.0536 42.652 10.0001 39.9998 10.0001ZM17.4998 10.0001C15.522 10.0001 13.5886 10.5866 11.9441 11.6854C10.2996 12.7842 9.0179 14.346 8.26102 16.1732C7.50415 18.0005 7.30611 20.0112 7.69197 21.951C8.07782 23.8908 9.03022 25.6726 10.4287 27.0711C11.8273 28.4697 13.6091 29.4221 15.5489 29.8079C17.4887 30.1938 19.4994 29.9957 21.3267 29.2389C23.1539 28.482 24.7157 27.2003 25.8145 25.5558C26.9133 23.9113 27.4998 21.9779 27.4998 20.0001C27.4998 17.3479 26.4463 14.8044 24.5709 12.929C22.6955 11.0536 20.152 10.0001 17.4998 10.0001ZM17.4998 47.5001C17.4961 44.8741 18.0135 42.2735 19.0219 39.8489C20.0304 37.4242 21.5099 35.2238 23.3748 33.3751C21.8487 32.7989 20.2311 32.5025 18.5998 32.5001H16.3998C12.7153 32.5067 9.18366 33.9733 6.57833 36.5786C3.97301 39.1839 2.50643 42.7156 2.49982 46.4001V47.5001C2.49982 48.1631 2.76321 48.799 3.23205 49.2678C3.70089 49.7367 4.33678 50.0001 4.99982 50.0001H17.9498C17.6588 49.1984 17.5066 48.3529 17.4998 47.5001Z" fill="#1E33F2"/>
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-xxl-6 col-sm-6">
				<div class="card card-bx">
					<div class="card-body">
						<div class="media align-items-center">
							<div class="media-body me-3">	
								<h2 class="text-black font-w700" id="kpiTotalTeachers">--</h2>
								<p class="mb-0 text-black font-w600">total Teachers</p>
								<!-- <span ><b class="text-danger me-1">-2%</b>than last month</span> -->
							</div>
							<div class="d-inline-block">
								<svg class="primary-icon" width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M59.0284 17.8807L30.7862 3.81817C30.2918 3.57103 29.7082 3.57103 29.2138 3.81817L0.971602 17.8807C0.375938 18.1794 0 18.787 0 19.4531C0 20.1192 0.375938 20.7268 0.971602 21.0255L29.2138 35.088C29.4609 35.2116 29.7305 35.2734 30 35.2734C30.2695 35.2734 30.5391 35.2116 30.7862 35.088L59.0284 21.0255C59.6241 20.7268 60 20.1192 60 19.4531C60 18.787 59.6241 18.1794 59.0284 17.8807Z" fill="#1E33F2"/>
									<path d="M56.4844 46.1441V26.2285L52.9688 27.9863V46.1441C50.9271 46.8722 49.4531 48.805 49.4531 51.0937V54.6093C49.4531 55.5809 50.2393 56.3671 51.2109 56.3671H58.2422C59.2138 56.3671 60 55.5809 60 54.6093V51.0937C60 48.805 58.526 46.8722 56.4844 46.1441Z" fill="#1E33F2"/>
									<path d="M32.3586 38.2329C31.6308 38.5967 30.8154 38.789 30 38.789C29.1846 38.789 28.3692 38.5967 27.6414 38.2329L10.5469 29.7441V33.5156C10.5469 40.4147 19.1578 45.8203 30 45.8203C40.8422 45.8203 49.4531 40.4147 49.4531 33.5156V29.7441L32.3586 38.2329Z" fill="#1E33F2"/>
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-xxl-6 col-sm-6">
				<div class="card">
					<div class="card-body">
						<div class="media align-items-center">
							<div class="d-inline-block position-relative donut-chart-sale me-4">
								<span class="donut2" data-peity='{ "fill": ["rgb(246, 67, 67, 1)", "rgba(241, 241, 241,1)"],   "innerRadius": 45, "radius": 10}'>5/8</span>
								<small class="text-black">62%</small>
							</div>
							<div class="media-body ">	
								<h2 class="fs-36 text-black font-w700" id="kpiEvents">--</h2>
								<p class="fs-18 mb-0 text-black font-w500">Events</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-3 col-xxl-6 col-sm-6">
				<div class="card">
					<div class="card-body">
						<div class="media align-items-center">
							<div class="d-inline-block position-relative donut-chart-sale me-4">
								<span class="donut2" data-peity='{ "fill": ["rgb(30, 51, 242, 1)", "rgba(241, 241, 241,1)"],   "innerRadius": 45, "radius": 10}'>3/8</span>
								<small class="text-black">38%</small>
							</div>
							<div class="media-body me-3">	
								<h2 class="fs-36 text-black font-w700" id="kpiFoods">--</h2>
								<p class="fs-18 mb-0 text-black font-w500">Foods</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-6">
				<div class="row">					
					<div class="col-xl-12">
						<div class="card">
							<div class="card-header d-sm-flex d-block pb-0 border-0">
								<div class="me-auto pe-3">
									<h4 class="text-black fs-24 font-w700">School Finance</h4>
								</div>
								<div class="d-flex align-items-center justify-content-between">
									<select class="form-control style-1 default-select me-3">
										<option>Daily</option>
										<option>Weekly</option>
										<option>Monthly</option>
									</select>
									<div class="dropdown c-pointer ">
										<div class="btn-link" data-bs-toggle="dropdown">
											<svg  xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1"><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><rect x="0" y="0" width="24" height="24"></rect><circle fill="#000000" cx="12" cy="5" r="2"></circle><circle fill="#000000" cx="12" cy="12" r="2"></circle><circle fill="#000000" cx="12" cy="19" r="2"></circle></g></svg>
										</div>
										<div class="dropdown-menu dropdown-menu-end">
											<a class="dropdown-item" href="javascript:void(0);">View Detail</a>
											<a class="dropdown-item" href="javascript:void(0);">Edit</a>
											<a class="dropdown-item" href="javascript:void(0);">Delete</a>
										</div>
									</div>
								</div>
							</div>
							
							<div class="card-body pb-0">
								<div class="d-flex flex-wrap">	
									<div class="media  align-items-center mb-3">
										<div class="d-inline-block position-relative me-sm-3 me-2">
											<svg class="circle-svg-ico" width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M56 28C56 43.464 43.464 56 28 56C12.536 56 0 43.464 0 28C0 12.536 12.536 0 28 0C43.464 0 56 12.536 56 28ZM8.4 28C8.4 38.8248 17.1752 47.6 28 47.6C38.8248 47.6 47.6 38.8248 47.6 28C47.6 17.1752 38.8248 8.4 28 8.4C17.1752 8.4 8.4 17.1752 8.4 28Z" fill="#F5F5F5"/>
												<path class="primary-svg-path" d="M28 0C32.6046 5.49096e-08 37.1382 1.1356 41.1991 3.3062C45.26 5.47681 48.723 8.61542 51.2811 12.444C53.8393 16.2726 55.4138 20.6731 55.8652 25.2555C56.3165 29.838 55.6307 34.461 53.8686 38.7151C52.1065 42.9693 49.3224 46.7231 45.763 49.6443C42.2036 52.5654 37.9787 54.5637 33.4625 55.462C28.9464 56.3603 24.2784 56.131 19.872 54.7943C15.4657 53.4577 11.457 51.055 8.20102 47.799L14.1407 41.8593C16.4199 44.1385 19.226 45.8204 22.3104 46.756C25.3949 47.6917 28.6625 47.8522 31.8238 47.2234C34.9851 46.5946 37.9425 45.1958 40.4341 43.151C42.9257 41.1062 44.8746 38.4785 46.108 35.5006C47.3415 32.5227 47.8216 29.2866 47.5056 26.0789C47.1897 22.8711 46.0875 19.7908 44.2968 17.1108C42.5061 14.4308 40.082 12.2338 37.2394 10.7143C34.3967 9.19492 31.2232 8.4 28 8.4V0Z" fill="#1E33F2"/>
											</svg>
										</div>
										<div class="media-body me-sm-4 me-3">	
											<h2 class="fs-24 text-black font-w700 mb-0" id="kpiTotalCollected">--</h2>
											<p class="fs-16 mb-0 text-black font-w400">total Income</p>
										</div>
									</div>
									<div class="media align-items-center mb-3">
										<div class="d-inline-block position-relative me-sm-3 me-2">
											<svg  class="circle-svg-ico" width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M56 28C56 43.464 43.464 56 28 56C12.536 56 0 43.464 0 28C0 12.536 12.536 0 28 0C43.464 0 56 12.536 56 28ZM8.4 28C8.4 38.8248 17.1752 47.6 28 47.6C38.8248 47.6 47.6 38.8248 47.6 28C47.6 17.1752 38.8248 8.4 28 8.4C17.1752 8.4 8.4 17.1752 8.4 28Z" fill="#F5F5F5"/>
												<path d="M28 0C32.6373 5.52994e-08 37.202 1.15177 41.2842 3.35188C45.3664 5.55199 48.8382 8.73155 51.3879 12.605C53.9376 16.4785 55.4853 20.9246 55.8921 25.544C56.2988 30.1635 55.5519 34.8116 53.7183 39.071L46.0028 35.7497C47.2863 32.7681 47.8092 29.5144 47.5245 26.2808C47.2397 23.0472 46.1563 19.9349 44.3715 17.2235C42.5868 14.5121 40.1565 12.2864 37.2989 10.7463C34.4414 9.20624 31.2461 8.4 28 8.4L28 0Z" fill="#FF5045"/>
											</svg>

										</div>
										<div class="media-body me-sm-4 me-0">	
											<h2 class="fs-24 text-black font-w700 mb-0" id="kpiTotalExpense">--</h2>
											<p class="fs-16 mb-0 text-black font-w400">total Expense</p>
										</div> 
									</div>	
								</div>	
								<div id="chartBarRunning"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xl-6">
				<div class="card">
					<div class="card-header border-0 pb-0 header-cal">
						<div class="me-auto pe-3">
							<h4 class="text-black font-w700">School Performance</h4>
							<p class="mb-0">You have <strong>245</strong> contacts</p>
						</div>								
					</div>		
					<div class="card-body text-center event-calender pb-2">
						<input type='text' class="form-control d-none" id='datetimepicker1'>
					</div>
				</div>
			</div>
		</div>	
		<div class="col-xl-12">
			<div class="card">
				<div class="card-header border-0 pb-3">
					<div class="me-auto pe-3">
						<h4 class="text-black font-w700 fs-24">Class Analytics</h4>
						<p class="fs-16 mb-0">
							View student performance, attendance and exam analytics by class
						</p>
					</div>

					<a href="{{ url('/admin/class-analytics/classes') }}"
					class="btn btn-primary btn-sm">
						Open Analytics
					</a>
				</div>

				<div class="card-body pt-0">

					<div class="row">

						<div class="col-md-3">
							<div class="border rounded p-3 text-center">
								<h5 class="text-primary font-w700 mb-1">
									Student Insights
								</h5>
								<p class="text-muted small mb-0">
									View student list, gender ratio and category distribution
								</p>
							</div>
						</div>

						<div class="col-md-3">
							<div class="border rounded p-3 text-center">
								<h5 class="text-success font-w700 mb-1">
									Attendance Analytics
								</h5>
								<p class="text-muted small mb-0">
									Track attendance percentage and absentees
								</p>
							</div>
						</div>

						<div class="col-md-3">
							<div class="border rounded p-3 text-center">
								<h5 class="text-warning font-w700 mb-1">
									Exam Performance
								</h5>
								<p class="text-muted small mb-0">
									Analyze exam results and student rankings
								</p>
							</div>
						</div>

						<div class="col-md-3">
							<div class="border rounded p-3 text-center">
								<h5 class="text-danger font-w700 mb-1">
									Category Reports
								</h5>
								<p class="text-muted small mb-0">
									View caste / category wise student statistics
								</p>
							</div>
						</div>

					</div>

				</div>
			</div>
		</div>
		
	</div>
	
        </div>
        <!--**********************************
            Content body end
			***********************************-->
			
		<!--**********************************
		Footer start
		***********************************-->
		
	<div class="footer">
		<div class="copyright">
			<p>Copyright © Designed &amp; Developed by <a href="http://dexignzone.com/" target="_blank">DexignZone</a> 2023</p>
		</div>
    </div>

        <!--**********************************
            Footer end
        ***********************************-->

		<!--**********************************
           Support ticket button start
        ***********************************-->

        <!--**********************************
           Support ticket button end
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
	document.addEventListener("DOMContentLoaded", function () {

		const token = localStorage.getItem("auth_token");
		const user  = JSON.parse(localStorage.getItem("user"));

		// ================================
		// AUTH CHECK
		// ================================
		if (!token || !user) {
			window.location.href = "{{ url('/login') }}";
			return;
		}

		// ================================
		// ROLE PROTECTION (Admin only)
		// ================================
		const allowedRoles = ["super_admin", "school_admin"];
		if (!allowedRoles.includes(user.role)) {
			alert("Access denied");
			localStorage.clear();
			window.location.href = "{{ url('/login') }}";
			return;
		}

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

		

		// ================================
		// DASHBOARD SUMMARY
		// ================================
		apiRequest("GET", "/dashboard/summary")
			.done(function (res) {

				document.getElementById("kpiTotalStudents").innerText =
					res.total_students ?? "--";

				document.getElementById("kpiTotalTeachers").innerText =
					res.total_teachers ?? "--";

				console.log("Dashboard summary loaded:", res);
			})
			.fail(function (err) {
				console.error("Dashboard summary failed", err);
				alert("Failed to load dashboard summary");
			});

	});
	</script>


</body>

</html>

