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
        $editForm = $this->createForm(new PlatoType(), $entity);
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        $editForm->handleRequest($this->request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // EXTRAIGO DE LA BD LA RELACION DE ESTE ALIMENTO CON TODOS SUS PRODUCTOS
            $productosAlimentos = $em->getRepository('AppBundle:ProductoPlato')->findByPlato($entity->getId());

            // SI EN EL FORMULARIO DE EDICION SE ELIMINO DE LA COLECCION ALGUN PRODUCTO DEBO ELIMINARLO EXPLICITAMENTE
            foreach ($productosAlimentos as $value) {
                if (false === $entity->getProductoPlatos()->contains($value)) {
                    $this->em->remove($value);
                }
            }

            $this->em->persist($entity);
            $this->em->flush();

            return $this->redirectToRoute('admin', ['action' => 'list', 'entity' => $this->entity['name']]);
        }

        return $this->render('easy_admin/Plato/edit.html.twig', [
            'form' => $editForm->createView(),
            'entity_fields' => $this->entity['edit']['fields'],
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    public function createMenuNewForm($entity)
    {
        return $this->createForm(new MenuType(), $entity);
    }

    public function editMenuAction()
    {
        $id = $this->request->query->get('id');
        /** @var Menu $entity */
        $entity = $this->em->getRepository($this->entity['class'])->find($id);
        $editForm = $this->createForm(new MenuType(), $entity);
        $deleteForm = $this->createDeleteForm($this->entity['name'], $id);

        $editForm->handleRequest($this->request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
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

            return $this->redirectToRoute('admin', ['action' => 'list', 'entity' => $this->entity['name']]);
        }

        return $this->render('easy_admin/Menu/edit.html.twig', [
            'form' => $editForm->createView(),
            'entity_fields' => $this->entity['edit']['fields'],
            'entity' => $entity,
            'delete_form' => $deleteForm->createView(),
        ]);
    }
}
