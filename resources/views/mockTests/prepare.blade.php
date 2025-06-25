@extends('layouts.app')

@section('title', $currentTest->title)

@section('content')
    <div class="container">
        <div class="header">
            <a href="#">
                <img src="{{ asset('icons/mockTests/home.svg') }}" alt="Home" />
            </a>
            <h1 class="header-title">
                THI THỬ<br>
                {{ $currentTest->title }}
                @if ($currentTest->vietnamese_title)
                    ({{ $currentTest->vietnamese_title }})
                @endif
            </h1>
        </div>

        <main class="main-content">
            <div class="prepare-card">
                <img src="{{ asset('icons/mockTests/' . $currentTest->slug . '.svg') }}" alt="{{ $currentTest->title }}"
                    class="prepare-icon" />

                <h2 class="test-title text-center">{{ $currentTest->title }}</h2>
                @if ($currentTest->vietnamese_title)
                    <p class="test-subtitle text-center text-muted mb-2">({{ $currentTest->vietnamese_title }})</p>
                @endif

                @if ($previousTest)
                    <p class="text-center">
                        Bạn đã hoàn thành phần <strong>{{ $previousTest->title }}</strong>.<br>
                        Tiếp theo là phần <strong>{{ $currentTest->title }}</strong>.
                    </p>
                @else
                    <p class="text-center">
                        Bạn sắp bắt đầu phần <strong>{{ $currentTest->title }}</strong>.
                    </p>
                @endif

                <div class="text-center">
                    <a href="{{ route('start.mock-test', $currentTest->slug) }}" class="btn btn-normal">
                        Tôi đã sẵn sàng
                    </a>
                </div>
            </div>
        </main>
    </div>
@endsection
