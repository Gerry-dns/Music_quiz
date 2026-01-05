<?php

namespace App\Controller;

use App\Entity\Questions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuizController extends AbstractController
{
    #[Route('/admin/quiz_test', name: 'admin_quiz_test')]
    public function index(EntityManagerInterface $em): Response
    {
        $questions = $em->getRepository(Questions::class)->findBy([], ['artist' => 'ASC', 'id' => 'ASC']);

        return $this->render('admin/quiz_test.html.twig', [
            'questions' => $questions,
        ]);
    }
}
