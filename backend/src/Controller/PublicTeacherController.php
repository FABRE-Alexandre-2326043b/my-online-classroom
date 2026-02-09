<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PublicTeacherController extends AbstractController
{
    #[Route('/teachers', name: 'app_teachers')]
    public function index(UserRepository $userRepository): Response
    {
        $teachers = $userRepository->findByRole('ROLE_TEACHER');

        return $this->render('teacher/index.html.twig', [
            'teachers' => $teachers,
        ]);
    }
}