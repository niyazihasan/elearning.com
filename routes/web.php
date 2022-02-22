<?php

use Illuminate\Support\Facades\Route;

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::name('logout.get')->get('/logout', 'Auth\LoginController@logout');

Route::name('dashboard')->match(['get', 'post'], '/dashboard', 'DashboardController@index');

Route::prefix('admin')->middleware(['can:admin'])->group(function () {
    Route::name('admin.index')->match(['get', 'post'], '/index', 'AdminController@index');
    Route::name('teacher.index')->match(['get', 'post'], '/teacher/index', 'TeacherController@index');
    Route::name('teacher.edit')->match(['get', 'post'], '/teacher/edit/{teacher}', 'TeacherController@edit');
    Route::name('discipline.index')->match(['get', 'post'], '/discipline/index', 'DisciplineController@index');
    Route::name('group.index')->match(['get', 'post'], '/group/index', 'GroupController@index');
    Route::name('group.edit')->match(['get', 'post'], '/group/edit/{group}', 'GroupController@edit');
    Route::name('specialty.index')->match(['get', 'post'], '/specialty/index', 'SpecialtyController@index');
    Route::name('specialty.edit')->match(['get', 'post'], '/specialty/edit/{specialty}', 'SpecialtyController@edit');
});

Route::prefix('teacher')->middleware(['can:teacher'])->group(function () {
    Route::name('lecture.index')->match(['get', 'post'], '/lecture/index/{discipline}', 'LectureController@index');
    Route::name('lecture.edit')->match(['get', 'post'], '/lecture/edit/{lecture}', 'LectureController@edit');
    Route::name('lecture.show')->match(['get', 'post'], '/lecture/show/{lecture}', 'LectureController@show');
    Route::name('task.index')->match(['get', 'post'], '/task/index/{discipline}', 'TaskController@index');
    Route::name('presence.index')->match(['get', 'post'], '/presence/index/{discipline}', 'PresenceController@index');
});

Route::prefix('student')->middleware(['can:student'])->group(function () {
    Route::name('student.lecture.index')->match(['get', 'post'], '/lecture/index/{discipline}', 'LectureController@index');
    Route::name('student.lecture.show')->match(['get', 'post'], '/lecture/show/{lecture}', 'LectureController@show');
    Route::name('student.task.show')->match(['get', 'post'], '/task/show/{discipline}', 'TaskController@show');
    Route::name('student.presence.show')->match(['get', 'post'], '/presence/show/{discipline}', 'PresenceController@show');
});

Route::name('user.profile')->match(['get', 'post'], '/users/profile', 'UserController@profile');

Route::name('message.inbox')->match(['get', 'post'], '/messages/inbox', 'MessageController@inbox');
Route::name('message.outbox')->match(['get', 'post'], '/messages/outbox', 'MessageController@outbox');
Route::name('message.show')->match(['get', 'post'], '/messages/show/{message}', 'MessageController@show');

Route::get('/document/{document}/id/{id}', 'DocumentController@show')->name('document.show');
