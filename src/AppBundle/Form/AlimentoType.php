<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlimentoType extends AbstractType
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
            ->add('descripcion')
            ->add('categoria')
            ->add('precio')
            ->add('unidadMedida')
            ->add('cantidad')
            ->add('productoAlimentos', 'collection', [
                'type' => new ProductoAlimentoType(),
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Alimento',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_alimento';
    }
}
