<?php
namespace App\Service;

use Symfony\Component\AI\ChatInterface;
use App\Entity\Course;
use App\Entity\Lesson;

class CourseAIService
{
    public function __construct(
        private ChatInterface $chat
    ) {}

    public function generateCourseSummary(Course $course): string
    {
        $prompt = sprintf(
            "Génère un résumé engageant pour ce cours :\n\n" .
            "Titre: %s\nDescription: %s",
            $course->getTitle(),
            $course->getDescription()
        );

        $response = $this->chat->prompt($prompt);
        return $response->getContent();
    }

    public function generateQuiz(Lesson $lesson, int $questions = 5): array
    {
        $prompt = sprintf(
            "Crée %d questions QCM à partir de cette leçon :\n\n%s\n\n" .
            "Format JSON: [{\"question\": \"...\", \"options\": [...], " .
            "\"correct_answer\": 0}]",
            $questions,
            $lesson->getContent()
        );

        $response = $this->chat->prompt($prompt);
        return json_decode($response->getContent(), true);
    }

    public function answerQuestion(string $question, Course $course): string
    {
        $context = implode("\n", 
            $course->getLessons()->map(fn($l) => $l->getContent())->toArray()
        );

        $prompt = "Contexte du cours: $context\n\nQuestion: $question";
        $response = $this->chat->prompt($prompt);
        
        return $response->getContent();
    }
}