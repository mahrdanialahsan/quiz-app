<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question',
    ];

    /**
     * Get the options for the question.
     */
    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * Get the user answers for the question.
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
