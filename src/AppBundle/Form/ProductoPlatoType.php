<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductoPlatoType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('producto', null, [
                'required' => true,
            ])
            ->add('pesoNeto')
            ->add('pesoBruto', null, [
                'required' => true,
                //'invalid_message' => 'Este valor no es un numero correcto'
            ])
            ->add('unidadMedida', null, [
                'required' => true,
            ])
            //->add('alimento')
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\ProductoPlato',
        ));
    }
}
