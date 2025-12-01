<?php

namespace App\Controller;

use App\Entity\Questions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class QuizController extends AbstractController
{
    #[Route('/quiz', name: 'app_quiz')]
    public function index(EntityManagerInterface $em): Response
    {
        $questions = $em->getRepository(Questions::class)->findAll();
        return $this->render('quiz/index.html.twig', [
            'questions' => $questions,
        ]);
    }
}
