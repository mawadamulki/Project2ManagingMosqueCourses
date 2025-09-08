<?php

use Illuminate\Http\Request;
use App\Http\Middleware\HasRole;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\UpdateProfileController;
use App\Http\Controllers\CurriculumPlanController;
use App\Http\Controllers\JoiningRequestController;
use App\Http\Controllers\WorksheetController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\GetFuncController;
use App\Http\Controllers\SearchController;


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
Route::get('/getAllAnnouncementsWithoutToken', action: [AnnouncementController::class, 'getAllAnnouncements']);


Route::middleware(['auth:sanctum'])->group(function () {


    //_______________________CurriculumPlanController___________________________________
    Route::post('/updateCurriculumPlanForLevel/{levelId}/{sessionId}', [CurriculumPlanController::class, 'updateCurriculumPlanForLevel']);



    // All Actors Route

    Route::delete('/logout', [AuthController::class, 'logout']);

    Route::get('/getAllAnnouncements', [AnnouncementController::class, 'getAllAnnouncements']);

    Route::get('getSubjects/{couresID}/{levelName}', [PresenceController::class, 'getSubjects']);
    Route::get('getStudentInLevel/{subjectID}', [PresenceController::class, 'getStudentInLevel']);
    Route::get('getStudentInLevel2/{courseID}/{levelName}', [PresenceController::class, 'getStudentInLevel2']);

    Route::get('getStudentInfoInLevel/{studentID}/{courseID}/{levelName}', [GetFuncController::class, 'getStudentInfoInLevel']);

    Route::get('searchInLevel/{courseID}/{levelName}/{search}', [SearchController::class, 'searchInLevel']);
    Route::get('searchStudentTeacherInSystem/{search}', [SearchController::class, 'searchStudentTeacherInSystem']);
    Route::get('searchSubject/{search}', [SearchController::class, 'searchSubject']);

    Route::post('addProfileImage', [UpdateProfileController::class, 'addProfileImage']);
    Route::post('updateProfile', [UpdateProfileController::class, 'updateProfile']);

    Route::get('getSubadmin', [MessagesController::class, 'getSubadmin']);
    Route::post('sendMessage', [MessagesController::class, 'sendMessage']);
    Route::get('receivedMessages', [MessagesController::class, 'receivedMessages']);
    Route::get('sentMessages', [MessagesController::class, 'sentMessages']);
    Route::get('openMessage/{messageID}', [MessagesController::class, 'openMessage']);
    Route::get('getTeachers', [MessagesController::class, 'getTeachers']);

    Route::get('getCurriculumPlan/{courseID}/{levelName}', [CurriculumPlanController::class, 'getCurriculumPlan']);


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

        Route::get('getMarksAdmin/{subjectID}', [ResultController::class, 'getMarks']);

        Route::delete('deleteStudentAccount/{studentID}', [GetFuncController::class, 'deleteStudentAccount']);
        Route::get('getAllStudents', [GetFuncController::class, 'getAllStudents']);
        Route::get('getCoursesForStudent/{studentID}', [GetFuncController::class, 'getCoursesForStudent']);
        Route::get('getCourseDetailForStudent/{studentID}/{courseID}/{levelName}', [GetFuncController::class, 'getCourseDetailForStudent']);
        Route::delete('deleteTeacherAccount/{tacherID}', [GetFuncController::class, 'deleteTeacherAccount']);
        Route::get('getAllTeachers', [GetFuncController::class, 'getAllTeachers']);
        Route::get('getCoursesForTeacher/{teacherID}', [GetFuncController::class, 'getCoursesForTeacher']);
        Route::get('getAllSubadmins', [GetFuncController::class, 'getAllSubadmins']);
        Route::delete('deleteSubadminAccount/{subadminID}', [GetFuncController::class, 'deleteSubadminAccount']);


        Route::post('addCurriculumPlan', [CurriculumPlanController::class, 'addCurriculumPlan']);
        Route::delete('deleteCurriculumPlan/{sessionID}', [CurriculumPlanController::class, 'deleteCurriculumPlan']);

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
        Route::get('getWorksheetWithAnswers/{worksheetID}', [WorksheetController::class, 'getWorksheetWithAnswers']);

        Route::get('getTeacherSubjects/{courseID}/{levelName}', [ResultController::class, 'getTeacherSubjects']);
        Route::get('getMarksTeacher/{subjectID}', [ResultController::class, 'getMarks']);

        Route::get('teacherProfile', [ProfileController::class, 'teacherProfile']);

        Route::get('getLevelsForTeacher', [MessagesController::class, 'getLevelsForTeacher']);
        Route::get('getStudentInLevel/{levelID}', [MessagesController::class, 'getStudentInLevel']);

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
        Route::get('getWorksheetWithAnswers/{worksheetID}', [WorksheetController::class, 'getWorksheetWithAnswers']);
        Route::get('getTeacherAnswers/{worksheetID}', [WorksheetController::class, 'getTeacherAnswers']);

        Route::get('getMarksStudent/{subjectID}', [ResultController::class, 'getMarksStudent']);

        Route::get('studentProfile', [ProfileController::class, 'studentProfile']);
        Route::get('getCourseDetailForStudent1/{courseID}/{levelName}', [GetFuncController::class, 'getCourseDetailForStudent1']);

    });



    // Subadmin-only routes
    Route::middleware(['role:subadmin'])->prefix('subadmin')->group(function () {
        Route::post('/createAnnouncementCourse', [AnnouncementController::class, 'createAnnouncementCourse']);
        Route::delete('/deleteAnnouncementCourse/{id}', [AnnouncementController::class, 'deleteAnnouncementCourse']);

        Route::get('/getSubadminNewCourses', [CourseController::class, 'getSubadminNewCourses']);
        Route::get('/getSubadminCurrentCourses', [CourseController::class, 'getSubadminCurrentCourses']);
        Route::get('/getBookRequestStudents/{courseID}/{levelName}', [SubjectController::class, 'getBookRequestStudents']);

        Route::post('addPresence', [PresenceController::class, 'addPresence']);
        Route::get('getStudentInLevel/{subjectID}', [PresenceController::class, 'getStudentInLevel']);
        Route::get('getPresence/{subjectID}', [PresenceController::class, 'getPresence']);

        Route::post('addEmptyMarks/{subjectID}', [ResultController::class, 'addEmptyMarks']);
        Route::post('addTestMarks/{subjectID}', [ResultController::class, 'addTestMarks']);
        Route::post('addExamMarks/{subjectID}', [ResultController::class, 'addExamMarks']);
        Route::get('getMarksSubadmin/{subjectID}', [ResultController::class, 'getMarks']);

        Route::get('getAllStudents', [GetFuncController::class, 'getAllStudents']);
        Route::get('getCoursesForStudent/{studentID}', [GetFuncController::class, 'getCoursesForStudent']);
        Route::get('getCourseDetailForStudent/{studentID}/{courseID}/{levelName}', [GetFuncController::class, 'getCourseDetailForStudent']);
        Route::get('getAllTeachers', [GetFuncController::class, 'getAllTeachers']);
        Route::get('getCoursesForTeacher/{teacherID}', [GetFuncController::class, 'getCoursesForTeacher']);

        Route::get('subadminProfile', [ProfileController::class, 'subadminProfile']);

    });
});
