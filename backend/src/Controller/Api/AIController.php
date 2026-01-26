<?php
namespace App\Controller\Api;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Service\CourseAIService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/ai')]
class AIController extends AbstractController
{
    public function __construct(
        private CourseAIService $aiService
    ) {}

    #[Route('/course/{id}/summary', methods: ['POST'])]
    #[IsGranted('ROLE_TEACHER')]
    public function generateSummary(Course $course): JsonResponse
    {
        try {
            $summary = $this->aiService->generateCourseSummary($course);
            
            return $this->json([
                'summary' => $summary,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la génération',
                'success' => false
            ], 500);
        }
    }

    #[Route('/lesson/{id}/quiz', methods: ['POST'])]
    #[IsGranted('ROLE_TEACHER')]
    public function generateQuiz(Lesson $lesson, Request $request): JsonResponse
    {
        $numberOfQuestions = $request->request->getInt('questions', 5);
        
        $quiz = $this->aiService->generateQuiz($lesson, $numberOfQuestions);
        
        return $this->json([
            'quiz' => $quiz,
            'success' => true
        ]);
    }

    #[Route('/course/{id}/ask', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function askQuestion(Course $course, Request $request): JsonResponse
    {
        $question = $request->request->get('question');
        
        if (!$question) {
            return $this->json(['error' => 'Question requise'], 400);
        }

        $answer = $this->aiService->answerQuestion($question, $course);
        
        return $this->json([
            'answer' => $answer,
            'success' => true
        ]);
    }
}