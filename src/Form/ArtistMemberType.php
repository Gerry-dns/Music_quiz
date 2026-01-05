<?php
namespace App\Form;

use App\Entity\ArtistMember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // <-- vrai FormType pour relation
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ArtistMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
$builder
    ->add('member', EntityType::class, [
        'class' => \App\Entity\Member::class,
        'choice_label' => 'name',
        'label' => 'Membre'
    ])
    ->add('memberInstruments', CollectionType::class, [
        'entry_type' => \App\Form\ArtistMemberInstrumentType::class,
        'label' => 'Instruments joués',
        'allow_add' => true,
        'allow_delete' => true,
        'by_reference' => false, // <-- très important !
    ]);
    }
}
