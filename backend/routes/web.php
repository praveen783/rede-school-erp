<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentResultController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');


Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
});

    /*
    |--------------------------------------------------------------------------
    | Admin/Student Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/students', fn() => view('admin.student.student'));
    Route::get('/admin/students/create', fn() => view('admin.student.student-create'));
    Route::get('/admin/students/{id}/edit', fn() => view('admin.student.student-edit'));

    /*
    |--------------------------------------------------------------------------
    | Admin/Teacher Routes
    |--------------------------------------------------------------------------
    */
    // Teacher list page
    Route::get('/admin/teachers', fn() => view('admin.teacher.teachers'))
        ->name('admin.teachers');

    // Add teacher page
    Route::get('/admin/teachers/create', fn() => view('admin.teacher.add-teacher'))
        ->name('admin.teachers.create');

    // Teacher profile page
    Route::get('/admin/teachers/profile/{id}', function ($id) {
        return view('admin.teacher.teacher-profile', compact('id'));
    })->whereNumber('id')->name('admin.teachers.profile');

    Route::get('/admin/teachers/{id}/edit', fn($id) =>
        view('admin.teacher.edit-teacher', compact('id'))
    )->whereNumber('id')
    ->name('admin.teachers.edit');

        /*
    |--------------------------------------------------------------------------
    | Admin Academic Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/academic')->group(function () {

        Route::view('/academic-years', 'admin.academic.academic-years')
            ->name('admin.academic-years');

        Route::view('/attendance', 'admin.academic.attendance')
            ->name('admin.academic.attendance');

        Route::view('/attendance/manage', 'admin.academic.attendance-manage')
            ->name('admin.academic.attendance.manage');

        Route::view('/classes', 'admin.academic.classes')
            ->name('admin.academic.classes');

        Route::view('/reference', 'admin.academic.reference')
            ->name('admin.academic.reference');

        Route::view('/subjects', 'admin.academic.subjects')
            ->name('admin.academic.subjects');

        // Route::view('/syllabus', 'admin.academic.syllabus.index')
        //     ->name('admin.academic.syllabus');

        Route::view('/teacher-allocation', 'admin.academic.teacher-allocation')
            ->name('admin.academic.teacher-allocation');

        Route::view('/teacher-assignments', 'admin.academic.teacher-assignments')
            ->name('admin.academic.teacher-assignments');

        Route::view('/timetable', 'admin.academic.timetable.index')
            ->name('admin.timetable.index');

    });

    // Admin Syllabus Routes
    Route::prefix('admin/academic/syllabus')->group(function () {

        Route::view('/', 'admin.academic.syllabus.index')
            ->name('admin.academic.syllabus.index');

        Route::view('/create', 'admin.academic.syllabus.create')
            ->name('admin.academic.syllabus.create');

        Route::view('/{id}/manage', 'admin.academic.syllabus.manage')
            ->name('admin.academic.syllabus.manage');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Timetable Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/academic/timetable')->group(function () {

        Route::get('/', function () {
            return view('admin.academic.timetable.index');
        })->name('admin.timetable.index');

        Route::get('/{id}/manage', function ($id) {
            return view('admin.academic.timetable.manage', compact('id'));
        })->name('admin.timetable.manage');

    });

    Route::prefix('admin/academic/timetable')->group(function () {
        Route::get('/periods', function () {
            return view('admin.academic.periods.index');
        })->name('admin.periods.index');
    });

    
    /*
    |--------------------------------------------------------------------------
    | Admin/Fee Routes
    |--------------------------------------------------------------------------
    */


    Route::get('/admin/fee/fee-heads', fn() =>
        view('admin.fee.fee-heads')
    )->name('admin.fee.heads');


    Route::get('/admin/fee/fee-structure', fn() =>
        view('admin.fee.fee-structure')
    )->name('admin.fee.structure');


    Route::get('/admin/fee/fee-assign-adhoc', fn() =>
        view('admin.fee.fee-assign-adhoc')
    )->name('admin.fee.assign.adhoc');


    /* FIXED: assign route */
    Route::get('/admin/fee/fee-structure/{id}/assign', fn($id) =>
        view('admin.fee.fee-structure-assign', compact('id'))
    )->name('admin.fee.structure.assign');


    /* already correct */
    Route::get('/admin/fee/fee-structure/{id}/items', fn($id) =>
        view('admin.fee.fee-structure-items', compact('id'))
    )->name('admin.fee.structure.items');


    /* already correct */
    Route::get('/admin/fee/fee-structure/{id}/view', fn($id) =>
        view('admin.fee.fee-structure-view', compact('id'))
    )->name('admin.fee.structure.view'); 

                
    /*
    |--------------------------------------------------------------------------
    | Parent Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/parents', fn() => view('admin.parent.parents'));
    Route::get('/admin/parents/create', fn() => view('admin.parent.add-parent'));
    Route::get('/admin/parents/profile/{id}', fn($id) =>
        view('admin.parent.parent-profile', compact('id'))
    )->whereNumber('id')->name('admin.parents.profile');
    
    Route::get('/admin/parents/{id}/edit', fn($id) =>
        view('admin.parent.edit-parent', compact('id'))
    )->whereNumber('id')
    ->name('admin.parents.edit');

    /*
    |--------------------------------------------------------------------------
    | Examination Routes
    |--------------------------------------------------------------------------
    */

    // Exam list
    Route::get('/admin/examinations/exams', fn() =>
        view('admin.examinations.exam-list')
    )->name('admin.exams.list');


    // Create exam
    Route::get('/admin/examinations/exams/create', fn() =>
        view('admin.examinations.exam-create')
    )->name('admin.exams.create');


    // Edit exam
    Route::get('/admin/examinations/exams/{id}/edit', fn($id) =>
        view('admin.examinations.exam-edit', compact('id'))
    )->whereNumber('id')->name('admin.exams.edit');


    // Exam subjects mapping
    Route::get('/admin/examinations/exams/{examId}/subjects', function ($examId) {
        return view('admin.examinations.exam-subjects', compact('examId'));
    })->whereNumber('examId')->name('admin.exams.subjects');


    // Exam schedule
    Route::get('/admin/examinations/exams/{id}/schedule', fn($id) =>
        view('admin.examinations.exam-schedule', compact('id'))
    )->whereNumber('id')->name('admin.exams.schedule');


    // Marks entry
    Route::get('/admin/examinations/exams/{id}/marks-entry', fn($id) =>
        view('admin.examinations.marks-entry', compact('id'))
    )->whereNumber('id')->name('admin.exams.marks');


    // Results page
    Route::get('/admin/examinations/results', fn() =>
        view('admin.examinations.results')
    )->name('admin.exams.results');

    Route::get('/admin/examinations/exams/{id}/results', fn($id) =>
        view('admin.examinations.results-view', compact('id'))
    )->whereNumber('id')->name('admin.exams.results');

    // Report cards
    Route::get('/admin/examinations/report-cards', fn() =>
        view('admin.examinations.report-cards')
    )->name('admin.exams.report-cards');


    // Promotions
    Route::get('/admin/examinations/promotions', fn() =>
        view('admin.examinations.promotions')
    )->name('admin.exams.promotions');


    // Admit card list
    Route::get('/admin/examinations/admit-cards', fn() =>
        view('admin.examinations.admit-card-list')
    )->name('admin.exams.admit-cards');


    // Exam list by class
    Route::get('/admin/examinations/class/{classId}/exams', fn($classId) =>
        view('admin.examinations.exam-list-by-class', compact('classId'))
    )->whereNumber('classId')->name('admin.exams.by-class');


   /*
|--------------------------------------------------------------------------
| Auth & Utility Pages Routes
|--------------------------------------------------------------------------
*/

Route::prefix('pages')->group(function () {

    Route::get('/register', fn() => view('pages.page-register'))
        ->name('pages.register');

    Route::get('/forgot-password', fn() => view('pages.page-forgot-password'))
        ->name('pages.forgot-password');

    Route::get('/lock-screen', fn() => view('pages.page-lock-screen'))
        ->name('pages.lock-screen');

});

/*
|--------------------------------------------------------------------------
| Admin category analytics routes Satrts :
|--------------------------------------------------------------------------
*/
    Route::get('/admin/class-analytics', fn() =>
        view('admin.analytics.class-analytics')
    )->name('admin.class-analytics');

    Route::get('/admin/class-analytics/classes', function () {
        return view('admin.analytics.class-list');
    })->name('admin.class-analytics.classes');

    Route::get('/admin/student-profile', fn() =>
        view('admin.analytics.student-profile')
    )->name('admin.student-profile.profile');
    /*
|--------------------------------------------------------------------------
|Admin category analytics routes Ends :
|--------------------------------------------------------------------------
*/

Route::prefix('error')->group(function () {

    Route::get('/400', fn() => view('pages.page-error-400'))
        ->name('error.400');

    Route::get('/403', fn() => view('pages.page-error-403'))
        ->name('error.403');

    Route::get('/404', fn() => view('pages.page-error-404'))
        ->name('error.404');

    Route::get('/500', fn() => view('pages.page-error-500'))
        ->name('error.500');

    Route::get('/503', fn() => view('pages.page-error-503'))
        ->name('error.503');

});


/*
|--------------------------------------------------------------------------
| Students Routes
|--------------------------------------------------------------------------
*/

Route::prefix('student')->group(function () {

    Route::get('/dashboard', function () {
        return view('students.dashboard');
    });

    

    Route::get('/admit-card', function () {
        return view('students.student-admit-card');
    });

    Route::get('/attendance', function () {
        return view('students.student-attendance');
    });

    Route::get(
    '/results/{id}',
    fn($id)=>view('students.results-view',compact('id'))
    )->whereNumber('id');

    Route::get('/grade-results', function () {
        return view('students.grade-results');
    });

    Route::get('/component-wise', function () {
        return view('students.student-component-wise');
    });

   Route::get(
    '/results/{exam}/marksheet',
    [StudentResultController::class,'downloadMarksheet']
    );

    Route::get('/exam-list', function () {
        return view('students.student-examlist');
    });

    Route::get('/fee-structure', function () {
        return view('students.student-fee-structure');
    });

    Route::get('/previous-fees', function () {
        return view('students.student-previous-fees');
    });

    Route::get('/student/previous-fees/{id}', function ($id) {
        return view('students.student-previous-fee-details');
    });

    Route::get('/payment-history', function () {
        return view('students.student-payment-history');
    });


    Route::get('/fee', function () {
        return view('students.student-fee');
    });

    Route::get('/pass-fail', function () {
        return view('students.student-pass-fail');
    });

    Route::get('/profile', function () {
        return view('students.student-profile');
    });

});


/*
|--------------------------------------------------------------------------
| Teachers Routes
|--------------------------------------------------------------------------
*/

Route::prefix('teacher')->group(function () {

    Route::get('/dashboard', function () {
        return view('teachers.dashboard');
    });

    Route::get('/marks', function () {
        return view('teachers.marks');
    });

    Route::get('/profile', function () {
        return view('teachers.profile');
    });

    Route::get('/students', function () {
        return view('teachers.students');
    });

    Route::get('/attendance', function () {
        return view('teachers.teacher-attendance');
    });

    Route::get('/timetable', function () {
        return view('teachers.my-timetable');
    });

    Route::get('/classes', function () {
        return view('teachers.teacher-classes');
    });

    Route::get('/teacher-marks', function () {
        return view('teachers.teacher-marks');
    });

});




