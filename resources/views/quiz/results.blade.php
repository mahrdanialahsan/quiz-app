@extends('layouts.app')

@section('title', 'Results - PHP Quiz')

@section('content')
<div class="text-center">
    <h2>Result Page</h2>
    <p class="mb-4">Here are your quiz results:</p>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Correct Ans</h5>
                    <h3 class="card-text">{{ $results->correct_count ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Wrong Ans</h5>
                    <h3 class="card-text">{{ $results->wrong_count ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Skip Ans</h5>
                    <h3 class="card-text">{{ $results->skip_count ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-custom" id="restartBtn">Take Quiz Again</button>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#restartBtn').click(function() {
        $.ajax({
            url: '{{ route("quiz.reset") }}',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = '{{ route("quiz.index") }}';
                }
            },
            error: function(xhr) {
                console.error('Error resetting quiz:', xhr.responseText);
                // Fallback redirect
                window.location.href = '{{ route("quiz.index") }}';
            }
        });
    });
});
</script>
@endsection
