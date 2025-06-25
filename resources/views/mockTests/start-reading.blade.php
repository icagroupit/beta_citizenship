@extends('layouts.app')

@section('title', $testType->title)

@section('content')
    <div class="container">
        <div class="container">
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
                    <form method="POST" action="{{ route('submit.answer', [$testType->slug, 'page' => $page]) }}"
                        id="questionForm">
                        @csrf
                        <input type="hidden" name="question_id" value="{{ $question->id }}">

                        <div class="quiz-container">
                            <div class="audio">
                                <img src="{{ asset('icons/mockTests/audio.svg') }}" style="width: 40px;" alt="Play audio" />
                            </div>

                            <h1 class="font-sm">
                                {{ $question->question_text }}
                            </h1>

                            <input type="text" name="answer_text" class="instruction-text form-control mt-3"
                                placeholder="Nhấn vào micro và đọc câu">
                        </div>
                    </form>
                @endif

                <div class="test-footer">
                    <a href="{{ $page > 1 ? route('start.mock-test', $testType->slug) . '?page=' . ($page - 1) : '#' }}"
                        class="btn btn-round {{ $page <= 1 ? 'disabled' : '' }}" id="prevBtn">
                        <img src="{{ asset('icons/mockTests/arrow-left.svg') }}" alt="Prev" />
                    </a>
                    <button class="btn btn-round active">
                        <img src="{{ asset('icons/mockTests/micro.svg') }}" alt="">
                    </button>
                    <a href="{{ route('start.mock-test', $testType->slug) }}?page={{ $page + 1 }}" class="btn-round"
                        id="nextBtn">
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
                    $('.option').on('click', function() {
                        $('.option').removeClass('active');
                        $(this).addClass('active');
                        $('#answer_id').val($(this).data('answer'));
                        $('#nextBtn').addClass('active');

                    });

                    $('#nextBtn').on('click', function(e) {
                        e.preventDefault();

                        // if (!$('#answer_id').val()) {
                        //     alert('Vui lòng chọn một đáp án!');
                        //     return;
                        // }

                        $('#questionForm').submit();
                    });
                });
            </script>
        @endpush
    @endpush
