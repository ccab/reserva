<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('aprobado')
            ->add('fecha')
            ->add('tipoMenu')
            ->add('menuPlatos', 'collection', [
                'type' => new MenuPlatoType(),
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
            'data_class' => 'AppBundle\Entity\Menu',
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'appbundle_menu';
    }
}
