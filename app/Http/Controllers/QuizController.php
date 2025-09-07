<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\UserAnswer;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    /**
     * Show the welcome page with name input.
     */
    public function index()
    {
        return view('quiz.welcome');
    }

    /**
     * Store user name in session.
     */
    public function storeUserName(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        session(['user_name' => $request->name]);

        return response()->json(['success' => true, 'message' => 'Name stored successfully']);
    }

    /**
     * Show a specific question.
     */
    public function showQuestion($questionNumber)
    {
        $question = Question::with('options')->find($questionNumber);

        if (!$question) {
            return redirect()->route('quiz.results');
        }

        return view('quiz.question', compact('question', 'questionNumber'));
    }

    /**
     * Get question data via AJAX.
     */
    public function getQuestion(Request $request): JsonResponse
    {
        $questionNumber = $request->input('question_number', 1);
        $question = Question::with(['options' => function($query) {
            $query->select('id', 'question_id', 'option'); // Exclude is_correct, created_at, updated_at
        }])->select('id', 'question')->find($questionNumber); // Exclude created_at, updated_at from question

        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        return response()->json([
            'question' => $question,
            'question_number' => $questionNumber
        ]);
    }

    /**
     * Get all questions for single page loading.
     */
    public function getAllQuestions(): JsonResponse
    {
        $questions = Question::with(['options' => function($query) {
            $query->select('id', 'question_id', 'option');
        }])->select('id', 'question')->get();

        return response()->json([
            'questions' => $questions,
            'total_questions' => $questions->count()
        ]);
    }

    /**
     * Submit answer via AJAX.
     */
    public function submitAnswer(Request $request): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|integer|exists:questions,id',
            'option_id' => 'nullable|integer|exists:question_options,id',
            'action' => 'required|in:submit,skip'
        ]);

        $userName = session('user_name');

        if (!$userName) {
            return response()->json(['error' => 'User not found'], 400);
        }

        // Get or create user
        $user = User::firstOrCreate(['name' => $userName]);


        $actualAnswer = 'skip'; // Default to skip

        if ($request->action === 'submit' && $request->option_id) {
            // User submitted an answer - check if it's correct
            $selectedOption = QuestionOption::find($request->option_id);
            if ($selectedOption && $selectedOption->is_correct) {
                $actualAnswer = 'true';
            } else {
                $actualAnswer = 'false';
            }
        } else if ($request->action === 'skip') {
            // User skipped the question
            $actualAnswer = 'skip';
        }

        // Check if answer already exists
        $existingAnswer = UserAnswer::where('user_id', $user->id)
            ->where('question_id', $request->question_id)
            ->first();

        if ($existingAnswer) {
            // Update existing answer
            $existingAnswer->update([
                'option_id' => $request->option_id,
                'ans' => $actualAnswer
            ]);
            \Log::info('Updated existing answer for user: ' . $user->id . ', question: ' . $request->question_id . ', ans: ' . $actualAnswer);
        } else {
            // Create new answer
            UserAnswer::create([
                'user_id' => $user->id,
                'question_id' => $request->question_id,
                'option_id' => $request->option_id,
                'ans' => $actualAnswer
            ]);
            \Log::info('Created new answer for user: ' . $user->id . ', question: ' . $request->question_id . ', ans: ' . $actualAnswer);
        }

        return response()->json(['success' => true, 'message' => 'Answer saved successfully']);
    }

    /**
     * Get next question number.
     */
    public function getNextQuestion(Request $request): JsonResponse
    {
        $currentQuestion = $request->input('current_question', 1);
        $nextQuestion = $currentQuestion + 1;

        $questionExists = Question::find($nextQuestion);

        if ($questionExists) {
            return response()->json([
                'next_question' => $nextQuestion,
                'has_next' => true
            ]);
        }

        return response()->json([
            'next_question' => null,
            'has_next' => false
        ]);
    }

    /**
     * Show results page.
     */
    public function showResults()
    {
        $userName = session('user_name');

        if (!$userName) {
            return redirect()->route('quiz.index');
        }

        $user = User::where('name', $userName)->first();

        if (!$user) {
            return redirect()->route('quiz.index');
        }

        // Use SQL aggregate functions to get counts
        $results = DB::table('user_answers')
            ->where('user_id', $user->id)
            ->selectRaw('
                SUM(CASE WHEN ans = "true" THEN 1 ELSE 0 END) as correct_count,
                SUM(CASE WHEN ans = "false" THEN 1 ELSE 0 END) as wrong_count,
                SUM(CASE WHEN ans = "skip" THEN 1 ELSE 0 END) as skip_count
            ')
            ->first();

        return view('quiz.results', compact('results'));
    }

    /**
     * Get results as JSON for AJAX.
     */
    public function getResultsAjax(): JsonResponse
    {
        $userName = session('user_name');

        if (!$userName) {
            return response()->json(['error' => 'User not found'], 400);
        }

        $user = User::where('name', $userName)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 400);
        }

        // Use SQL aggregate functions to get counts
        $results = DB::table('user_answers')
            ->where('user_id', $user->id)
            ->selectRaw('
                SUM(CASE WHEN ans = "true" THEN 1 ELSE 0 END) as correct_count,
                SUM(CASE WHEN ans = "false" THEN 1 ELSE 0 END) as wrong_count,
                SUM(CASE WHEN ans = "skip" THEN 1 ELSE 0 END) as skip_count
            ')
            ->first();

        return response()->json([
            'correct_count' => $results->correct_count ?? 0,
            'wrong_count' => $results->wrong_count ?? 0,
            'skip_count' => $results->skip_count ?? 0,
            'total_answered' => ($results->correct_count ?? 0) + ($results->wrong_count ?? 0) + ($results->skip_count ?? 0)
        ]);
    }

    /**
     * Get detailed questions and answers for results page.
     */
    public function getDetailedResults(): JsonResponse
    {
        $userName = session('user_name');
        
        if (!$userName) {
            return response()->json(['error' => 'User not found'], 400);
        }

        $user = User::where('name', $userName)->first();
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 400);
        }

        // Get detailed results with questions and answers
        $detailedResults = DB::table('user_answers')
            ->join('questions', 'user_answers.question_id', '=', 'questions.id')
            ->leftJoin('question_options', 'user_answers.option_id', '=', 'question_options.id')
            ->where('user_answers.user_id', $user->id)
            ->select(
                'questions.id as question_id',
                'questions.question',
                'user_answers.ans',
                'question_options.option as selected_option'
            )
            ->orderBy('questions.id')
            ->get();

        return response()->json([
            'questions' => $detailedResults
        ]);
    }

    /**
     * Reset quiz session.
     */
    public function resetQuiz(): JsonResponse
    {
        session()->forget('user_name');
        return response()->json(['success' => true]);
    }

    /**
     * Save quiz state to cookie.
     */
    public function saveQuizState(Request $request): JsonResponse
    {
        $userName = session('user_name');

        if (!$userName) {
            return response()->json(['error' => 'User not found'], 400);
        }

        $quizState = [
            'user_name' => $userName,
            'current_question' => $request->input('current_question', 1),
            'answers' => $request->input('answers', []),
            'timestamp' => now()->timestamp
        ];

        // Set quiz state cookie for 7 days
        $quizCookie = cookie('quiz_state', json_encode($quizState), 60 * 24 * 7);

        // Also save user name in separate cookie for 30 days
        $userCookie = cookie('quiz_user_name', $userName, 60 * 24 * 30);

        return response()->json(['success' => true])->withCookie($quizCookie)->withCookie($userCookie);
    }

    /**
     * Get quiz state from cookie.
     */
    public function getQuizState(Request $request): JsonResponse
    {
        $quizStateCookie = $request->cookie('quiz_state');

        if (!$quizStateCookie) {
            return response()->json(['has_state' => false]);
        }

        $quizState = json_decode($quizStateCookie, true);

        // Check if the state is not too old (7 days)
        if (!$quizState || (now()->timestamp - $quizState['timestamp']) > (60 * 24 * 7)) {
            return response()->json(['has_state' => false]);
        }

        return response()->json([
            'has_state' => true,
            'quiz_state' => $quizState
        ]);
    }

    /**
     * Get saved user name from cookie.
     */
    public function getSavedUserName(Request $request): JsonResponse
    {
        $userName = $request->cookie('quiz_user_name');

        if (!$userName) {
            return response()->json(['has_user' => false]);
        }

        return response()->json([
            'has_user' => true,
            'user_name' => $userName
        ]);
    }

    /**
     * Clear quiz state cookie.
     */
    public function clearQuizState(): JsonResponse
    {
        $quizCookie = cookie('quiz_state', '', -1); // Expire immediately
        $userCookie = cookie('quiz_user_name', '', -1); // Also clear user name
        return response()->json(['success' => true])->withCookie($quizCookie)->withCookie($userCookie);
    }
}
