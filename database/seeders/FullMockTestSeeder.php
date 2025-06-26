<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use App\Models\TestType;
use Illuminate\Database\Seeder;

class FullMockTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // CIVICS TEST (multiple_choice)
        $civics = TestType::create([
            'title' => 'Civics Test',
            'vietnamese_title' => 'Bài thi dân sự',
            'slug' => 'civics',
            'note' => 'Đúng ít nhất 6/10 câu',
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $q = Question::create([
                'test_type_id' => $civics->id,
                'question_text' => "Civics question $i?",
                'vietnamese_question_text' => "Câu hỏi Civics số $i?",
                'type' => 'multiple_choice',
            ]);
            Answer::insert([
                ['question_id' => $q->id, 'answer_text' => "Correct answer $i", 'vietnamese_answer_text' => "Câu trả lời đúng $i", 'is_correct' => true],
                ['question_id' => $q->id, 'answer_text' => "Wrong answer $i", 'vietnamese_answer_text' => "Câu trả lời sai $i", 'is_correct' => false],
                ['question_id' => $q->id, 'answer_text' => "Wrong answer $i", 'vietnamese_answer_text' => "Câu trả lời sai $i", 'is_correct' => false],
                ['question_id' => $q->id, 'answer_text' => "Wrong answer $i", 'vietnamese_answer_text' => "Câu trả lời sai $i", 'is_correct' => false],
            ]);
        }

        // READING TEST (text)
        $reading = TestType::create([
            'title' => 'Reading Test',
            'vietnamese_title' => 'Bài thi đọc',
            'slug' => 'reading',
            'note' => 'Đọc đúng 1 câu (có 3 cơ hội làm bài)',
            'max_attempts' => 3,
        ]);

        $q3 = Question::create([
            'test_type_id' => $reading->id,
            'question_text' => 'Read this sentence: "Citizens can vote"',
            'vietnamese_question_text' => 'Đọc câu sau: "Citizens can vote"',
            'type' => 'text',
        ]);
        Answer::create([
            'question_id' => $q3->id,
            'answer_text' => 'Citizens can vote',
            'pronunciation_suggest' => 'Ci-ti-gien can vốt',
            'is_correct' => true,
        ]);

        // WRITING TEST (text)
        $writing = TestType::create([
            'title' => 'Writing Test',
            'vietnamese_title' => 'Bài thi viết',
            'slug' => 'writing',
            'note' => 'Viết đúng 1 câu (có 3 cơ hội làm bài)',
            'max_attempts' => 3,
        ]);

        $q4 = Question::create([
            'test_type_id' => $writing->id,
            'question_text' => 'Write this sentence: "We pay taxes"',
            'vietnamese_question_text' => 'Viết câu sau: "We pay taxes"',
            'type' => 'text',
        ]);
        Answer::create([
            'question_id' => $q4->id,
            'answer_text' => 'We pay taxes',
            'pronunciation_suggest' => 'Quy bay tát(s)',
            'is_correct' => true,
        ]);

        // N-400 (text)
        $n400 = TestType::create([
            'title' => 'N-400',
            'vietnamese_title' => '',
            'slug' => 'n400',
            'note' => 'Câu trả lời dựa trên các thông tin bạn đã điền trên Form N-400',
        ]);

        $q5 = Question::create([
            'test_type_id' => $n400->id,
            'question_text' => 'What is your full name?',
            'type' => 'text',
        ]);
        Answer::create([
            'question_id' => $q5->id,
            'answer_text' => '',
            'is_correct' => true,
        ]);


        $q6 = Question::create([
            'test_type_id' => $n400->id,
            'question_text' => 'Are you sure about that?',
            'type' => 'multiple_choice',
        ]);
        Answer::insert([
            ['question_id' => $q6->id, 'answer_text' => "No", 'is_correct' => true, 'has_additional_answer' => false],
            ['question_id' => $q6->id, 'answer_text' => "Yes", 'is_correct' => false, 'has_additional_answer' => true],
        ]);
    }
}
