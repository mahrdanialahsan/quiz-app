@extends('layouts.app')

@section('title', 'Question ' . $questionNumber . ' - PHP Quiz')

@section('content')
<div class="text-center">
    <h2>Question {{ $questionNumber }}</h2>
    <p class="mb-4">{{ $question->question }}</p>
    
    <form id="questionForm">
        @csrf
        <input type="hidden" name="question_id" value="{{ $question->id }}">
        <input type="hidden" name="current_question" value="{{ $questionNumber }}">
        
        <div class="mb-4">
            @foreach($question->options as $option)
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="option_id" id="option{{ $option->id }}" value="{{ $option->id }}">
                <label class="form-check-label" for="option{{ $option->id }}">
                    {{ $option->option }}
                </label>
            </div>
            @endforeach
        </div>
        
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-custom" id="skipBtn">Skip</button>
            <button type="button" class="btn btn-custom" id="nextBtn" disabled>Next</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let selectedOption = null;
    
    // Handle option selection
    $('input[name="option_id"]').change(function() {
        selectedOption = $(this).val();
        $('#nextBtn').prop('disabled', false);
    });
    
    // Handle Next button
    $('#nextBtn').click(function() {
        if (selectedOption) {
            submitAnswer('true');
        } else {
            // If no option selected, save as skip
            submitAnswer('skip');
        }
    });
    
    // Handle Skip button
    $('#skipBtn').click(function() {
        submitAnswer('skip');
    });
    
    function submitAnswer(answerType) {
        const formData = {
            question_id: $('input[name="question_id"]').val(),
            option_id: selectedOption || null,
            ans: answerType
        };
        
        console.log('Submitting answer:', formData); // Debug log
        
        $.ajax({
            url: '{{ route("quiz.submit-answer") }}',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log('Answer saved successfully:', response); // Debug log
                if (response.success) {
                    checkNextQuestion();
                }
            },
            error: function(xhr) {
                console.error('Error submitting answer:', xhr.responseText);
                alert('Error submitting answer. Please try again.');
            }
        });
    }
    
    function checkNextQuestion() {
        const currentQuestion = parseInt($('input[name="current_question"]').val());
        
        $.ajax({
            url: '{{ route("quiz.next-question") }}',
            type: 'POST',
            data: { current_question: currentQuestion },
            dataType: 'json',
            success: function(response) {
                if (response.has_next) {
                    window.location.href = '/question/' + response.next_question;
                } else {
                    window.location.href = '{{ route("quiz.results") }}';
                }
            },
            error: function(xhr) {
                console.error('Error checking next question:', xhr.responseText);
                // Fallback to results page
                window.location.href = '{{ route("quiz.results") }}';
            }
        });
    }
});
</script>
@endsection
