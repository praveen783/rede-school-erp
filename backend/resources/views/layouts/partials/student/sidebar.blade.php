<div class="deznav">
    <div class="deznav-scroll">
        <ul class="metismenu" id="menu">

            <li>
                <a href="{{ url('/student/dashboard') }}" class="ai-icon">
                    <i class="flaticon-025-dashboard"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="{{ url('/student/profile') }}" class="ai-icon">
                    <i class="flaticon-012-students"></i>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>

            <li>
                <a href="{{ url('/student/attendance') }}" class="ai-icon">
                    <i class="flaticon-043-menu"></i>
                    <span class="nav-text">My Attendance</span>
                </a>
            </li>

            <li class="has-arrow">
                <a href="javascript:void()" class="ai-icon">
                    <i class="flaticon-025-dashboard"></i>
                    <span class="nav-text">Examinations</span>
                </a>
                <ul>
                    <li><a href="{{ url('/student/exam-list') }}">Exam List</a></li>
                    <li><a href="{{ url('/student/admit-card') }}">Hall Ticket</a></li>
                    <li><a href="{{ url('/student/grade-results') }}">Results</a></li>
                    <li><a href="{{ url('/student/component-wise') }}">Component-wise</a></li>
                    <li><a href="{{ url('/student/pass-fail') }}">Pass / Fail</a></li>
                </ul>
            </li>

            <li>
                <a href="{{ url('/student/fee') }}" class="ai-icon">
                    <i class="flaticon-043-menu"></i>
                    <span class="nav-text">Fee</span>
                </a>
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