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
            <div class="question-header">
                <p class="text-center font-bold">Câu hỏi {{ $page }}/{{ $total }}</p>
            </div>

            @if ($question)
                <div class="audio shadow">
                    <img src="{{ asset('icons/mockTests/audio.svg') }}" style="width: 40px;" alt="Play audio" />
                    <input class="questionText hidden" type="hidden" value="{{ $question->question_text }}"></input>
                </div>

                <form method="POST" action="{{ route('submit.answer', [$testType->slug, 'page' => $page]) }}"
                    id="questionForm">
                    @csrf
                    <div class="options-container">
                        <input type="hidden" name="question_id" value="{{ $question->id }}">
                        <input type="hidden" name="answer_id" id="answer_id">
                        @foreach ($question->answers as $answer)
                            <div class="option" data-answer="{{ $answer->id }}">
                                {{ $answer->answer_text }}
                            </div>
                        @endforeach
                    </div>
                </form>

                <div class="test-footer">
                    <a href="{{ $page > 1 ? route('start.mock-test', $testType->slug) . '?page=' . ($page - 1) : '#' }}"
                        class="btn btn-round {{ $page <= 1 ? 'disabled' : '' }}" id="prevBtn">
                        <img src="{{ asset('icons/mockTests/arrow-left.svg') }}" alt="Prev" />
                    </a>

                    <a href="{{ route('start.mock-test', $testType->slug) }}?page={{ $page + 1 }}" class="btn-round"
                        id="nextBtn">
                        <img src="{{ asset('icons/mockTests/arrow-right.svg') }}" alt="Next" />
                    </a>

                </div>
            @endif
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

                $('.audio').on('click', function() {
                    const text = $('.questionText').val();
                    console.log('speak', text);
                    speak(text);
                })

                $('#nextBtn').on('click', function(e) {
                    e.preventDefault();

                    if (!$('#answer_id').val()) {
                        alert('Vui lòng chọn một đáp án!');
                        return;
                    }

                    $('#questionForm').submit();
                });
            });
        </script>
    @endpush
@endpush
