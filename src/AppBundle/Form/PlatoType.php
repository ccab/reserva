<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlatoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('codigo')
            ->add('nombre')
            ->add('categoria')
            ->add('precio')
            ->add('norma')
            ->add('valorNutricProteina')
            ->add('valorNutricCarbohidrato')
            ->add('valorNutricGrasa')
            ->add('valorNutricEnergia')
            ->add('temperatura')
            ->add('tiempo')
            ->add('observaciones')
            ->add('preparacion')
            ->add('coccion')
            ->add('productosPlato', CollectionType::class, [
                'entry_type' => ProductoPlatoType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                //'label' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Guardar Cambios'
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Plato',
        ));
    }
}
