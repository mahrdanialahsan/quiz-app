<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option',
        'is_correct',
    ];

    /**
     * Get the question that owns the option.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the user answers for the option.
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'option_id');
    }
}
