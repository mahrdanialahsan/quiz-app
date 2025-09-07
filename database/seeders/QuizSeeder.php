<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('user_answers')->delete();
        DB::table('question_options')->delete();
        DB::table('questions')->delete();

        // Question 1: PHP Basics
        $question1 = DB::table('questions')->insertGetId([
            'question' => 'What does PHP stand for?',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('question_options')->insert([
            [
                'question_id' => $question1,
                'option' => 'Personal Home Page',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question1,
                'option' => 'PHP: Hypertext Preprocessor',
                'is_correct' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question1,
                'option' => 'Private Home Page',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question1,
                'option' => 'Public Home Page',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Question 2: PHP Variables
        $question2 = DB::table('questions')->insertGetId([
            'question' => 'Which of the following is the correct way to declare a variable in PHP?',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('question_options')->insert([
            [
                'question_id' => $question2,
                'option' => '$variable_name',
                'is_correct' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question2,
                'option' => 'var variable_name',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question2,
                'option' => 'variable_name',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question2,
                'option' => 'let variable_name',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Question 3: PHP Arrays
        $question3 = DB::table('questions')->insertGetId([
            'question' => 'What is the correct way to create an array in PHP?',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('question_options')->insert([
            [
                'question_id' => $question3,
                'option' => 'array()',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question3,
                'option' => '[]',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question3,
                'option' => 'Both array() and []',
                'is_correct' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question3,
                'option' => 'new Array()',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Question 4: PHP Functions
        $question4 = DB::table('questions')->insertGetId([
            'question' => 'Which function is used to output text in PHP?',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('question_options')->insert([
            [
                'question_id' => $question4,
                'option' => 'echo',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question4,
                'option' => 'print',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question4,
                'option' => 'printf',
                'is_correct' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question_id' => $question4,
                'option' => 'All of the above',
                'is_correct' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Quiz data seeded successfully!');
    }
}
