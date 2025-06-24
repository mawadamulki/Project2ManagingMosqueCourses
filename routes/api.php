<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UpdateProfileController;

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





    //     // Admin-only routes
    //     Route::middleware(['role:admin'])->prefix('admin')->group(function () {
    //         Route::get('/dashboard', [AdminController::class, 'dashboard']);
    //         Route::apiResource('/users', AdminController::class);
    //     });

    //     // Teacher-only routes
    //     Route::middleware(['role:teacher'])->prefix('teacher')->group(function () {
    //         Route::get('/classes', [TeacherController::class, 'getClasses']);
    //         Route::post('/assignments', [TeacherController::class, 'createAssignment']);
    //     });

    //     // Student-only routes
    //     Route::middleware(['role:student'])->prefix('student')->group(function () {
    //         Route::get('/grades', [StudentController::class, 'getGrades']);
    //         Route::post('/assignments/submit', [StudentController::class, 'submitAssignment']);
    //     });

    //     // Adminster-only routes
    //     Route::middleware(['role:adminster'])->prefix('adminster')->group(function () {
    //         Route::get('/reports', [AdminsterController::class, 'generateReports']);
    //         Route::post('/schedule', [AdminsterController::class, 'createSchedule']);
    //     });
});
