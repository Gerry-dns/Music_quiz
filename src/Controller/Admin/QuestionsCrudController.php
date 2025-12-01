<?php

namespace App\Controller\Admin;

use App\Entity\Questions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class QuestionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Questions::class;
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