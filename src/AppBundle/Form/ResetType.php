<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResetType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['is_admin'] == false) {
            $builder
                ->add('claveActual', PasswordType::class, [
                    'mapped' => false,
                ]);
        }

        $builder
            ->add('clave', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Clave Nueva'],
                'second_options' => ['label' => 'Repetir Clave'],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Usuario',
            'is_admin' => false,
        ));
    }
}
