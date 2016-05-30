<?php

namespace AppBundle\Form;

use AppBundle\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RecepcionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recepciones', CollectionType::class, [
                'entry_type' => RecepcionarProductoType::class,
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
                'label' => false,
            ])
            ->add('fecha', DateType::class, [
                'data' => new \DateTime('today'),
            ])
            ->add('aceptar',SubmitType::class)
            ->add('imprimir', SubmitType::class)
        ;
    }

    public function validate($value, ExecutionContextInterface $context)
    {
        $tmp = [];

        foreach ($value['recepciones'] as $key => $recepcion) {
            if (!isset($recepcion['producto'])) {
                $context->buildViolation('Debe seleccionar un producto')
                    ->addViolation();
            }
        }

        foreach ($value['recepciones'] as $key => $recepcion) {
            if (!in_array($recepcion['producto'], $tmp)) {
                $tmp[] = $recepcion['producto'];
            } else {
                $context->buildViolation('Ha seleccionado más de una vez el mismo producto')
                    ->addViolation();
                break;
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new Callback(['callback' => [$this, 'validate']])
            ]
        ]);
    }
    
}
