<?php
namespace App\Controller\Admin;

use App\Entity\Course;
use App\Form\CourseType;
use App\Service\CourseAIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/courses')]
#[IsGranted('ROLE_TEACHER')]
class CourseController extends AbstractController
{
    #[Route('/', name: 'admin_course_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $courseRepository = $em->getRepository(Course::class);

        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $courses = $courseRepository->findAll();
        } else {
            $courses = $courseRepository->findBy(['teacher' => $user]);
        }
        
        return $this->render('admin/course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    #[Route('/new', name: 'admin_course_new')]
    public function new(
        Request $request, 
        EntityManagerInterface $em,
        CourseAIService $aiService,
        SluggerInterface $slugger
    ): Response {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $pdfFile = $form->get('pdfFile')->getData();

            if ($pdfFile) {
                $originalFilename = pathinfo($pdfFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$pdfFile->guessExtension();

                try {
                    $pdfFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/courses',
                        $newFilename
                    );
                    $course->setPdfFilename($newFilename);

                    if ($form->get('generate_description')->getData()) {
                        
                        try {
                        $parser = new Parser();
                        $pdf = $parser->parseFile(
                            $this->getParameter('kernel.project_dir') . '/public/uploads/courses/' . $newFilename
                        );
                        $text = $pdf->getText(); 

                        $rawText = $parser->parseFile($pdfFile->getPathname())->getText();

                        $cleanText = iconv('UTF-8', 'UTF-8//IGNORE', $rawText);
                        
                        if (false === $cleanText) {
                            $cleanText = mb_convert_encoding($rawText, 'UTF-8', 'UTF-8');
                        }
                        
                        $description = $aiService->generateCourseDescription($cleanText, $course->getTitle());
                        $course->setDescription($description);
                        } catch (\Exception $e) {
                        $this->addFlash('warning', 'Le cours est créé, mais l\'IA n\'a pas pu générer le résumé : ' . $e->getMessage());
                        }
                    }

                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload du fichier');
                }
            }

            $course->setTeacher($this->getUser());

            $em->persist($course);
            $em->flush();

            $this->addFlash('success', 'Cours créé avec succès !');
            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('admin/course/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_course_show')]
    public function show(Course $course): Response
    {
        return $this->render('admin/course/show.html.twig', [
            'course' => $course,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_course_edit')]
    public function edit(
        Course $course, 
        Request $request, 
        EntityManagerInterface $em,
        CourseAIService $aiService
    ): Response {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Cours mis à jour avec succès !');
            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('admin/course/edit.html.twig', [
            'form' => $form,
            'course' => $course,
        ]);
    }
}