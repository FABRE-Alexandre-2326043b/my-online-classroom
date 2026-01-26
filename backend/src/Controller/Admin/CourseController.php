<?php
namespace App\Controller\Admin;

use App\Entity\Course;
use App\Form\CourseType;
use App\Service\CourseAIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/courses')]
#[IsGranted('ROLE_TEACHER')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'admin_course_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $courses = $em->getRepository(Course::class)->findAll();
        
        return $this->render('admin/course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/new', name: 'admin_course_new')]
    public function new(
        Request $request, 
        EntityManagerInterface $em,
        CourseAIService $aiService
    ): Response {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Génération automatique du résumé par IA
            if ($form->get('generate_summary')->getData()) {
                $summary = $aiService->generateCourseSummary($course);
                $course->setSummary($summary);
            }

            $em->persist($course);
            $em->flush();

            $this->addFlash('success', 'Cours créé avec succès !');
            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('admin/course/new.html.twig', [
            'form' => $form,
        ]);
    }
}