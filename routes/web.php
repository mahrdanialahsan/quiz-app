<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizController;

// Quiz routes
Route::get('/', [QuizController::class, 'index'])->name('quiz.index');
Route::get('/results', [QuizController::class, 'showResults'])->name('quiz.results');

// AJAX routes
Route::post('/store-name', [QuizController::class, 'storeUserName'])->name('quiz.store-name');
Route::post('/submit-answer', [QuizController::class, 'submitAnswer'])->name('quiz.submit-answer');
Route::post('/get-question', [QuizController::class, 'getQuestion'])->name('quiz.get-question');
Route::get('/get-all-questions', [QuizController::class, 'getAllQuestions'])->name('quiz.get-all-questions');
Route::get('/get-results-ajax', [QuizController::class, 'getResultsAjax'])->name('quiz.get-results-ajax');
Route::get('/get-detailed-results', [QuizController::class, 'getDetailedResults'])->name('quiz.get-detailed-results');
Route::post('/next-question', [QuizController::class, 'getNextQuestion'])->name('quiz.next-question');
Route::post('/reset-quiz', [QuizController::class, 'resetQuiz'])->name('quiz.reset');

// Cookie-based quiz state routes
Route::post('/save-quiz-state', [QuizController::class, 'saveQuizState'])->name('quiz.save-state');
Route::get('/get-quiz-state', [QuizController::class, 'getQuizState'])->name('quiz.get-state');
Route::get('/get-saved-user', [QuizController::class, 'getSavedUserName'])->name('quiz.get-saved-user');
Route::post('/clear-quiz-state', [QuizController::class, 'clearQuizState'])->name('quiz.clear-state');
