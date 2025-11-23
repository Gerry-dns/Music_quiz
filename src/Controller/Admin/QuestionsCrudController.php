<?php

namespace App\Controller\Admin;

use App\Entity\Questions;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class QuestionsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Questions::class;
    }
}