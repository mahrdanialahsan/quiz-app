@extends('layouts.app')

@section('title', 'PHP Quiz')

@section('content')
<div class="text-center">
    <!-- Welcome Section -->
    <div id="welcome-section">
        <h2 class="mb-4">Welcome to PHP Quiz</h2>
        <p class="mb-4">Test your PHP knowledge with our interactive quiz!</p>
        
        <!-- Name Input Form (for new users) -->
        <div id="name-input-section">
            <form id="nameForm">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Enter your name</label>
                    <input type="text" class="form-control" id="name" name="name" required maxlength="255" placeholder="Your name">
                </div>
                <button type="submit" class="btn btn-custom">Start Quiz</button>
            </form>
        </div>
        
        <!-- Quick Start Section (for existing users) -->
        <div id="quick-start-section" style="display: none;">
            <div class="mb-4">
                <p class="mb-3">Welcome back, <span id="saved-user-name" class="fw-bold"></span>!</p>
                <button id="start-quiz-btn" class="btn btn-custom me-2">Start New Quiz</button>
                <button id="enter-different-name-btn" class="btn btn-outline-secondary">Enter Different Name</button>
            </div>
        </div>
        
    </div>
    
    <!-- Quiz Section (Hidden Initially) -->
    <div id="quiz-section" style="display: none;">
        <h2 id="quiz-title">PHP Quiz</h2>
        <p class="mb-4">Welcome, <span id="user-name"></span>!</p>
        
        <!-- Progress Bar -->
        <div class="progress mb-4" style="height: 25px;">
            <div class="progress-bar" role="progressbar" id="progress-bar" style="width: 0%">
                <span id="progress-text">Question 0 of 4</span>
            </div>
        </div>
        
        <!-- Question Container -->
        <div id="question-container">
            <h3 id="question-number">Question 1</h3>
            <p id="question-text" class="mb-4"></p>
            
            <form id="question-form">
                @csrf
                <input type="hidden" id="current-question-id" name="question_id">
                <input type="hidden" id="current-question-number" name="question_number">
                
                <div id="options-container" class="mb-4">
                    <!-- Options will be loaded here -->
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-custom" id="skip-btn">Skip</button>
                    <button type="button" class="btn btn-custom" id="next-btn" disabled>Next</button>
                </div>
            </form>
        </div>
        
        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading questions...</p>
        </div>
        
        <!-- Results Container -->
        <div id="results-container" style="display: none;">
            <h3 id="results-title">Result Page</h3>
            <p class="mb-4">Here are your quiz results:</p>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Correct Ans</h5>
                            <h3 class="card-text" id="correct-count">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Wrong Ans</h5>
                            <h3 class="card-text" id="wrong-count">0</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Skip Ans</h5>
                            <h3 class="card-text" id="skip-count">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            
        <div class="mb-4">
            <h4>Total Questions: <span id="total-questions">4</span></h4>
            <h4>Answered: <span id="answered-count">0</span></h4>
        </div>
        
        <!-- Detailed Questions and Answers -->
        <div id="detailed-results" class="mb-4">
            <h4>Question Details:</h4>
            <div id="questions-details">
                <!-- Questions will be loaded here -->
            </div>
        </div>
        
        <div class="mb-3">
            <button type="button" class="btn btn-custom" id="restart-btn">Take Quiz Again</button>
        </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let questions = [];
    let currentQuestionIndex = 0;
    let totalQuestions = 0;
    let selectedOption = null;
    let answers = {};
    let userName = '';
    
    // Check for existing user and quiz state on page load
    checkForExistingUserAndState();
    
    function checkForExistingUserAndState() {
        // First check if we have a saved user
        $.ajax({
            url: '{{ route("quiz.get-saved-user") }}',
            type: 'GET',
            dataType: 'json',
            success: function(userResponse) {
                if (userResponse.has_user) {
                    // User exists, show quick start section
                    showQuickStartSection(userResponse.user_name);
                    // Also check quiz state
                    checkQuizStateForUser(userResponse.user_name);
                } else {
                    // No saved user, show name input section
                    showNameInputSection();
                }
            },
            error: function(xhr) {
                console.error('Error checking saved user:', xhr.responseText);
                showNameInputSection();
            }
        });
    }
    
    function showQuickStartSection(userName) {
        $('#saved-user-name').text(userName);
        $('#name-input-section').hide();
        $('#quick-start-section').show();
    }
    
    function showNameInputSection() {
        $('#name-input-section').show();
        $('#quick-start-section').hide();
    }
    
    function checkQuizStateForUser(savedUserName) {
        // Check for incomplete quiz state
        $.ajax({
            url: '{{ route("quiz.get-state") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.has_state) {
                    const quizState = response.quiz_state;
                    // Check if this is an incomplete quiz
                    if (quizState.current_question <= 4) {
                        // Quiz is incomplete, show resume option
                        showResumeQuizOption(quizState);
                    }
                }
            },
            error: function(xhr) {
                console.error('Error checking quiz state:', xhr.responseText);
            }
        });
    }
    
    function showResumeQuizOption(quizState) {
        // Add a button to resume incomplete quiz
        const resumeButton = `
            <div class="mt-3">
                <button id="resume-incomplete-quiz-btn" class="btn btn-warning">
                    <i class="fas fa-play me-2"></i>Resume Incomplete Quiz (Question ${quizState.current_question})
                </button>
            </div>
        `;
        $('#quick-start-section').append(resumeButton);
        
        $('#resume-incomplete-quiz-btn').click(function() {
            resumeQuiz(quizState);
        });
    }
    
    function resumeQuiz(quizState) {
        userName = quizState.user_name;
        answers = quizState.answers;
        
        // Find the next unanswered question
        currentQuestionIndex = quizState.current_question - 1;
        
        console.log('Resuming quiz from question:', currentQuestionIndex + 1);
        console.log('Saved answers:', answers);
        
        // Store name in session
        $.ajax({
            url: '{{ route("quiz.store-name") }}',
            type: 'POST',
            data: {
                name: userName,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#user-name').text(userName);
                    $('#welcome-section').hide();
                    $('#quiz-section').show();
                    loadAllQuestionsAndResume();
                }
            },
            error: function(xhr) {
                console.error('Error storing name:', xhr.responseText);
                alert('Error resuming quiz. Please try again.');
            }
        });
    }
    
    function loadAllQuestionsAndResume() {
        $('#loading-spinner').show();
        $('#question-container').hide();
        
        $.ajax({
            url: '{{ route("quiz.get-all-questions") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                questions = response.questions;
                totalQuestions = questions.length;
                
                $('#loading-spinner').hide();
                $('#question-container').show();
                
                // Resume from the saved question index
                showQuestion(currentQuestionIndex);
                updateProgress();
            },
            error: function(xhr) {
                console.error('Error loading questions:', xhr.responseText);
                $('#loading-spinner').hide();
                alert('Error loading questions. Please try again.');
            }
        });
    }

    // Handle name form submission
    $('#nameForm').submit(function(e) {
        e.preventDefault();
        
        userName = $('#name').val().trim();
        if (!userName) {
            alert('Please enter your name');
            return;
        }
        
        // Store name via AJAX
        $.ajax({
            url: '{{ route("quiz.store-name") }}',
            type: 'POST',
            data: {
                name: userName,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#user-name').text(userName);
                    $('#welcome-section').hide();
                    $('#quiz-section').show();
                    loadAllQuestions();
                }
            },
            error: function(xhr) {
                console.error('Error storing name:', xhr.responseText);
                alert('Error storing name. Please try again.');
            }
        });
    });
    
    // Handle "Start Quiz" button click (for existing users)
    $(document).on('click', '#start-quiz-btn', function(e) {
        e.preventDefault();
        
        // Check if we have a saved user
        $.ajax({
            url: '{{ route("quiz.get-saved-user") }}',
            type: 'GET',
            dataType: 'json',
            success: function(userResponse) {
                if (userResponse.has_user) {
                    // User exists, start new quiz directly
                    startNewQuizForExistingUser(userResponse.user_name);
                } else {
                    // No saved user, show name input
                    alert('Please enter your name first');
                }
            },
            error: function(xhr) {
                console.error('Error checking saved user:', xhr.responseText);
                alert('Please enter your name first');
            }
        });
    });
    
    // Handle "Start Quiz" button click (for existing users)
    $(document).on('click', '#start-quiz-btn', function(e) {
        e.preventDefault();
        
        // Check if we have a saved user
        $.ajax({
            url: '{{ route("quiz.get-saved-user") }}',
            type: 'GET',
            dataType: 'json',
            success: function(userResponse) {
                if (userResponse.has_user) {
                    // User exists, start new quiz directly
                    startNewQuizForExistingUser(userResponse.user_name);
                } else {
                    // No saved user, show name input
                    alert('Please enter your name first');
                }
            },
            error: function(xhr) {
                console.error('Error checking saved user:', xhr.responseText);
                alert('Please enter your name first');
            }
        });
    });
    
    // Handle "Enter Different Name" button click
    $(document).on('click', '#enter-different-name-btn', function(e) {
        e.preventDefault();
        
        // Clear user cookies and show name input
        $.ajax({
            url: '{{ route("quiz.clear-state") }}',
            type: 'POST',
            dataType: 'json',
            success: function() {
                console.log('User state cleared, showing name input');
                showNameInputSection();
            },
            error: function(xhr) {
                console.error('Error clearing user state:', xhr.responseText);
                showNameInputSection();
            }
        });
    });
    
    function startNewQuizForExistingUser(userName) {
        console.log('Starting new quiz for existing user:', userName);
        
        // First clear any existing quiz state
        $.ajax({
            url: '{{ route("quiz.clear-state") }}',
            type: 'POST',
            dataType: 'json',
            success: function() {
                console.log('Quiz state cleared, storing name...');
                
                // Then store name in session
                $.ajax({
                    url: '{{ route("quiz.store-name") }}',
                    type: 'POST',
                    data: {
                        name: userName,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            console.log('Name stored successfully, starting quiz...');
                            $('#user-name').text(userName);
                            $('#welcome-section').hide();
                            $('#quiz-section').show();
                            loadAllQuestions();
                        } else {
                            console.error('Failed to store name:', response);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error storing name:', xhr.responseText);
                        alert('Error starting quiz. Please try again.');
                    }
                });
            },
            error: function(xhr) {
                console.error('Error clearing quiz state:', xhr.responseText);
                // Continue anyway
                startNewQuizForExistingUser(userName);
            }
        });
    }
    
    function loadAllQuestions() {
        $('#loading-spinner').show();
        $('#question-container').hide();
        
        // Reset quiz state for new quiz
        currentQuestionIndex = 0;
        selectedOption = null;
        answers = {};
        
        $.ajax({
            url: '{{ route("quiz.get-all-questions") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Questions loaded successfully:', response);
                questions = response.questions;
                totalQuestions = response.total_questions;
                $('#total-questions').text(totalQuestions);
                
                console.log('Questions array set to:', questions);
                console.log('Total questions:', totalQuestions);
                
                $('#loading-spinner').hide();
                $('#question-container').show();
                
                // Show first question
                console.log('About to show question 0');
                showQuestion(0);
                updateProgress();
            },
            error: function(xhr) {
                console.error('Error loading questions:', xhr.responseText);
                alert('Error loading questions. Please refresh the page.');
            }
        });
    }
    
    function showQuestion(index) {
        console.log('showQuestion called with index:', index);
        console.log('questions array:', questions);
        console.log('questions.length:', questions.length);
        
        if (index >= questions.length) {
            console.log('Index >= questions.length, showing results');
            showResults();
            return;
        }
        
        const question = questions[index];
        console.log('Current question:', question);
        currentQuestionIndex = index;
        
        $('#current-question-id').val(question.id);
        $('#current-question-number').val(index + 1);
        $('#question-number').text('Question ' + (index + 1));
        $('#question-text').text(question.question);
        
        // Clear previous options
        $('#options-container').empty();
        selectedOption = null;
        $('#next-btn').prop('disabled', true);
        
        // Add options
        question.options.forEach(function(option) {
            const optionHtml = `
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="option_id" id="option${option.id}" value="${option.id}">
                    <label class="form-check-label" for="option${option.id}">
                        ${option.option}
                    </label>
                </div>
            `;
            $('#options-container').append(optionHtml);
        });
        
        // Check if we have a saved answer for this question (for resume functionality)
        const questionId = question.id.toString();
        if (answers[questionId] && answers[questionId].option_id) {
            selectedOption = answers[questionId].option_id;
            $(`input[value="${selectedOption}"]`).prop('checked', true);
            $('#next-btn').prop('disabled', false);
            console.log('Restored answer for question', questionId, ':', selectedOption);
        }
        
        // Bind option change events
        $('input[name="option_id"]').change(function() {
            selectedOption = $(this).val();
            $('#next-btn').prop('disabled', false);
        });
        
        updateProgress();
    }
    
    function updateProgress() {
        const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
        $('#progress-bar').css('width', progress + '%');
        $('#progress-text').text(`Question ${currentQuestionIndex + 1} of ${totalQuestions}`);
    }
    
    function submitAnswer(action) {
        const questionId = $('#current-question-id').val();
        const formData = {
            question_id: questionId,
            option_id: selectedOption || null,
            action: action // Frontend only sends action, backend determines ans
        };
        
        console.log('Submitting answer:', formData);
        
        $.ajax({
            url: '{{ route("quiz.submit-answer") }}',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Answer saved successfully:', response);
                // Store answer locally (for fallback calculation)
                answers[questionId] = {
                    option_id: selectedOption,
                    action: action
                };
                
                // Save quiz state to cookie
                saveQuizStateToCookie();
                
                // Move to next question
                showQuestion(currentQuestionIndex + 1);
            },
            error: function(xhr) {
                console.error('Error submitting answer:', xhr.responseText);
                alert('Error submitting answer. Please try again.');
            }
        });
    }
    
    function saveQuizStateToCookie() {
        // Save the NEXT question number (the question user will see next)
        const nextQuestionNumber = currentQuestionIndex + 2; // +1 for 1-based, +1 for next question
        
        const quizState = {
            user_name: userName,
            current_question: nextQuestionNumber,
            answers: answers,
            timestamp: Math.floor(Date.now() / 1000)
        };
        
        console.log('Saving quiz state - next question will be:', nextQuestionNumber);
        
        $.ajax({
            url: '{{ route("quiz.save-state") }}',
            type: 'POST',
            data: {
                current_question: quizState.current_question,
                answers: quizState.answers,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                console.log('Quiz state saved to cookie');
            },
            error: function(xhr) {
                console.error('Error saving quiz state:', xhr.responseText);
            }
        });
    }
    
    function showResults() {
        $('#question-container').hide();
        $('#results-container').show();
        
        // Update results title to show current user
        $('#results-title').text(`Quiz Results for ${userName}`);
        
        // Save completed quiz state instead of clearing it
        saveCompletedQuizState();
        
        // Get actual results from backend
        $.ajax({
            url: '{{ route("quiz.get-results-ajax") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                $('#correct-count').text(response.correct_count || 0);
                $('#wrong-count').text(response.wrong_count || 0);
                $('#skip-count').text(response.skip_count || 0);
                $('#answered-count').text(response.total_answered || 0);
                
                // Load detailed questions and answers
                loadDetailedResults();
            },
            error: function(xhr) {
                console.error('Error getting results:', xhr.responseText);
                // Fallback to local calculation
                calculateLocalResults();
            }
        });
    }
    
    function saveCompletedQuizState() {
        const quizState = {
            user_name: userName,
            current_question: 5, // Mark as completed (beyond question 4)
            answers: answers,
            timestamp: Math.floor(Date.now() / 1000)
        };
        
        console.log('Saving completed quiz state');
        
        $.ajax({
            url: '{{ route("quiz.save-state") }}',
            type: 'POST',
            data: {
                current_question: quizState.current_question,
                answers: quizState.answers,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(response) {
                console.log('Completed quiz state saved to cookie');
            },
            error: function(xhr) {
                console.error('Error saving completed quiz state:', xhr.responseText);
            }
        });
    }
    
    function loadDetailedResults() {
        console.log('Loading detailed results...');
        $.ajax({
            url: '{{ route("quiz.get-detailed-results") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Detailed results loaded:', response);
                displayDetailedResults(response.questions);
            },
            error: function(xhr) {
                console.error('Error loading detailed results:', xhr.responseText);
            }
        });
    }
    
    function displayDetailedResults(questions) {
        console.log('Displaying detailed results for questions:', questions);
        let html = '';
        
        if (!questions || questions.length === 0) {
            html = '<p class="text-muted">No detailed results available.</p>';
            $('#questions-details').html(html);
            return;
        }
        
        questions.forEach(function(item, index) {
            let statusClass = '';
            let statusText = '';
            let statusIcon = '';
            
            if (item.ans === 'true') {
                statusClass = 'border-success bg-light';
                statusText = 'Correct';
                statusIcon = '✓';
            } else if (item.ans === 'false') {
                statusClass = 'border-danger bg-light';
                statusText = 'Wrong';
                statusIcon = '✗';
            } else {
                statusClass = 'border-warning bg-light';
                statusText = 'Skipped';
                statusIcon = '○';
            }
            
            html += `
                <div class="card mb-3 ${statusClass}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">Question ${index + 1}</h6>
                            <span class="badge ${item.ans === 'true' ? 'bg-success' : item.ans === 'false' ? 'bg-danger' : 'bg-warning text-dark'}">
                                ${statusIcon} ${statusText}
                            </span>
                        </div>
                        <p class="card-text mb-2"><strong>Q:</strong> ${item.question}</p>
                        <p class="card-text mb-0">
                            <strong>Your Answer:</strong> 
                            ${item.selected_option ? item.selected_option : 'Skipped'}
                        </p>
                    </div>
                </div>
            `;
        });
        
        $('#questions-details').html(html);
    }
    
    function calculateLocalResults() {
        // Fallback: Calculate results from local answers
        let correctCount = 0;
        let wrongCount = 0;
        let skipCount = 0;
        
        Object.values(answers).forEach(function(answer) {
            if (answer.ans === 'true') correctCount++;
            else if (answer.ans === 'false') wrongCount++;
            else if (answer.ans === 'skip') skipCount++;
        });
        
        $('#correct-count').text(correctCount);
        $('#wrong-count').text(wrongCount);
        $('#skip-count').text(skipCount);
        $('#answered-count').text(correctCount + wrongCount + skipCount);
    }
    
    // Event handlers
    $('#next-btn').click(function() {
        if (selectedOption) {
            submitAnswer('submit'); // Backend will determine if it's correct or wrong
        } else {
            submitAnswer('skip');
        }
    });
    
    $('#skip-btn').click(function() {
        submitAnswer('skip');
    });
    
    $('#restart-btn').click(function() {
        // Clear current session and cookie
        $.ajax({
            url: '{{ route("quiz.reset") }}',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Clear quiz state cookie
                    $.ajax({
                        url: '{{ route("quiz.clear-state") }}',
                        type: 'POST',
                        dataType: 'json',
                        success: function() {
                            console.log('Quiz state cookie cleared');
                            
                            // Reset everything
                            questions = [];
                            currentQuestionIndex = 0;
                            selectedOption = null;
                            answers = {};
                            userName = '';
                            
                            // Show welcome section again
                            $('#quiz-section').hide();
                            $('#results-container').hide();
                            $('#welcome-section').show();
                            $('#name').val('');
                            
                            // Check for any existing quiz state after clearing
                            setTimeout(function() {
                                checkForExistingUserAndState();
                            }, 100);
                        }
                    });
                }
            },
            error: function(xhr) {
                console.error('Error resetting quiz:', xhr.responseText);
                // Fallback reset
                location.reload();
            }
        });
    });
});
</script>
@endsection
