<?php
/**
 * Created by PhpStorm.
 * User: carlos
 * Date: 4/Nov
 * Time: 11:45 PM.
 */
namespace AppBundle\Controller;

use AppBundle\Entity\Menu;
use AppBundle\Form\PlatoType;
use AppBundle\Form\MenuType;
use AppBundle\Form\UsuarioType;
use Symfony\Component\HttpFoundation\Request;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class AdminController extends BaseAdminController
{
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
        $entity->setFecha(new \DateTime('today'));
        $form = $this->createForm(MenuType::class, $entity);

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            $this->addFlash('success', 'menu creado');
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

    /*public function listUsuarioAction()
    {
        $fields = $this->entity['list']['fields'];
        $paginator = $this->findAll($this->entity['class'], $this->request->query->get('page', 1), $this->config['list']['max_results'], $this->request->query->get('sortField'), $this->request->query->get('sortDirection'));

        $form = $this->createFormBuilder()
            ->add('solapin')
            ->getForm();

        $form->handleRequest($this->request);
        if ($form->isSubmitted() && $form->isValid()) {

        }
        
        return $this->render('easy_admin/Usuario/list.html.twig', array(
            'paginator' => $paginator,
            'fields' => $fields,
            'delete_form_template' => $this->createDeleteForm($this->entity['name'], '__id__')->createView(),
            'form' => $form->createView(),
        ));
    }*/

}
