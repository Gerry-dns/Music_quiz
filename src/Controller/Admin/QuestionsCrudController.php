<?php

namespace App\Controller\Admin;

use App\Entity\Questions;
use App\Service\QuizGeneratorService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class QuestionsCrudController extends AbstractCrudController
{
    public function __construct(
        private QuizGeneratorService $quizService,
        private EntityManagerInterface $em,
        private AdminUrlGenerator $urlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return Questions::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $generateQuiz = Action::new('generateQuiz', 'Générer le quiz de cet artiste')
            ->linkToCrudAction('generateQuizForArtist')
            ->createAsGlobalAction(); 

        return $actions  // bouton "Voir"
            ->add(Action::DETAIL, $generateQuiz)
            ->add(Crud::PAGE_NEW, $generateQuiz);
    }

    public function generateQuizForArtist(AdminContext $context): RedirectResponse
    {
        /** @var Questions $question */
        $question = $context->getEntity()->getInstance();

        if (!$question || !$question->getArtist()) {
            $this->addFlash('danger', 'Aucun artiste associé à cette question.');
            return $this->redirect(
                $this->urlGenerator->setAction(Action::INDEX)->generateUrl()
            );
        }

        $artist = $question->getArtist();

        $this->quizService->generateQuestionsForArtist($artist);

        $this->addFlash('success', 'Quiz généré pour : ' . $artist->getName());

        return $this->redirect(
            $this->urlGenerator
                ->setAction(Action::DETAIL)
                ->setEntityId($question->getId())
                ->generateUrl()
        );
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('text', 'Question'),
            TextField::new('correctAnswer', 'Bonne réponse'),
            TextField::new('wrongAnswer1', 'Mauvaise réponse 1'),
            TextField::new('wrongAnswer2', 'Mauvaise réponse 2'),
            TextField::new('wrongAnswer3', 'Mauvaise réponse 3'),
            IntegerField::new('difficulty'),
            TextField::new('category'),
            IntegerField::new('playedCount')->hideOnForm(),
            IntegerField::new('correctCount')->hideOnForm(),
            AssociationField::new('artist'),
        ];
    }
}
