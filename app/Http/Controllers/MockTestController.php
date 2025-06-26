<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\TestType;
use App\Models\UserAnswerQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isEmpty;

class MockTestController extends Controller
{
    public function show()
    {
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

        //  Đếm số câu đúng
        if ($slug === 'civics') {
            $correctAnswersCount = UserAnswerQuestion::where('attempt_id', $attemptId)
                ->where('is_correct', true)
                ->whereHas('question.testType', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                })
                ->count();

            if ($correctAnswersCount >= 6) {
                return redirect()->route('mock-test.prepare', 'reading');
            }
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
        $additionalField = $request->additional_field;
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
                'additional_answer' => empty($additionalField) ? null : $additionalField,
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
                'additional_answer' => empty($additionalField) ? null : $additionalField,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]);
        }

        // Nếu là reading và sai thì xử lý retry
        if ($slug === 'reading') {
            if ($redirect = $this->handleRetry('reading', $questionId, $currentPage, $maxAttempts, $isCorrect, 'writing')) {
                return $redirect;
            }
        }

        // Nếu là writing và sai thì xử lý retry
        if ($slug === 'writing') {
            if ($redirect = $this->handleRetry('writing', $questionId, $currentPage, $maxAttempts, $isCorrect, 'n400')) {
                return $redirect;
            }
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

    // private function handleRetry($slug, $questionId, $currentPage, $maxAttempts, $isCorrect, $nextSlug)
    // {
    //     if ($isCorrect) {
    //         // Nếu đúng thì không cần retry
    //         session()->forget("{$slug}_retry_{$questionId}");
    //         return null;
    //     }

    //     $retryKey = "{$slug}_retry_{$questionId}";
    //     $attemptCount = session()->get($retryKey, 1);

    //     if ($attemptCount >= $maxAttempts) {
    //         session()->forget($retryKey);
    //         return redirect()->route('mock-test.prepare', [$nextSlug]);
    //     }

    //     session()->put($retryKey, $attemptCount + 1);
    //     $remaining = $maxAttempts - $attemptCount;

    //     return redirect()
    //         ->route('start.mock-test', [$slug, 'page' => $currentPage])
    //         ->with('error', "Câu trả lời chưa đúng. Bạn còn {$remaining} lượt thử lại.");
    // }

    private function handleRetry($slug, $questionId, $currentPage, $maxAttempts, $isCorrect, $nextSlug)
    {
        $retryKey = "{$slug}_retry_{$questionId}";
        $resultKey = "{$slug}_retry_result_{$questionId}";

        if ($isCorrect) {
            $attemptCount = session()->get($retryKey, 1);

            // Ghi lại kết quả retry để showResult dùng
            session()->put($resultKey, $attemptCount);

            // Xóa retry để không ảnh hưởng đến logic chuyển tiếp
            session()->forget($retryKey);
            return null;
        }

        $attemptCount = session()->get($retryKey, 1);

        if ($attemptCount >= $maxAttempts) {
            // Ghi lại kết quả là maxAttempts vì đã sai hết
            session()->put($resultKey, $maxAttempts);

            // Clear retry chính
            session()->forget($retryKey);
            return redirect()->route('mock-test.prepare', [$nextSlug]);
        }

        session()->put($retryKey, $attemptCount + 1);
        $remaining = $maxAttempts - $attemptCount;

        return redirect()
            ->route('start.mock-test', [$slug, 'page' => $currentPage])
            ->with('error', "Câu trả lời chưa đúng. Bạn còn {$remaining} lượt thử lại.");
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


            $correctAnswers = $userAnswers->where('is_correct', true)->count();

            $isPassed = match ($testType->slug) {
                'civics' => $correctAnswers >= 6,
                'reading', 'writing' => $correctAnswers >= 1,
                default => null,
            };

            $details = [];

            foreach ($questions as $question) {
                $userAnswer = $userAnswers->get($question->id);

                //  Nếu là civics và chưa trả lời => bỏ qua
                if ($testType->slug === 'civics' && !$userAnswer) {
                    continue;
                }

                $correctAnswer = $question->answers->firstWhere('is_correct', true);

                $details[] = [
                    'question' => $question->question_text,
                    'vietnamese_question' => $question->vietnamese_question_text,
                    'type' => $question->type,
                    'user_answer' => $userAnswer?->answer_text ?? $userAnswer?->answer?->answer_text,
                    'correct_answer' => $correctAnswer?->answer_text,
                    'vietnamese_correct_answer' => $correctAnswer?->vietnamese_answer_text,
                    'pronunciation_suggest_answer' => $correctAnswer?->pronunciation_suggest,
                    'is_correct' => $userAnswer?->is_correct,
                ];
            }

            if ($testType->slug === 'civics') {
                $totalQuestions = UserAnswerQuestion::where('attempt_id', $attemptId)
                    ->whereIn('question_id', $questionIds)
                    ->where(function ($query) {
                        $query->whereNotNull('answer_text')
                            ->orWhereNotNull('answer_id');
                    })
                    ->count();
            } elseif (in_array($testType->slug, ['reading', 'writing'])) {
                $totalQuestions = 3;

                foreach ($questions as $question) {
                    $userAnswer = $userAnswers->get($question->id);

                    if ($userAnswer && $userAnswer->is_correct) {
                        $resultKey = "{$testType->slug}_retry_result_{$question->id}";
                        $retryCount = session()->get($resultKey);

                        $totalQuestions = $retryCount ?? 1;
                        break;
                    }
                }
            } else {
                $totalQuestions = $questions->count();
            }
            // $totalQuestions = $questions->count();

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
