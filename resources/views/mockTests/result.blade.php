@extends('layouts.app')

@section('title', 'Kết quả bài thi thử')

@section('content')
    <div class="container">
        <h3 class="result-title">Mock Test</h3>
        <h1 class="result-subtitle">KẾT QUẢ BÀI THI THỬ</h1>

        <div class="result-list mt-4">
            @foreach ($results as $result)
                <div class="test-card d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset($result['icon']) }}" style="width: 32px;" alt="{{ $result['title'] }}">
                        <div class="ms-3">
                            <div class="test-title">{{ $result['title'] }}</div>
                            @if ($result['slug'] !== 'n400')
                                <div class="test-subtitle">({{ $result['vietnamese_title'] }})</div>
                            @endif
                        </div>
                    </div>

                    @if ($result['slug'] !== 'n400')
                        <div class="d-flex align-items-center">
                            <div class="badge {{ $result['is_passed'] ? 'bg-success' : 'bg-danger' }} result-badge">
                                {{ $result['is_passed'] ? 'Đạt' : 'Chưa Đạt' }}
                            </div>
                        </div>
                        <span class="ms-2">{{ $result['correct'] }}/{{ $result['total'] }}</span>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('mock-test.list') }}" class="btn btn-normal big">
                Tiếp tục làm bài thi thử
            </a>
            <div class="mt-2">
                <a class="home-link" href="#">Về trang chủ</a>
            </div>
        </div>
    </div>
@endsection
