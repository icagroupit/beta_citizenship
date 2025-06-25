@extends('layouts.app')

@section('title', $testType->title)

@section('content')
    <div class="container">
        <div class="header">
            <a href="#"><img src="{{ asset('icons/mockTests/home.svg') }}" alt="Home" /></a>
            <h1 class="header-title">
                THI THỬ<br>
                {{ $testType->title }}
                @if ($testType->vietnamese_title)
                    ({{ $testType->vietnamese_title }})
                @endif
            </h1>
        </div>

        <main class="main-content">
            @if ($question)
                <form method="POST" action="{{ route('submit.answer', [$testType->slug, 'page' => $page]) }}"
                    id="questionForm">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">

                    <div class="quiz-container">
                        <div class="audio">
                            <img src="{{ asset('icons/mockTests/audio.svg') }}" style="width: 40px;" alt="Play audio" />
                        </div>

                        <textarea type="text" name="answer_text" class="instruction-text form-control mt-3" placeholder="Nhập ở đây">
                        </textarea>
                    </div>
                </form>
            @endif

            <div class="test-footer">
                <a href="#" class="btn-round" id="nextBtn">
                    <img src="{{ asset('icons/mockTests/arrow-right.svg') }}" alt="Next" />
                </a>

            </div>
        </main>
    </div>
@endsection

@push('scripts')
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('textarea[name="answer_text"]').val('');
                $('textarea[name="answer_text"]').on('input', function() {
                    if ($(this).val().trim().length > 0) {
                        $('#nextBtn').addClass('active');
                    } else {
                        $('#nextBtn').removeClass('active');
                    }
                });

                $('#nextBtn').on('click', function(e) {
                    if (!$('textarea[name="answer_text"]').val().trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập câu trả lời!');
                        return;
                    }

                    $('#questionForm').submit();
                });
            });
        </script>
    @endpush
@endpush
