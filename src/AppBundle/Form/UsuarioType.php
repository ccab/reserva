<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UsuarioType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('usuario')
            ->add('noSolapin')
            ->add('nombre')
            ->add('apellido')
            ->add('segundoApellido')
            ->add('rol');

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            $form = $event->getForm();

            if (!$entity || $entity->getId() === null) {
                $form->add('clave', RepeatedType::class, [
                    'first_name' => 'clave',
                    'first_options' => ['label' => 'Clave'],
                    'second_name' => 'confirm',
                    'second_options' => ['label' => 'Confirmar clave'],
                ]);
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Usuario'
        ));
    }
}
