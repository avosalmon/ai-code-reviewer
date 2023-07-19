<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;

class CodeReview extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'code-review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Review code taking into account the coding guidelines and best practices.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $guideline = Storage::get('coding-guideline.md');
        $codeToReview = Storage::get('DocumentController.php');

        $stream = OpenAI::chat()->createStreamed([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an experienced senior software developer who is familiar with Laravel and PHP. You will act as a reviewer to review this piece of code from a Github Pull Request.'],
                ['role' => 'user', 'content' => "Here is the list of Laravel and PHP conventions I want you to memorize and refer to when reviewing a PHP code snippet. The conventions are as follows: \n\n{$guideline}"],
                ['role' => 'assistant', 'content' => 'I have read through the list of Laravel and PHP conventions you have provided and will actively refer to this conventions when reviewing your PHP code snippets. Please provide me with the PHP code snippets to be reviewed.'],
                ['role' => 'user', 'content' => $codeToReview],
            ],
            // 'functions' => [
                //
            // ],
            // 'function_call' => [
            //     'name' => 'review_code',
            // ],
            'temperature' => 0,
            // 'max_tokens' => xxx,
        ]);

        foreach ($stream as $response) {
            $delta = $response->choices[0]->delta->content ?? '';
            $this->getOutput()->write($delta);
        }

        $this->newLine();
    }
}
