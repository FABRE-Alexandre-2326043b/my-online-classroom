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
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
            
            $directory = $this->getParameter('courses_directory');
            $pdfFile = $form->get('pdfFile')->getData();
            $pdfName = $this->uploadFile($pdfFile, $slugger, $directory);
            
            if ($pdfName) {
                $course->setPdfFilename($pdfName);

                $projectDir = $this->getParameter('kernel.project_dir');
                $finalPath = $projectDir . '/public/uploads/courses/' . $pdfName;
                
                $parser = new Parser();
                $pdf = $parser->parseFile($finalPath); 
                $rawText = $pdf->getText();

                $cleanText = mb_convert_encoding($rawText, 'UTF-8', 'auto');
                $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cleanText);
                $cleanText = iconv('UTF-8', 'UTF-8//IGNORE', $cleanText);
                $cleanText = substr($cleanText, 0, 10000); 

                $summary = $aiService->generateCourseDescription($cleanText, $course->getTitle()); 
                $course->setDescription($summary);
            }

            $videoFile = $form->get('videoFile')->getData();
            $videoName = $this->uploadFile($videoFile, $slugger, $directory);

            if ($videoName) {
                $course->setVideoFilename($videoName);
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
        CourseAIService $aiService,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $directory = $this->getParameter('courses_directory');
            $pdfFile = $form->get('pdfFile')->getData();
            $pdfName = $this->uploadFile($pdfFile, $slugger, $directory);
            
            if ($pdfName) {
                $course->setPdfFilename($pdfName);

                $projectDir = $this->getParameter('kernel.project_dir');
                $finalPath = $projectDir . '/public/uploads/courses/' . $pdfName;
                
                $parser = new Parser();
                $pdf = $parser->parseFile($finalPath); 
                $rawText = $pdf->getText();

                $cleanText = mb_convert_encoding($rawText, 'UTF-8', 'auto');
                $cleanText = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cleanText);
                $cleanText = iconv('UTF-8', 'UTF-8//IGNORE', $cleanText);
                $cleanText = substr($cleanText, 0, 10000); 

                $summary = $aiService->generateCourseDescription($cleanText, $course->getTitle()); 
                $course->setDescription($summary);
            }

            $videoFile = $form->get('videoFile')->getData();
            $videoName = $this->uploadFile($videoFile, $slugger, $directory);

            if ($videoName) {
                $course->setVideoFilename($videoName);
            }

            $em->flush();

            $this->addFlash('success', 'Cours mis à jour avec succès !');
            return $this->redirectToRoute('admin_course_index');
        }

        return $this->render('admin/course/edit.html.twig', [
            'form' => $form,
            'course' => $course,
        ]);
    }

    /**
     * Gère l'upload d'un fichier (PDF ou Vidéo)
     * Retourne le nom du fichier ou null si aucun fichier n'est envoyé
     */
    private function uploadFile(?UploadedFile $file, SluggerInterface $slugger, string $targetDirectory): ?string
    {
        if (!$file) {
            return null;
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($targetDirectory, $newFilename);
            return $newFilename; 
        } catch (FileException $e) {
            $this->addFlash('danger', 'Erreur upload vidéo : ' . $e->getMessage());
        }
        return null;
    }
}