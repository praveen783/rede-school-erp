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