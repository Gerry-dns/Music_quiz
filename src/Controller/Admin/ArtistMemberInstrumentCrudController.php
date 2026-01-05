<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use App\Entity\ArtistMemberInstrument;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class ArtistMemberInstrumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ArtistMemberInstrument::class;
    }

    

    public function configureFields(string $pageName): iterable
    {
         // Affiche le nom du membre au lieu de "ArtistMember #61"
        yield AssociationField::new('artistMember', "Membre de l'artiste")
            ->formatValue(function ($value, $entity) {
                return $entity->getArtistMember()->getMember()->getName();
            });

        // Affiche le nom de l'instrument au lieu de "Instrument #6"
        yield AssociationField::new('instrument', 'Instruments joués')
            ->formatValue(function ($value, $entity) {
                return $entity->getInstrument()->getName();
            });

    }
}
