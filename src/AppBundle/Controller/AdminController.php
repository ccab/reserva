<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 4/Nov
 * Time: 11:45 PM.
 */
namespace AppBundle\Controller;

use AppBundle\Entity\Menu;
use AppBundle\Entity\TipoMenu;
use AppBundle\Form\PlatoType;
use AppBundle\Form\MenuType;
use AppBundle\Form\UsuarioType;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\Validator\Constraints\Regex;

class AdminController extends BaseAdminController
{
    private function executeDynamicMethod($methodNamePattern, array $arguments = array())
    {
        $methodName = str_replace('<EntityName>', $this->entity['name'], $methodNamePattern);
        if (!is_callable(array($this, $methodName))) {
            $methodName = str_replace('<EntityName>', '', $methodNamePattern);
        }

        return call_user_func_array(array($this, $methodName), $arguments);
    }


    protected function newAction()
    {
        $this->dispatch(EasyAdminEvents::PRE_NEW);

        $entity = $this->executeDynamicMethod('createNew<EntityName>Entity');

        $easyadmin = $this->request->attributes->get('easyadmin');
        $easyadmin['item'] = $entity;
        $this->request->attributes->set('easyadmin', $easyadmin);

        $fields = $this->entity['new']['fields'];

        $newForm = $this->executeDynamicMethod('create<EntityName>NewForm', array($entity, $fields));

        $newForm->handleRequest($this->request);
        if ($newForm->isValid()) {
            $this->dispatch(EasyAdminEvents::PRE_PERSIST, array('entity' => $entity));

            $this->executeDynamicMethod('prePersist<EntityName>Entity', array($entity));

            $this->em->persist($entity);
            $this->em->flush();

            $this->dispatch(EasyAdminEvents::POST_PERSIST, array('entity' => $entity));

            $refererUrl = $this->request->query->get('referer', '');

            return !empty($refererUrl)
                ? $this->redirect(urldecode($refererUrl))
                : $this->redirect($this->generateUrl('easyadmin', array('action' => 'new', 'entity' => $this->entity['name'])));
        }

        $this->dispatch(EasyAdminEvents::POST_NEW, array(
            'entity_fields' => $fields,
            'form' => $newForm,
            'entity' => $entity,
        ));

        return $this->render($this->entity['templates']['new'], array(
            'form' => $newForm->createView(),
            'entity_fields' => $fields,
            'entity' => $entity,
        ));
    }

    // http://symfony.com/doc/current/book/security.html#security-encoding-password
    public function prePersistUsuarioEntity($entity)
    {
        $encoded = $this->get('security.password_encoder')->encodePassword($entity, $entity->getClave());
        $entity->setClave($encoded);
    }

    public function createPlatoNewForm($entity)
    {
        return $this->createForm(PlatoType::class, $entity);
    }

    public function editPlatoAction()
    {
        $id = $this->request->query->get('id');
        $entity = $this->em->getRepository($this->entity['class'])->find($id);
        $editForm = $this->createForm(PlatoType::class, $entity);

        $editForm->handleRequest($this->request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // EXTRAIGO DE LA BD LA RELACION DE ESTE ALIMENTO CON TODOS SUS PRODUCTOS
            $productosAlimentos = $this->em->getRepository('AppBundle:ProductoPlato')->findByPlato($entity->getId());

            // SI EN EL FORMULARIO DE EDICION SE ELIMINO DE LA COLECCION ALGUN PRODUCTO DEBO ELIMINARLO EXPLICITAMENTE
            foreach ($productosAlimentos as $productoAlimento) {
                if (false === $entity->getProductosPlato()->contains($productoAlimento)) {
                    $this->em->remove($productoAlimento);
                }
            }

            $this->em->persist($entity);
            $this->em->flush();
            return $this->redirectToRoute('admin', ['action' => 'list', 'entity' => $this->entity['name']]);
        }

        return $this->render('easy_admin/Plato/new.html.twig', [
            'form' => $editForm->createView(),
        ]);
    }

    public function newMenuAction()
    {
        $entity = new Menu();
        $entity->setFecha(new \DateTime('Monday next week'));
        $form = $this->createForm(MenuType::class, $entity);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            //$this->addFlash('success', 'menu creado');
            return $this->redirectToRoute('admin', ['action' => 'new', 'entity' => $this->entity['name']]);
        }

        return $this->render('easy_admin/Menu/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function editMenuAction()
    {
        $id = $this->request->query->get('id');
        /** @var Menu $entity */
        $entity = $this->em->getRepository($this->entity['class'])->find($id);
        $form = $this->createForm(MenuType::class, $entity);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            // EXTRAIGO DE LA BD LA RELACION DE ESTE MENU CON TODOS SUS ALIMENTOS
            $menuPlatos = $this->getDoctrine()->getRepository('AppBundle:MenuPlato')->findByMenu($entity->getId());

            // SI EN EL FORMULARIO DE EDICION SE ELIMINO DE LA COLECCION ALGUN ALIMENTO DEBO ELIMINARLO EXPLICITAMENTE
            foreach ($menuPlatos as $mp) {
                if (false === $entity->getMenuPlatos()->contains($mp)) {
                    $this->em->remove($mp);
                }
            }

            $this->em->persist($entity);
            $this->em->flush();

            //$this->addFlash('success', 'menu creado');
            return $this->redirectToRoute('admin', ['action' => 'list', 'entity' => $this->entity['name']]);
        }

        return $this->render('easy_admin/Menu/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function createUsuarioEntityForm($entity)
    {
        return $this->createForm(UsuarioType::class, $entity);
    }

    public function listUsuarioAction()
    {
        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $form = $this->createFormBuilder()
            ->add('solapin', IntegerType::class, [
                'required' => false,
            ])
            ->add('nombre', null, [
                'required' => false,
                'constraints' => new Regex([
                    'pattern' => '/\d/',
                    'match' => false,
                ])
            ])
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $this->getDoctrine()
                ->getRepository('AppBundle:Usuario')->findBySearchParams($this->request->query->get('page', 1), $form->getData());
        }

        return $this->render('easy_admin/Usuario/list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
            'form' => $form->createView(),
        ));
    }

    public function listUnidadMedidaAction()
    {
        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $form = $this->createFormBuilder()
            ->add('nombre', null, [
                'required' => false,
                'constraints' => new Regex([
                    'pattern' => '/\d/',
                    'match' => false,
                ])
            ])
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $this->getDoctrine()
                ->getRepository('AppBundle:UnidadMedida')->findBySearchParams($this->request->query->get('page', 1), $form->getData());
        }

        return $this->render('easy_admin/UnidadMedida/list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
            'form' => $form->createView(),
        ));
    }

    public function listProductoAction()
    {
        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $form = $this->createFormBuilder()
            ->add('codigo')
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $this->getDoctrine()
                ->getRepository('AppBundle:Producto')->findBySearchParams($this->request->query->get('page', 1), $form->getData());
        }

        return $this->render('easy_admin/Producto/list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
            'form' => $form->createView(),
        ));
    }

    public function listCategoriaAction()
    {
        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $form = $this->createFormBuilder()
            ->add('nombre', null, [
                'required' => true,
                'constraints' => new Regex([
                    'pattern' => '/\d/',
                    'match' => false,
                ])
            ])
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $this->getDoctrine()
                ->getRepository('AppBundle:Categoria')->findBySearchParams($this->request->query->get('page', 1), $form->getData());
        }

        return $this->render('easy_admin/Categoria/list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
            'form' => $form->createView(),
        ));
    }

    public function listPlatoAction()
    {
        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $form = $this->createFormBuilder()
            ->add('nombre', null, [
                'required' => true,
                'constraints' => new Regex([
                    'pattern' => '/\d/',
                    'match' => false,
                ])
            ])
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $this->getDoctrine()
                ->getRepository('AppBundle:Plato')->findBySearchParams($this->request->query->get('page', 1), $form->getData());
        }

        return $this->render('easy_admin/Plato/list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
            'form' => $form->createView(),
        ));
    }

    public function listMenuAction()
    {
        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $form = $this->createFormBuilder()
            ->add('tipo', EntityType::class, [
                'class' => TipoMenu::class,
                'required' => false,
            ])
            ->add('fecha', DateType::class, [
                'data' => new \DateTime('today'),
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'date datepicker'],
            ])
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $this->getDoctrine()
                ->getRepository('AppBundle:Menu')->findBySearchParams($this->request->query->get('page', 1), $form->getData());
        }

        return $this->render('easy_admin/Menu/list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
            'form' => $form->createView(),
        ));
    }

}
