@extends('layouts.app')

@section('title', 'Kết quả bài thi thử')

@section('content')
    <div class="container">
        <h3 class="result-title">Mock Test</h3>
        <h1 class="result-subtitle">KẾT QUẢ BÀI THI THỬ</h1>

        <div class="accordion mt-4" id="resultsAccordion">
            @foreach ($results as $index => $result)
                <div class="accordion-item mb-3">
                    <h2 class="accordion-header" id="heading{{ $index }}">
                        <button class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse{{ $index }}"
                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-controls="collapse{{ $index }}">
                            <div class="d-flex align-items-center w-100">
                                <img src="{{ asset($result['icon']) }}" style="width: 32px;" alt="{{ $result['title'] }}">
                                <div class="ms-3 flex-grow-1">
                                    <div class="fw-bold">{{ $result['title'] }}</div>
                                    @if ($result['slug'] !== 'n400')
                                        <div class="text-muted small">{{ $result['vietnamese_title'] }}</div>
                                    @endif
                                </div>
                                @if ($result['slug'] !== 'n400')
                                    <div class="d-flex align-items-center">
                                        <span
                                            class="badge {{ $result['is_passed'] ? 'bg-success' : 'bg-danger' }} result-badge">
                                            {{ $result['is_passed'] ? 'Đạt' : 'Chưa Đạt' }}
                                        </span>
                                        <span
                                            class="ms-2 fw-bold  mx-2">{{ $result['correct'] }}/{{ $result['total'] }}</span>
                                    </div>
                                @else
                                    {{-- TODO: Fix --}}
                                    <span class="ms-2 fw-bold  mx-2">{{ $result['total'] }}/{{ $result['total'] }}</span>
                                @endif

                            </div>
                        </button>
                    </h2>
                    <div id="collapse{{ $index }}"
                        class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                        aria-labelledby="heading{{ $index }}" data-bs-parent="#resultsAccordion">
                        <div class="accordion-body">
                            @if (!empty($result['details']))
                                @foreach ($result['details'] as $i => $detail)
                                    <div class="p-3 border rounded mb-3 bg-light">
                                        <div><strong>{{ $i + 1 }}:</strong> {{ $detail['question'] }}</div>

                                        @if ($result['slug'] == 'n400')
                                            <p>Câu trả lời của bạn: <strong>{{ $detail['user_answer'] }}</strong></p>
                                        @elseif ($detail['is_correct'])
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ asset('icons/mockTests/success.svg') }}" alt="Success">
                                                <p class="text-success m-0">{{ $detail['user_answer'] }}</p>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <img src="{{ asset('icons/mockTests/error.svg') }}" alt="Error">
                                                <p class="text-danger m-0">{{ $detail['user_answer'] }}</p>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ asset('icons/mockTests/success.svg') }}" alt="Success">
                                                <p class="text-success m-0">{{ $detail['correct_answer'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-muted">Không có câu hỏi nào trong phần thi này.</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('mock-test.list') }}" class="btn btn-primary px-5 py-2">
                Tiếp tục làm bài thi thử
            </a>
            <div class="mt-2">
                <a class="home-link text-decoration-none text-secondary" href="#">Về trang chủ</a>
            </div>
        </div>
    </div>
@endsection
