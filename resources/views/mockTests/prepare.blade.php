@extends('layouts.app')

@section('title', $currentTest->title)

@section('content')
    <div class="header-inner">
        <div class="header">
            <a href="#"><img src="{{ asset('icons/mockTests/home.svg') }}" alt="Home" /></a>
            <h1 class="header-title">
                THI THỬ<br>
                <span class="header-subtitle">{{ $currentTest->title }}</span>

                @if ($currentTest->vietnamese_title)
                    <span class="header-subtitle-2">({{ $currentTest->vietnamese_title }})</span>
                @endif
            </h1>
        </div>
    </div>

    <main class="main-content">
        <div class="prepare-card">
            <img src="{{ asset('icons/mockTests/' . $currentTest->slug . '.svg') }}" alt="{{ $currentTest->title }}"
                class="prepare-icon" />

            <h2 class="font-md text-center">{{ $currentTest->title }}</h2>
            @if ($currentTest->vietnamese_title)
                <p class="text-center text-muted mb-2 font-sm">({{ $currentTest->vietnamese_title }})</p>
            @endif

            @if ($previousTest)
                <p class="text-center font-sm-2">
                    Bạn đã hoàn thành phần <strong>{{ $previousTest->title }}</strong>.<br>
                    Tiếp theo là phần <strong>{{ $currentTest->title }}</strong>.
                </p>
            @else
                <p class="text-center">
                    Bạn sắp bắt đầu phần <strong>{{ $currentTest->title }}</strong>.
                </p>
            @endif
        </div>
    </main>

    <div class="text-center test-footer">
        <a href="{{ route('start.mock-test', $currentTest->slug) }}" class="btn btn-normal">
            Tôi đã sẵn sàng
        </a>
    </div>
@endsection
