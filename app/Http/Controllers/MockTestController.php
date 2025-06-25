<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\TestType;
use App\Models\UserAnswerQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockTestController extends Controller
{
    public function show()
    {
        // $mockTest = [
        //     [
        //         'title' => 'Civics Test',
        //         'vietnamese_title' => 'Bài thi dân sự',
        //         'correct' => 6,
        //         'total' => 10,
        //         'note' => 'Đúng ít nhất 6/10 câu',
        //         'slug' => 'civics',
        //     ],
        //     [
        //         'title' => 'Reading Test',
        //         'vietnamese_title' => 'Bài thi đọc',
        //         'correct' => 1,
        //         'total' => 3,
        //         'note' => 'Đọc đúng 1 câu (có 3 cơ hội làm bài)',
        //         'slug' => 'reading',
        //     ],
        //     [
        //         'title' => 'Writing Test',
        //         'vietnamese_title' => 'Bài thi viết',
        //         'correct' => 1,
        //         'total' => 3,
        //         'note' => 'Viết đúng 1 câu (có 3 cơ hội làm bài)',
        //         'slug' => 'writing',
        //     ],
        //     [
        //         'title' => 'N-400',
        //         'vietnamese_title' => '',
        //         'note' => 'Câu trả lời dựa trên các thông tin bạn đã điền trên Form N-400',
        //         'slug' => 'n400',
        //     ],
        // ];

        $mockTest = TestType::all();
        return view('mockTests.index', compact('mockTest'));
    }

    public function start(Request $request, $slug)
    {
        $testType = TestType::where('slug', $slug)->firstOrFail();

        if (!$request->session()->has('mock_test_attempt_id')) {
            $attemptId = (string) Str::uuid();
            $request->session()->put('mock_test_attempt_id', $attemptId);
        } else {
            $attemptId = $request->session()->get('mock_test_attempt_id');
        }

        $page = (int) $request->query('page', 1);
        $question = $testType->questions()->with('answers')->skip($page - 1)->take(1)->first();

        $total = match ($slug) {
            'civics' => 10,
            'reading', 'writing' => 1,
            'n400' => 5,
            default => $testType->questions()->count(),
        };

        $view = match ($slug) {
            'civics' => 'mockTests.start-civic',
            'reading' => 'mockTests.start-reading',
            'writing' => 'mockTests.start-writing',
            'n400' => 'mockTests.start-n400',
        };

        return view($view, compact('testType', 'question', 'page', 'total', 'attemptId'));
    }

    public function submitAnswer(Request $request, $slug)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer_id' => 'nullable|exists:answers,id',
            'answer_text' => 'nullable|string',
        ]);

        $questionId = $request->question_id;
        $answerId = $request->answer_id;
        $answerText = $request->answer_text;
        $attemptId = session()->get('mock_test_attempt_id');

        if (!$attemptId) {
            return redirect()->route('start.mock-test', $slug)->with('error', 'Bài thi chưa được khởi tạo.');
        }

        $question = Question::with('answers')->findOrFail($questionId);
        $isCorrect = false;

        if ($question->type === 'text' && $answerText) {
            $correctAnswer = $question->answers->firstWhere('is_correct', true);
            $isCorrect = $correctAnswer &&
                strtolower(trim($correctAnswer->answer_text)) === strtolower(trim($answerText));
        }

        if ($question->type === 'multiple_choice' && $answerId) {
            $isCorrect = Answer::where('id', $answerId)->where('is_correct', true)->exists();
        }

        $testType = TestType::where('slug', $slug)->firstOrFail();
        $currentPage = (int) $request->query('page', 1);
        $total = $testType->questions()->count();
        $maxAttempts = $testType->max_attempts ?? 1;

        // Check nếu đã có câu trả lời trước đó thì update
        $userAnswer = UserAnswerQuestion::where('attempt_id', $attemptId)
            ->where('question_id', $questionId)
            ->first();

        if ($userAnswer) {
            $userAnswer->update([
                'answer_id' => $answerId,
                'answer_text' => $answerText,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]);
        } else {
            UserAnswerQuestion::create([
                'attempt_id' => $attemptId,
                'user_id' => null,
                'question_id' => $questionId,
                'answer_id' => $answerId,
                'answer_text' => $answerText,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]);
        }

        // Nếu là writing và sai thì xử lý retry
        if ($slug === 'writing' && !$isCorrect) {
            $attemptCount = session()->get("writing_retry_{$questionId}", 1);

            if ($attemptCount >= $maxAttempts) {
                session()->forget("writing_retry_{$questionId}");
                return redirect()->route('mock-test.prepare', ['n400']);
            }

            session()->put("writing_retry_{$questionId}", $attemptCount + 1);
            $remaining = $maxAttempts - $attemptCount;

            return redirect()->route('start.mock-test', [$slug, 'page' => $currentPage])
                ->with('error', "Câu trả lời chưa đúng. Bạn còn {$remaining} lượt thử lại.");
        }

        // Chuyển trang tiếp theo nếu không phải writing hoặc đúng
        if ($currentPage >= $total) {
            $nextTest = TestType::where('id', '>', $testType->id)->orderBy('id')->first();

            return $nextTest
                ? redirect()->route('mock-test.prepare', $nextTest->slug)
                : redirect()->route('mock-test.result');
        }

        return redirect()->route('start.mock-test', [$slug, 'page' => $currentPage + 1]);
    }

    public function prepare($slug)
    {
        $currentTest = TestType::where('slug', $slug)->firstOrFail();

        $previousTest = TestType::where('id', '<', $currentTest->id)
            ->orderBy('id', 'desc')
            ->first();

        return view('mockTests.prepare', [
            'currentTest' => $currentTest,
            'previousTest' => $previousTest,
        ]);
    }

    public function showResult(Request $request)
    {
        $testTypes = TestType::orderBy('id')->get();
        $results = [];

        foreach ($testTypes as $testType) {
            $questions = $testType->questions()->with('answers')->get();
            $questionIds = $questions->pluck('id');

            $attemptId = $request->session()->get("mock_test_attempt_id");

            if (!$attemptId) {
                // Nếu không có attempt, bỏ qua test này
                continue;
            }

            $userAnswers = UserAnswerQuestion::where('attempt_id', $attemptId)
                ->whereIn('question_id', $questionIds)
                ->with('answer') // để lấy dữ liệu answer_text nếu chọn lựa
                ->get()
                ->keyBy('question_id');

            $totalQuestions = $questions->count();
            $correctAnswers = $userAnswers->where('is_correct', true)->count();

            $isPassed = match ($testType->slug) {
                'civics' => $correctAnswers >= 6,
                'reading', 'writing' => $correctAnswers >= 1,
                default => null,
            };

            $details = [];

            foreach ($questions as $question) {
                $userAnswer = $userAnswers->get($question->id);
                $correctAnswer = $question->answers->firstWhere('is_correct', true);

                $details[] = [
                    'question' => $question->question_text,
                    'type' => $question->type,
                    'user_answer' => $userAnswer?->answer_text ?? $userAnswer?->answer?->answer_text,
                    'correct_answer' => $correctAnswer?->answer_text,
                    'is_correct' => $userAnswer?->is_correct,
                ];
            }

            $results[] = [
                'title' => $testType->title,
                'vietnamese_title' => $testType->vietnamese_title,
                'slug' => $testType->slug,
                'icon' => "icons/mockTests/{$testType->slug}.svg",
                'correct' => $correctAnswers,
                'total' => $totalQuestions,
                'is_passed' => $isPassed,
                'is_complete' => $totalQuestions > 0,
                'details' => $details,
            ];
        }

        $request->session()->forget('mock_test_attempt_id');
        return view('mockTests.result', compact('results'));
    }
}
