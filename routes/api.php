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
Route::get('/getAllAnnouncements', [AnnouncementController::class, 'getAllAnnouncements']);


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

    //_______________________CourseController___________________________________
    Route::post('/createCourseByAdmin', [CourseController::class, 'createCourseByAdmin']);
    Route::post('/updateCourseByAdmin', [CourseController::class, 'updateCourseByAdmin']); //غير مكتمل بسبب نقص المعلومات

    //_______________________AnnouncementController___________________________________

    Route::post('/createAnnouncementCourse', [AnnouncementController::class, 'createAnnouncementCourse']);
    // Route::post('/createMultipleAnnouncements', [AnnouncementController::class, 'createMultipleAnnouncements']);
    Route::delete('/deleteAnnouncementCourse/{id}', [AnnouncementController::class, 'deleteAnnouncementCourse']);

    //_______________________CurriculumPlanController___________________________________

    Route::post('/addCurriculumPlanToLevel/{levelId}', [CurriculumPlanController::class, 'addCurriculumPlanToLevel']);
    Route::get('/getCurriculumPlanByLevel/{levelId}', [CurriculumPlanController::class, 'getCurriculumPlanByLevel']);
    Route::post('/updateCurriculumPlanForLevel/{levelId}/{sessionId}', [CurriculumPlanController::class, 'updateCurriculumPlanForLevel']);

    // Admin-only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        Route::get('/getJoiningRequests/{courseID}', [JoiningRequestController::class, 'getJoiningRequests']);
        Route::get('/getStudentInfo/{studentID}', [JoiningRequestController::class, 'getStudentInfo']);
        Route::post('enrollStudentToLevel', [JoiningRequestController::class, 'enrollStudentToLevel']);
    });



    // Teacher-only routes
    Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {});



    // Student-only routes
    Route::middleware(['role:student'])->prefix('student')->group(function () {
        Route::get('/createJoiningRequest/{courseID}', [JoiningRequestController::class, 'createJoiningRequest']);
    });



    // Adminster-only routes
    Route::middleware(['role:subadmin'])->prefix('subadmin')->group(function () {});
});
