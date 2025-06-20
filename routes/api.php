<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


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


Route::get('/test', function() {
    return response()->json(['message' => 'Test route works']);
});



// Route::middleware(['auth:sanctum'])->group(function () {






//     // Common authenticated routes
//     Route::post('/logout', [AuthController::class, 'logout']);
//     Route::get('/user', [AuthController::class, 'user']);

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
// });
