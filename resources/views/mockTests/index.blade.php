@extends('layouts.app')

@section('title', 'Home Page')

@section('content')
    <div class="container">

        <!-- Header -->
        <div class="header">
            <img src="{{ asset('icons/mockTests/home.svg') }}" alt="Home" />
            <h1 class="header-title" style="margin-bottom: 0px;">
                THI THỬ
            </h1>
        </div>


        <main class="main-content">
            <h2 class="section-title">Yêu cầu bài thi quốc tịch</h2>

            @foreach ($mockTest as $test)
                <div class="test-card">
                    <img src="{{ asset('icons/mockTests/' . $test['slug'] . '.svg') }}" alt="{{ $test['title'] }}"
                        class="test-icon" />
                    <div class="test-content">
                        <div class="flex gap-sm">
                            <h3 class="test-title">{{ $test['title'] }}</h3>
                            @if (!empty($test['vietnamese_title']))
                                <p class="test-subtitle">({{ $test['vietnamese_title'] }})</p>
                            @endif
                        </div>
                        <p class="test-progress">
                            {{ $test['note'] }}
                        </p>
                    </div>
                </div>
            @endforeach

            <!-- Warning Section -->
            <div class="warning-section">
                <div class="warning-content">
                    <img src="{{ asset('icons/mockTests/warning.svg') }}" alt="Warning" class="warning-icon" />
                    <p class="warning-text">
                        Phần thi thử được thiết kế nhằm mô phỏng gần nhất với kỳ thi thật nên sẽ không hỗ trợ tiếng Việt.
                    </p>
                </div>
            </div>

            <!-- Start Button -->
            <a class="start-button" href="{{ route('start.mock-test', 'civics') }}">Bắt đầu thi</a>
        </main>
    </div>
@endsection
