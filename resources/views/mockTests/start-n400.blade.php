@extends('layouts.app')

@section('title', $testType->title)

@section('content')
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
            @if ($question && $question->type == 'text')
                <form method="POST" action="{{ route('submit.answer', [$testType->slug, 'page' => $page]) }}"
                    id="questionForm">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">

                    <div class="quiz-container">
                        <div class="audio">
                            <img src="{{ asset('icons/mockTests/audio.svg') }}" style="width: 40px;" alt="Play audio" />
                            <input class="questionText hidden" type="hidden"
                                value="{{ $question->question_text }}"></input>
                        </div>

                        <textarea type="text" name="answer_text" class="instruction-text form-control mt-3" placeholder="Nhập ở đây">
                        </textarea>
                    </div>
                </form>
            @endif

            @if ($question && $question->type === 'multiple_choice')
                <form method="POST" action="{{ route('submit.answer', [$testType->slug, 'page' => $page]) }}"
                    id="questionForm">
                    @csrf
                    <input type="hidden" name="question_id" value="{{ $question->id }}">
                    <input type="hidden" name="answer_id" id="answer_id">

                    <div class="quiz-container">
                        <div class="audio">
                            <img src="{{ asset('icons/mockTests/audio.svg') }}" style="width: 40px;" alt="Play audio" />
                            <input class="questionText hidden" type="hidden"
                                value="{{ $question->question_text }}"></input>
                        </div>

                        <div class="radio-options bg-light p-4 rounded">
                            @foreach ($question->answers as $answer)
                                <div class="form-check mb-2 d-flex gap-2 justify-content-center align-items-center">
                                    <input class="form-check-input" type="radio" name="answer_id"
                                        id="answer{{ $answer->id }}" value="{{ $answer->id }}">
                                    <label class="form-check-label radio-label font-sm" for="answer{{ $answer->id }}">
                                        {{ $answer->answer_text }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </form>
            @endif

            <div class="test-footer">
                <button class="btn btn-round" id="prevBtn">
                    <img src="{{ asset('icons/mockTests/arrow-left.svg') }}" alt="Prev" />
                </button>
                <button class="btn btn-round" id="nextBtn">
                    <img src="{{ asset('icons/mockTests/arrow-right.svg') }}" alt="Next" />
                </button>

            </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {

            const isTextType = $('textarea[name="answer_text"]').length > 0;

            if (isTextType) {
                $('textarea[name="answer_text"]').val('');
                $('textarea[name="answer_text"]').on('input', function() {
                    $('#nextBtn').toggleClass('active', $(this).val().trim().length > 0);
                });

                $('#nextBtn').on('click', function(e) {
                    if (!$('textarea[name="answer_text"]').val().trim()) {
                        e.preventDefault();
                        alert('Vui lòng nhập câu trả lời!');
                        return;
                    }
                    $('#questionForm').submit();
                });
            } else {
                // Highlight label khi chọn radio
                $('input[name="answer_id"]').on('change', function() {
                    $('.radio-label').removeClass('active'); // Bỏ active các label khác
                    $(`label[for="${$(this).attr('id')}"]`).addClass(
                        'active'); // Thêm active label tương ứng

                    $('#nextBtn').addClass('active'); // Cho phép bấm nút tiếp
                });

                $('#nextBtn').on('click', function(e) {
                    const selected = $('input[name="answer_id"]:checked').val();
                    if (!selected) {
                        e.preventDefault();
                        alert('Vui lòng chọn một đáp án!');
                        return;
                    }
                    $('#questionForm').submit();
                });
            }

            $('.audio').on('click', function() {
                const text = $('.questionText').val();
                console.log('speak', text)
                speak(text);
            })
        });
    </script>
@endpush
