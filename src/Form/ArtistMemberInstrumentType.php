<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ArtistMemberInstrumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('instrument', EntityType::class, [
                'class' => \App\Entity\Instrument::class,
                'choice_label' => 'name',
                'label' => 'Instrument'
            ])
            ->add('primary', CheckboxType::class, [
                'label' => 'Instrument principal',
                'required' => false
            ]);
    }
}
