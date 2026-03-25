<?php

use App\Http\Controllers\Api\SchoolController;
use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;        
use App\Http\Controllers\Api\AcademicYearController;   
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\MarkController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\TeacherAssignmentController;
use App\Http\Controllers\Api\FeeStructureController;
use App\Http\Controllers\Api\FeeHeadController;
use App\Http\Controllers\Api\StudentFeeAssignmentController;
use App\Http\Controllers\Api\FeePaymentController;
use App\Http\Controllers\Api\StudentFeeSummaryController;
use App\Http\Controllers\Api\Reports\FeeReportController;
use App\Http\Controllers\Api\Dashboard\FeeDashboardController;
use App\Http\Controllers\Api\Dashboard\AdminDashboardController;
use App\Http\Controllers\Api\ParentsController;
use App\Http\Controllers\Api\TeacherPortalController;
use App\Http\Controllers\Api\TeacherAttendanceController;
use App\Http\Controllers\Api\TeacherLoginController;
use App\Http\Controllers\Api\TeacherExamController;    
use App\Http\Controllers\Api\TeacherMarksController;
use App\Http\Controllers\Api\AdminResultController;
use App\Http\Controllers\Api\StudentLoginController;
use App\Http\Controllers\Api\ParentLoginController;
use App\Http\Controllers\Api\AdminAttendanceController;
use App\Http\Controllers\Api\Admin\ExamAdmitCardController;
use App\Http\Controllers\Api\Admin\ExamAdmitCardSubjectController;
use App\Http\Controllers\Api\ClassSubjectController;
use App\Http\Controllers\Api\ClassSectionController;
use App\Http\Controllers\Api\StudentProfileController;
use App\Http\Controllers\Api\student\StudentAdmitCardController;
use App\Http\Controllers\Api\Admin\BulkAdmitCardController;
use App\Http\Controllers\Api\Admin\SchoolLogoController;
use App\Http\Controllers\Api\ExamScheduleController;
use App\Http\Controllers\Api\FeeStructureItemController;
use App\Http\Controllers\Api\StudentFeeController;
use App\Http\Controllers\Api\StudentFeePaymentController;
use App\Http\Controllers\Api\FeeInstallmentPlanController;
use App\Http\Controllers\Api\Admin\AdminStudentController;
use App\Http\Controllers\Api\SyllabusController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\Admin\TimetableController;
use App\Http\Controllers\Api\Admin\TimetableEntryController;
use App\Http\Controllers\Api\Admin\PeriodController;
use App\Http\Controllers\Api\RazorpayController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\StudentResultController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\AdminAnalyticsController;


//  Public Auth Routes
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
    Route::get('/boards', [BoardController::class, 'index']);
});

//  Protected Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
     
    Route::post('/logout', [AuthController::class, 'logout']);

    // Temporary default route (will be replaced by /me later)
    Route::get('/me', [AuthController::class, 'me']);


    // Super Admin ONLY
    Route::middleware('role:' . Role::SUPER_ADMIN)->get('/admin-only', function () {
        return response()->json([
            'message' => 'Welcome Super Admin'
        ]);
    });


    // Super Admin + School Admin
    Route::middleware(
        'role:' . Role::SUPER_ADMIN . ',' . Role::SCHOOL_ADMIN
    )->get('/admin-school', function () {
        return response()->json([
            'message' => 'Welcome Admin or School Admin'
        ]);
    });

    // School Management (Super Admin only)
    Route::middleware('role:' . Role::SUPER_ADMIN)->group(function () {
        Route::get('/schools', [SchoolController::class, 'index']);
        Route::post('/schools', [SchoolController::class, 'store']);
    });

    
    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::post('/schools/{school}/academic-years', [AcademicYearController::class, 'store']);
        
        Route::patch(
            '/academic-years/{id}/close',
            [AcademicYearController::class, 'close']
        );
        Route::patch(
            '/academic-years/{id}/activate',
            [AcademicYearController::class, 'activate']
        );

    });
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/academic-years', [AcademicYearController::class, 'index']);
    });

    // Classes & Sections (Super Admin only)
    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {

        Route::post('/classes', [ClassController::class, 'store']);
        Route::post('/sections', [SectionController::class, 'store']);

    });

    /* --------------------------------------------------------
    -----------------------------------------------------------
        Admin Timetable Routes Starts
    -----------------------------------------------------------
    --------------------------------------------------------*/

    Route::middleware(['auth:sanctum','role:school_admin,super_admin'])
        ->prefix('timetables')
        ->group(function () {

        Route::get('/', [TimetableController::class, 'index']);
        Route::get('/class-data', [TimetableController::class, 'getClassAssignmentData']);
        Route::post('/', [TimetableController::class, 'store']);
        Route::get('/{timetable}', [TimetableController::class, 'show']);
        Route::post('/{timetable}/entries', [TimetableEntryController::class, 'store']);

        Route::put('/entries/{entry}', [TimetableEntryController::class, 'update']);
        Route::delete('/entries/{entry}', [TimetableEntryController::class, 'destroy']);
    });
    Route::middleware(['auth:sanctum'])
        ->prefix('timetable')
        ->group(function () {

        Route::get('/my-class', [TimetableController::class, 'myClassTimetable']);
        Route::get('/my-teacher', [TimetableController::class, 'myTeacherTimetable']);
    });

    Route::middleware(['auth:sanctum','role:school_admin,super_admin'])
        ->prefix('periods')
        ->group(function () {

            Route::get('/', [PeriodController::class, 'index']);
            Route::post('/', [PeriodController::class, 'store']);
            Route::put('/{period}', [PeriodController::class, 'update']);
            Route::delete('/{period}', [PeriodController::class, 'destroy']);

        });
    /* --------------------------------------------------------
    -----------------------------------------------------------
        Admin Timetable Routes Ends
    -----------------------------------------------------------
    --------------------------------------------------------*/
    
    // fetch list of sections
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/sections', action: [SectionController::class, 'index']);
    });

    // Fetch list of classes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/classes', action: [ClassController::class, 'index']);
    });


    // Fetch classes and section details combined
    Route::middleware(['auth:sanctum'])
    ->get('/class-sections', [ClassSectionController::class, 'index']);


   // List of classes to particular section 

    Route::middleware(['auth:sanctum'])->get(
        '/class-sections/{class}/sections/{section}/exams',
        [ExamController::class, 'byClassSection']
    );

    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {

        // Common Attendance APIs
        Route::post('/attendance/mark', [AttendanceController::class, 'mark']);
        Route::get('/attendance/summary', [AttendanceController::class, 'summary']);

        // Teacher Attendance
        Route::middleware(['role:teacher'])->group(function () {
            Route::get('/teacher/attendance', [TeacherAttendanceController::class, 'index']);
            Route::post('/teacher/attendance', [TeacherAttendanceController::class, 'store']);
        });

        // Admin Attendance
        Route::middleware(['role:school_admin,super_admin'])->group(function () {
            Route::get('/admin/attendance', [AdminAttendanceController::class, 'index']);
            Route::post('/admin/attendance', [AdminAttendanceController::class, 'store']);
        });

        Route::get('/admin/attendance-monitor', [AdminAttendanceController::class, 'monitor']);

    });
    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::post('/syllabus', [SyllabusController::class, 'store']);
        Route::put('/syllabus/{id}', [SyllabusController::class, 'update']);
        Route::delete('/syllabus/{id}', [SyllabusController::class, 'destroy']);

        Route::post('/syllabus/{id}/units', [SyllabusController::class, 'addUnit']);
        Route::put('/units/{unitId}', [SyllabusController::class, 'updateUnit']);
        Route::delete('/units/{unitId}', [SyllabusController::class, 'deleteUnit']);

        Route::post('/syllabus/{id}/resources', [SyllabusController::class, 'addResource']);
        Route::delete('/resources/{resourceId}', [SyllabusController::class, 'deleteResource']);
    });

    // ======================
    // VIEW ACCESS (ADMIN + TEACHER + STUDENT)
    // ======================

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,teacher,student'])->group(function () {
        Route::get('/syllabus', [SyllabusController::class, 'index']);
        Route::get('/classes/{class}/syllabus', [SyllabusController::class, 'classWise']);
        Route::get('/classes/{class}/subjects/{subject}/syllabus', [SyllabusController::class, 'classSubjectSyllabus']);
        Route::get('/syllabus/{id}', [SyllabusController::class, 'show']);  // ← ADD THIS
        
    });

    
    /* --------------------------------------------------------
    -----------------------------------------------------------
        Admin Examinations and Academics Routes Starts
    -----------------------------------------------------------
    --------------------------------------------------------*/

    
    // Subjects
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::patch('/subjects/{id}/deactivate', [SubjectController::class, 'deactivate']);

        // Fetch class-wise subjects
        Route::get('/classes/{classId}/subjects', [ClassSubjectController::class, 'index']);
        // Assign subjects to class
        Route::post('/classes/{classId}/subjects', [ClassSubjectController::class, 'store']);
        // Update class subjects
        Route::put('/classes/{classId}/subjects', [ClassSubjectController::class, 'update']);

        Route::delete('/classes/{classId}/subjects/{subjectId}', 
            [ClassSubjectController::class, 'destroy']); // NEW
    });

    
    
    // API to create, fetch and update exams 
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/exams', [ExamController::class, 'store']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/exams/{id}', [ExamController::class, 'show']);
        Route::put('/exams/{id}', [ExamController::class, 'update']);
    });
                                                                                                                    
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('/exams', [ExamController::class, 'index']);
        Route::patch('/exams/{id}/publish', [ExamController::class, 'publish']);
        Route::patch('/exams/{id}/deactivate', [ExamController::class, 'deactivate']);

    });

    Route::post('/exams/{examId}/subjects', [ExamController::class, 'mapSubjects']);
    Route::get(
        '/exams/{examId}/subjects',
        [ExamController::class, 'getMappedSubjects']
    );

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {

        Route::post(
            '/exam-schedules',
            [ExamScheduleController::class, 'store']
        );
        Route::get(
            '/exam-schedules',
            [ExamScheduleController::class, 'index']
        );


        Route::put(
            '/exam-schedules/{examId}/{classId}/{sectionId}',
            [ExamScheduleController::class, 'update']
        );

        Route::delete(
            '/exam-schedules/{examId}/{classId}/{sectionId}',
            [ExamScheduleController::class, 'destroy']
        );
    });



    // Admit Card Preview and PDF generation

    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {
        Route::post(
            '/admin/exams/{exam}/admit-card',
            [ExamAdmitCardController::class, 'store']
        );   
        Route::get(
            '/admin/exams/{exam}/admit-card/preview/{student}',
            [ExamAdmitCardController::class, 'preview']
        );
    
        Route::get(
            '/admin/exams/{exam}/admit-card/pdf/{student}',
            [ExamAdmitCardController::class, 'pdf']
        );
        Route::patch(
            '/admin/exams/{exam}/admit-card/publish',
            [ExamAdmitCardController::class, 'publish']
        );
    });

    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {
        Route::get(
            '/admin/exams/{exam}/admit-cards/class/{class}/section/{section}',
            [BulkAdmitCardController::class, 'download']
        );
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::post(
            '/admin/schools/{school}/logo',
            [SchoolLogoController::class, 'upload']
        );
    });

    // Class + section wise subject results for admin to view and edit.

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {

        Route::get(
            '/admin/results/{class}/{section}',
            [ExamController::class, 'resultsByClassSection']
        );

    });
    
    Route::middleware(['auth:sanctum','role:school_admin,super_admin'])->group(function () {

        Route::get(
            '/admin/results/{exam}',
            [ExamController::class, 'viewResults']
        );

    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::post('/admin/exams/{exam}/publish-results',[ExamController::class, 'publishResults']);

    });


    
    
    /* --------------------------------------------------------
    -----------------------------------------------------------
        Admin Examinations and Academics Routes Ends 
    -----------------------------------------------------------
    --------------------------------------------------------*/

    /* --------------------------------------------------------
    -----------------------------------------------------------
        Admin Examinations and Results Routes Starts 
    -----------------------------------------------------------
    --------------------------------------------------------*/



//     // Marks (Super Admin only for now)
//     Route::middleware(['auth:sanctum', 'role:' . Role::SUPER_ADMIN])->group(function () {

//         Route::post('/marks', [MarkController::class, 'store']);

//     });

//    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
//         Route::get('/results/student', [ResultController::class, 'studentResult']);
//     });

    
//     Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
//         Route::get('/results/class', [ResultController::class, 'classResult']);
//     });


//     Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {

//         Route::patch(
//             '/exams/{examId}/publish-results',
//             [ExamController::class, 'publishResults']
//         );

//     });

    /* --------------------------------------------------------
    -----------------------------------------------------------
        Admin Examinations and Results Routes Ends 
    -----------------------------------------------------------
    --------------------------------------------------------*/

    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {

        Route::post('/teachers', [TeacherController::class, 'store']);
        Route::get('/teachers', [TeacherController::class, 'index']);
        Route::get('/teachers/{id}', [TeacherController::class, 'show']);
        Route::put('/teachers/{id}', [TeacherController::class, 'update']);
        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);
        Route::patch('/teachers/{id}/toggle-status', [TeacherController::class, 'toggleStatus']);

    });

   
    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {

        Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store']);
        Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index']);
        Route::patch(
            '/teacher-assignments/{id}/toggle-status',
            [TeacherAssignmentController::class, 'toggleStatus']
        );

    });

  

    // To assign class teacher to a class
    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
    
        Route::post('/admin/class-teacher-assign', 
            [TeacherPortalController::class, 'assignClassTeacher']
        );

    });

    Route::middleware(['auth:sanctum','role:school_admin,super_admin'])->group(function () {

        Route::get('/admin/teacher-allocations',
            [TeacherPortalController::class, 'getAllocations']
        );

        Route::post('/admin/subject-teacher-assign',
            [TeacherPortalController::class, 'assignSubjectTeachers']
        );
    });

    // Teacher Portal urls 

   

    
    

    // Teacher Login Controll
    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {

        Route::post(
            '/teachers/{teacher}/create-login',
            [TeacherLoginController::class, 'create']
        );

    });
    // Student Login controll

   
    // Admin Login Controll
    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::post(
            '/parents/{parent}/create-login',
            [ParentLoginController::class, 'create']
        );
    });


    // Admin controll on exam publish
    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {
        Route::patch(
            '/exams/{exam}/publish-results',
            [AdminResultController::class, 'publish']
        );
    });


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get(
            '/reports/fees/academic-year/{academicYearId}',
            [FeeReportController::class, 'academicYearSummary']
        );
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get(
            '/reports/fees/academic-year/{academicYearId}/classes',
            [FeeReportController::class, 'classWiseSummary']
        );
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get(
            '/reports/fees/academic-year/{academicYear}/fee-heads',
            [FeeReportController::class, 'feeHeadWiseReport']
        );
    });
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get(
            '/dashboard/fees/academic-year/{academicYear}',
            [FeeDashboardController::class, 'academicYearSummary']
        );
    });

    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])
    ->get('/dashboard/summary', [AdminDashboardController::class, 'summary']);

    /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                            Student Portal API urls Starts : 
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/

    Route::middleware('role:' . Role::SUPER_ADMIN . ',' . Role::SCHOOL_ADMIN)->group(function () {
        Route::post('/students', [StudentController::class, 'store']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/categories', [CategoryController::class, 'index']);
    });

    // Update & deactivate (Admin only)
    Route::middleware([
        'auth:sanctum',
        'role:' . Role::SUPER_ADMIN . ',' . Role::SCHOOL_ADMIN
    ])->group(function () {

        Route::put('/students/{id}', [StudentController::class, 'update']);

        Route::patch('/students/{id}/activate', [StudentController::class, 'activate']);
        Route::patch('/students/{id}/deactivate', [StudentController::class, 'deactivate']);

    });

    
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/students/{id}', [StudentController::class, 'show']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/students/{student}/fee-summary', [StudentFeeSummaryController::class, 'show']);
    });

    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {
        Route::post('/students/promote', [StudentController::class, 'promote']);
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::post(
            '/students/{student}/create-login',
            [StudentLoginController::class, 'create']
        );
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::get('/student/attendance/summary', [AttendanceController::class, 'studentSummary']);
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::get('/student/attendance', [AttendanceController::class, 'studentAttendance']);
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::get('/student/exams', [ExamController::class, 'studentExams']);
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::get(
            '/student/exams/{examId}/results',
            [ExamController::class, 'studentExamResults']
        );
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::get('/student/exams/summary', [ExamController::class, 'studentExamSummary']);
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::get('/student/exams/list', [ExamController::class, 'studentExamList']);
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::post('/student/profile/photo', [StudentProfileController::class, 'uploadPhoto']);
    });


    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {
        Route::get(
            '/student/exams/{exam}/admit-card/pdf',
            [StudentAdmitCardController::class, 'pdf']
        );
    });


    // url to publish admit card
    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {

        Route::patch(
            '/admin/admit-cards/{admitCard}/publish',
            [ExamAdmitCardController::class, 'publish']
        );

    });
    // New route to fetch list of admit cards for student
    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
        Route::get(
            '/student/admit-cards',
            [StudentAdmitCardController::class, 'list']
        );
    });

    // student exam Results 
    Route::middleware(['auth:sanctum','role:student'])->group(function () {

        Route::get('/student/results',
        [StudentResultController::class,'examResults']);

        Route::get(
        '/student/results/{exam}',
        [StudentResultController::class,'examResultDetails']);

    });

    // Route::get(
    //     '/student/results/{exam}/marksheet',
    //     [StudentResultController::class,'downloadMarksheet']
    //     )->middleware('auth');


    /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                            Student Portal API urls Ends : 
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/

   //Admit Card creation apis :

   Route::middleware(['auth:sanctum', 'role:super_admin,school_admin,accountant'])
        ->prefix('admin/exams')
        ->group(function () {

        Route::post(
            '/{exam}/admit-card',
            [ExamAdmitCardController::class, 'store']
        );

        Route::put(
            '/admit-card/{admitCard}',
            [ExamAdmitCardController::class, 'update']
        );

        Route::post(
            '/admit-card/{admitCard}/subjects',
            [ExamAdmitCardSubjectController::class, 'store']
        );

        Route::post(
            '/admit-card/{admitCard}/publish',
            [ExamAdmitCardController::class, 'publish']
        );
    });


    // Parents portal API Urls:

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/parents', [ParentsController::class, 'index']);
        Route::post('/parents', [ParentsController::class, 'store']);
        Route::get('/parents/{id}', [ParentsController::class, 'show']);
        Route::put('/parents/{id}', [ParentsController::class, 'update']);
        Route::delete('/parents/{id}', [ParentsController::class, 'destroy']);

    });

    /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                        Fee related API urls Starts : 
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/

    Route::middleware(['auth:sanctum', 'role:school_admin'])->group(function () {

        Route::get('/fee-heads', [FeeHeadController::class, 'index']);
        Route::post('/fee-heads', [FeeHeadController::class, 'store']);
        Route::patch('/fee-heads/{feeHead}/toggle-status', [FeeHeadController::class, 'toggleStatus']);

    });

    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {

        Route::post('/fee-structures', [FeeStructureController::class, 'store']);

        // ADD THIS
        Route::get('/fee-structures', [FeeStructureController::class, 'index']);

        Route::patch(
            '/fee-structures/{feeStructure}/activate',
            [FeeStructureController::class, 'activate']
        );
    });

    Route::get(
        '/fee-structures/{feeStructure}',
        [FeeStructureController::class, 'show']
    );



    Route::middleware(['auth:sanctum', 'role:super_admin,school_admin'])->group(function () {
        Route::post(
            '/fee-structures/{feeStructure}/items',
            [FeeStructureItemController::class, 'store']
        );
        Route::get(
            '/fee-structures/{feeStructure}/items',
            [FeeStructureItemController::class, 'index']
        );   

        Route::patch(
            '/fee-structures/{feeStructure}/activate',
            [FeeStructureController::class, 'activate']
        );

    });

    // Assign Fee to student
    Route::post(
        '/fee-structures/{feeStructure}/assign',
        [StudentFeeAssignmentController::class, 'assign']
    );
    // Fetch individual student fee summary
    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
        Route::get( '/student/fees',
           [StudentFeeController::class, 'index'] 
        );
        Route::get(
            '/student/fees/{assignment}',
            [StudentFeeController::class, 'show']
        );

    });
    Route::get(
        '/fee-structures/{feeStructure}/assignment-status',
        [FeeStructureController::class, 'assignmentStatus']
    );
    
    /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                        Student Fee payment related API urls Starts : 
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/
    Route::middleware(['auth:sanctum', 'role:student,parent,school_admin,super_admin',])
        ->post('/student/fees/{assignment}/pay', 
            [StudentFeePaymentController::class, 'pay']
    );
    // Post installment plan by admin to students and parents
    Route::middleware(['auth:sanctum', 'role:school_admin'])
    ->post(
        '/student-fee-assignments/{assignment}/installment-plan',
        [FeeInstallmentPlanController::class, 'store']
    );
    Route::get(
        '/fee-structures/{feeStructure}/installment-status',
        [FeeInstallmentPlanController::class, 'status']
    );

    // Post url to assign adhoc fee to students other than semister fee 
    
    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::post('/fee-assignments/adhoc', [StudentFeeAssignmentController::class, 'assignAdhoc']);
    });
    
    // To fetch student details to assing other than semister fee 

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin'])->group(function () {
        Route::get('/admin/students', [AdminStudentController::class, 'index']);
        Route::get('/admin/student-options', [AdminStudentController::class, 'options']);
    });

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,student'])->group(function () {

        Route::post('/student/razorpay/create-order', 
            [RazorpayController::class, 'createOrder']
        );

        Route::post('/student/razorpay/verify-payment', 
            [RazorpayController::class, 'verifyPayment']
        );

    });
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::get('/payments/{id}/receipt', 
            [PaymentController::class, 'downloadReceipt']);

    });

    Route::middleware('auth:sanctum')->get(
        '/student/payment-history',
        [StudentFeeController::class, 'paymentHistory']
    );

    Route::middleware(['auth:sanctum', 'role:student'])->group(function () {

    Route::get('/student/previous-fees',
        [StudentFeeController::class, 'previousFees']);

    });
    Route::get('/student/previous-fees/{id}',
        [StudentFeeController::class, 'previousFeeDetails']);

    Route::post('/razorpay/webhook', [RazorpayController::class, 'webhook']);
    });

    /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                    Teacher Portal Fee API urls Starts : 
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/
     Route::middleware(['auth:sanctum','role:teacher,school_admin,super_admin'])->group(function () {


        Route::get('/teacher/me', [TeacherPortalController::class, 'me']);
        Route::get('/teacher/dashboard', [TeacherPortalController::class, 'dashboard']);

        Route::get('/teacher/classes', action: [TeacherPortalController::class, 'classes']);
        Route::get('/teacher/students', [TeacherPortalController::class, 'students']);

        Route::post('/teacher/attendance', [TeacherAttendanceController::class, 'store']);
        Route::get('/teacher/attendance', [TeacherAttendanceController::class, 'index']);


        Route::post('/teacher/marks', [TeacherMarksController::class, 'store']);
        // To fetch class teacher's class
        Route::get('/teacher/class-teacher-classes', 
        [TeacherPortalController::class, 'classTeacherClasses']
        );
    });

    //To fetch exams of a teacher when selected particular class and section in teacher portal for marks entry

    Route::middleware(['auth:sanctum', 'role:school_admin,super_admin,teacher'])->group(function () {
        Route::get('/teacher/exams', [TeacherExamController::class, 'exams']);
        Route::get('/teacher/exams/{exam}/subjects',[TeacherExamController::class, 'subjects']);
        Route::get('/teacher/exams/{exam}/marks-sheet',[TeacherExamController::class, 'marksSheet']);
        Route::post('/teacher/marks', [TeacherExamController::class, 'saveMarks']);
    });

    /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                    Teacher Portal Fee API urls Ends: 
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/

 /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                        Admin Analytics urls Starts:
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/

    Route::middleware('role:' . Role::SUPER_ADMIN . ',' . Role::SCHOOL_ADMIN)
    ->group(function () {

        Route::get('/admin/class-analytics', [AdminAnalyticsController::class, 'classAnalytics']);
        Route::get('/admin/student-details', [AdminAnalyticsController::class, 'studentDetails']);


    });

    /*----------------------------------------------------------------------------------------
    -----------------------------------------------------------------------------------------
                    Admin Analytics urls Ends:
    -----------------------------------------------------------------------------------------
    ----------------------------------------------------------------------------------------*/






