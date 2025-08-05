<?php

use Illuminate\Http\Request;
use App\Http\Middleware\HasRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\UpdateProfileController;
use App\Http\Controllers\CurriculumPlanController;
use App\Http\Controllers\JoiningRequestController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\WorksheetController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/getAllAnnouncementsWithoutToken', [AnnouncementController::class, 'getAllAnnouncements']);


Route::middleware(['auth:sanctum'])->group(function () {

    Route::delete('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);



    //_______________________ProfileController_________________________________________
    Route::get('/showDetailesForStudent', [ProfileController::class, 'showDetailesForStudent']);
    Route::get('/showUserProfileByAdmin/{id}', [ProfileController::class, 'showUserProfileByAdmin']);
    Route::get('/showDetailesForTeacher', [ProfileController::class, 'showDetailesForTeacher']);
    Route::get('/showDetailesForSupervisor', [ProfileController::class, 'showDetailesForSupervisor']);
    Route::get('/showDetailesForAdmin', [ProfileController::class, 'showDetailesForAdmin']);

    //_______________________UpdateProfileController___________________________________
    Route::post('/updateProfileImage', [UpdateProfileController::class, 'updateProfileImage']);
    Route::post('/updatePassword', [UpdateProfileController::class, 'updatePassword']);
    Route::post('/updateEmail', [UpdateProfileController::class, 'updateEmail']);
    Route::post('/updatePhoneNumber', [UpdateProfileController::class, 'updatePhoneNumber']);
    Route::post('/updateStudyOrCareer', [UpdateProfileController::class, 'updateStudyOrCareer']);
    Route::post('/updateMagazeh', [UpdateProfileController::class, 'updateMagazeh']);
    Route::post('/updatePreviousExperience', [UpdateProfileController::class, 'updatePreviousExperience']);
    Route::post('/updatePreviousCoursesInOtherPlace', [UpdateProfileController::class, 'updatePreviousCoursesInOtherPlace']);
    Route::post('/updatePreviousCourses', [UpdateProfileController::class, 'updatePreviousCourses']);






    //_______________________CurriculumPlanController___________________________________

    Route::post('/addCurriculumPlanToLevel/{levelId}', [CurriculumPlanController::class, 'addCurriculumPlanToLevel']);
    Route::get('/getCurriculumPlanByLevel/{levelId}', [CurriculumPlanController::class, 'getCurriculumPlanByLevel']);
    Route::post('/updateCurriculumPlanForLevel/{levelId}/{sessionId}', [CurriculumPlanController::class, 'updateCurriculumPlanForLevel']);

    // All Actors Route
    Route::get('/getAllAnnouncements', [AnnouncementController::class, 'getAllAnnouncements']);



    // Admin-only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/getJoiningRequests/{courseID}', [JoiningRequestController::class, 'getJoiningRequests']);
        Route::get('/getStudentInfo/{studentID}', [JoiningRequestController::class, 'getStudentInfo']);
        Route::get('/enrollStudentToLevel/{studentID}/{courseID}/{levelName}', [JoiningRequestController::class, 'enrollStudentToLevel']);

        Route::post('/createCourse', [CourseController::class, 'createCourse']);
        Route::get('/startNewCourse/{courseID}', [CourseController::class, 'startNewCourse']);
        Route::get('/endCurrentCourse/{couresID}', [CourseController::class, 'endCurrentCourse']);
        Route::get('/getAdminCourses', [CourseController::class, 'getAdminCourses']);

        Route::get('/getTeachers', [SubjectController::class, 'getTeachers']);
        Route::post('/addSubject', [SubjectController::class, 'addSubject']);
        Route::get('/getSubjects/{courseID}/{levelName}', [SubjectController::class, 'getSubjects']);
        Route::post('/addCurriculum', [SubjectController::class, 'addCurriculum']);
        Route::post('/updateCurriculum', [SubjectController::class, 'updateCurriculum']);
        Route::get('/getSubjectDetails/{courseID}/{levelName}', [SubjectController::class, 'getSubjectDetails']);


    });



    // Teacher-only routes
    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
        Route::get('/getTeacherCourses', [CourseController::class, 'getTeacherCourses']);

        Route::post('/addExtension', [SubjectController::class, 'addExtension']);
        Route::delete('/deleteExtension/{extensionID}', [SubjectController::class, 'deleteExtension']);
        Route::get('/getSubjectDetails/{courseID}/{levelName}', [SubjectController::class, 'getSubjectDetails']);

        Route::post('/addWorksheet', [WorkSheetController::class, 'addWorksheet']);
        Route::delete('/deleteWorksheet/{worksheetID}', [WorkSheetController::class, 'deleteWorksheet']);
        Route::delete('/deleteQuestion/{questionID}', [WorkSheetController::class, 'deleteQuestion']);
        Route::put('/editQuestion', [WorkSheetController::class, 'editQuestion']);
        Route::post('/addQuestionToWorksheet', [WorksheetController::class, 'addQuestionToWorksheet']);
        Route::post('teacherSubmitAnswers', [WorksheetController::class, 'teacherSubmitAnswers']);
        Route::put('teacherEditAnswer', [WorksheetController::class, 'teacherEditAnswer']);
        Route::get('getWorksheets/{subjectID}', [WorksheetController::class, 'getWorksheets']);


    });



    // Student-only routes
    Route::middleware(['role:student'])->prefix('student')->group(function () {
        Route::get('/createJoiningRequest/{courseID}', [JoiningRequestController::class, 'createJoiningRequest']);

        Route::get('/getStudentNewCourses', [CourseController::class, 'getStudentNewCourses']);
        Route::get('/getStudentEnrolledCourses', [CourseController::class, 'getStudentEnrolledCourses']);

        Route::get('/requestBook/{curriculumID}', [SubjectController::class, 'requestBook']);
        Route::get('/getSubjectDetailsStudent/{courseID}', [SubjectController::class, 'getSubjectDetailsStudent']);

        Route::post('studentSubmitAnswers', [WorksheetController::class, 'studentSubmitAnswers']);
        Route::put('studentEditAnswer', [WorksheetController::class, 'studentEditAnswer']);
        Route::get('getWorksheets/{subjectID}', [WorksheetController::class, 'getWorksheets']);

    });



    // Adminster-only routes
    Route::middleware(['role:subadmin'])->prefix('subadmin')->group(function () {
        Route::post('/createAnnouncementCourse', [AnnouncementController::class, 'createAnnouncementCourse']);
        Route::delete('/deleteAnnouncementCourse/{id}', [AnnouncementController::class, 'deleteAnnouncementCourse']);

        Route::get('/getSubadminNewCourses', [CourseController::class, 'getSubadminNewCourses']);
        Route::get('/getSubadminCurrentCourses', [CourseController::class, 'getSubadminCurrentCourses']);

        Route::get('/getBookRequestStudents/{curriculumID}', [SubjectController::class, 'getBookRequestStudents']);

    });
});
