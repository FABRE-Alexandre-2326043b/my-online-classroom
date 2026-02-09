<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Quiz;
use App\Repository\CourseRepository;
use App\Form\QuizEditType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Smalot\PdfParser\Parser;
use App\Service\CourseAIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin/quizzes')]
#[IsGranted('ROLE_TEACHER')]
class QuizController extends AbstractController
{
    #[Route('/', name: 'admin_quiz_index')]
    public function index(CourseRepository $courseRepository): Response
    {
        $courses = $courseRepository->findAll();

        return $this->render('admin/quiz/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/{id}/results', name: 'admin_quiz_results')]
    public function results(Quiz $quiz): Response
    {
        $results = $quiz->getQuizResults();

        $average = 0;
        if (count($results) > 0) {
            $totalScore = 0;
            foreach ($results as $result) {
                $totalScore += $result->getScore();
            }
            $average = $totalScore / count($results);
        }

        return $this->render('admin/quiz/results.html.twig', [
            'quiz' => $quiz,
            'results' => $results,
            'average' => $average
        ]);
    }

    #[Route('/{id}', name: 'admin_quiz_details')]
    public function show(Quiz $quiz): Response
    {
        return $this->render('admin/quiz/show.html.twig', [
            'quiz' => $quiz,
        ]);
    }

    #[Route('/{id}/generate', name: 'admin_quiz_generate')]
    public function generate(
        Request $request,   
        Course $course, 
        CourseAIService $aiService, 
        EntityManagerInterface $em
    ): Response {
        if (!$course->getPdfFilename()) {
            $this->addFlash('danger', 'Ce cours n\'a pas de PDF, impossible de générer un QCM.');
            return $this->redirectToRoute('admin_quiz_index');
        }

        $count = (int) $request->request->get('count', 5);
        $type = $request->request->get('type', 'qcm');

       try {
            $projectDir = $this->getParameter('kernel.project_dir');
            $filePath = $projectDir . '/public/uploads/courses/' . $course->getPdfFilename();
            
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $rawText = $pdf->getText();

            $cleanText = mb_convert_encoding($rawText, 'UTF-8', 'auto');
            
            $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cleanText);
            
            $cleanText = iconv('UTF-8', 'UTF-8//IGNORE', $cleanText);

            $cleanText = substr($cleanText, 0, 15000);

            $questions = $aiService->generateQuiz($cleanText, $count, $type); 

            $quiz = new Quiz();
            $titleType = ($type === 'true_false') ? 'Vrai/Faux' : 'QCM';
            $quiz->setTitle("$titleType : " . $course->getTitle());
            $quiz->setCourse($course);
            $quiz->setQuestions($questions);

            $em->persist($quiz);
            $em->flush();

            $this->addFlash('success', 'Quiz ' . ($titleType) . ' de '.  $count . ' questions généré avec succès par l\'IA');

        } catch (\Exception $e) {
            $jsonError = json_last_error_msg();
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage() . ' (JSON Error: ' . $jsonError . ')');
        }

        return $this->redirectToRoute('admin_quiz_index');
    }

    #[Route('/{id}/edit', name: 'admin_quiz_edit')]
    public function edit(Quiz $quiz, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(QuizEditType::class, $quiz);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'QCM modifié avec succès !');
            return $this->redirectToRoute('admin_quiz_index');
        }

        return $this->render('admin/quiz/edit.html.twig', [
            'quiz' => $quiz,
            'form' => $form,
        ]);
    }
}