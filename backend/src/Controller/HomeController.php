<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        if ($this->getUser()) {
            
            if ($this->isGranted('ROLE_TEACHER')) {
                return $this->redirectToRoute('admin_course_index');
            }
            
            return $this->redirectToRoute('student_dashboard');
        }

        return $this->redirectToRoute('app_login');
    }
}