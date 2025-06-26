@extends('layouts.app')

@section('title', $testType->title)

@section('content')


    <div class="header-inner">
        <div class="header">
            <a href="#"><img src="{{ asset('icons/mockTests/home.svg') }}" alt="Home" /></a>
            <h1 class="header-title">
                THI THỬ<br>
                <span class="header-subtitle">{{ $testType->title }}</span>

                @if ($testType->vietnamese_title)
                    <span class="header-subtitle-2">({{ $testType->vietnamese_title }})</span>
                @endif
            </h1>
        </div>
    </div>


    <main class="main-content">
        @if ($question)
            <form method="POST" action="{{ route('submit.answer', [$testType->slug, 'page' => $page]) }}" id="questionForm">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">

                <div class="quiz-container">
                    <div class="audio">
                        <img src="{{ asset('icons/mockTests/audio.svg') }}" style="width: 40px;" alt="Play audio" />
                        <input class="questionText hidden" type="hidden" value="{{ $question->question_text }}"></input>
                    </div>

                    <textarea type="text" name="answer_text" class="instruction-text form-control mt-3" placeholder="Nhập ở đây">
                        </textarea>
                </div>
            </form>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3">
                {{ session('error') }}
            </div>
        @endif

    </main>


    <div class="test-footer">
        {{-- <a href="{{ $page > 1 ? route('start.mock-test', $testType->slug) . '?page=' . ($page - 1) : '#' }}"
                    class="btn btn-round {{ $page <= 1 ? 'disabled' : '' }}" id="prevBtn">
                    <img src="{{ asset('icons/mockTests/arrow-left.svg') }}" alt="Prev" />
                </a> --}}
        <a href="#" class="btn-round" id="nextBtn">
            <img src="{{ asset('icons/mockTests/arrow-right.svg') }}" alt="Next" />
        </a>
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

                $('.audio').on('click', function() {
                    const text = $('.questionText').val();
                    speak(text);
                })

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
