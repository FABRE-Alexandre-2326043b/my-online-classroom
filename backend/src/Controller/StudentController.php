<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Entity\QuizResult;
use App\Repository\CourseRepository;
use App\Repository\QuizResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/student')]
#[IsGranted('ROLE_USER')]
class StudentController extends AbstractController
{
    #[Route('/', name: 'student_dashboard')]
    public function index(CourseRepository $courseRepository): Response
    {
        return $this->render('student/index.html.twig', [
            'courses' => $courseRepository->findAll(),
        ]);
    }

    #[Route('/course/{id}', name: 'student_course_show')]
    public function show(Course $course): Response
    {
        return $this->render('student/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/quiz/{id}', name: 'student_quiz_take', methods: ['GET', 'POST'])]
    public function quiz(Quiz $quiz, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $score = 0;
            $questions = $quiz->getQuestions();
            $totalQuestions = count($questions);
            $userAnswers = $request->request->all('answers');

            foreach ($questions as $index => $question) {
                if (isset($userAnswers[$index]) && (int)$userAnswers[$index] === $question['correct_index']) {
                    $score++;
                }
            }

            $finalScore = $totalQuestions > 0 ? round(($score / $totalQuestions) * 20) : 0;

            $result = new QuizResult();
            $result->setStudent($this->getUser());
            $result->setQuiz($quiz);
            $result->setScore($finalScore);
            $result->setCompletedAt(new \DateTimeImmutable());

            $em->persist($result);
            $em->flush();

            $this->addFlash('success', "Quiz terminé ! Vous avez eu $finalScore/20");
            return $this->redirectToRoute('student_results');
        }

        return $this->render('student/quiz.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/results', name: 'student_results')]
    public function results(QuizResultRepository $resultRepository): Response
    {
        return $this->render('student/results.html.twig', [
            'results' => $resultRepository->findBy(
                ['student' => $this->getUser()], 
                ['completedAt' => 'DESC']
            )
        ]);
    }
}