<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswerQuestion extends Model
{
    use HasFactory;

    protected $table = 'user_answer_question';

    protected $fillable = [
        'user_id',
        'attempt_id',
        'question_id',
        'answer_id',
        'answer_text',
        'additional_answer',
        'is_correct',
        'answered_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class);
    }
}
