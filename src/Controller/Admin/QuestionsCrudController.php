<?php

namespace App\Controller\Admin;

use App\Entity\Questions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class QuestionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Questions::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('artist', 'Artiste')->setRequired(true),
            TextField::new('text', 'Question'),
            TextField::new('correctAnswer', 'Réponse correcte'),
            TextField::new('wrongAnswer1', 'Réponse fausse 1'),
            TextField::new('wrongAnswer2', 'Réponse fausse 2'),
            TextField::new('wrongAnswer3', 'Réponse fausse 3'),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions;
    }
}
    