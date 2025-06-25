<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\TestType;
use App\Models\UserAnswerQuestion;
use Illuminate\Http\Request;

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

        $page = (int) $request->query('page', 1);
        $question = $testType->questions()->with('answers')->skip($page - 1)->take(1)->first();

        if ($slug === 'civics') {
            $total = 10;
            return view('mockTests.start-civic', compact('testType', 'question', 'page', 'total'));
        }

        if ($slug === 'reading') {
            $total = 1;
            return view('mockTests.start-reading', compact('testType', 'question', 'page', 'total'));
        }

        if ($slug === 'writing') {
            $total = 1;
            return view('mockTests.start-writing', compact('testType', 'question', 'page', 'total'));
        }

        if ($slug === 'n400') {
            $total = 5;
            return view('mockTests.start-n400', compact('testType', 'question', 'page', 'total'));
        }
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

        $question = Question::with('answers')->findOrFail($questionId);

        $isCorrect = false;

        if ($question->type === 'text' && $answerText) {
            $correctAnswer = $question->answers->firstWhere('is_correct', true);
            if ($correctAnswer) {
                $isCorrect = strtolower(trim($correctAnswer->answer_text)) === strtolower(trim($answerText));
            }
        }

        if ($question->type === 'multiple_choice' && $answerId) {
            $isCorrect = Answer::where('id', $answerId)
                ->where('is_correct', true)
                ->exists();
        }

        UserAnswerQuestion::updateOrCreate(
            [
                'user_id' => null,
                'question_id' => $questionId,
            ],
            [
                'answer_id' => $answerId,
                'answer_text' => $answerText,
                'is_correct' => $isCorrect,
                'answered_at' => now(),
            ]
        );

        $testType = TestType::where('slug', $slug)->firstOrFail();

        $currentPage = (int) $request->query('page', 1);
        $total = $testType->questions()->count();

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

    public function showResult()
    {
        $testTypes = TestType::orderBy('id')->get();

        $results = [];

        foreach ($testTypes as $testType) {
            $questions = $testType->questions()->with('answers')->get();
            $questionIds = $questions->pluck('id');

            $userAnswers = UserAnswerQuestion::whereIn('question_id', $questionIds)->get()->keyBy('question_id');

            $totalQuestions = $questions->count();
            $correctAnswers = $userAnswers->where('is_correct', true)->count();

            // Rule để đạt từng phần thi
            $isPassed = match ($testType->slug) {
                'civics' => $correctAnswers >= 6,
                'reading', 'writing-test' => $correctAnswers >= 1,
                default => null,
            };

            // Chi tiết từng câu hỏi
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

        return view('mockTests.result', compact('results'));
    }
}
