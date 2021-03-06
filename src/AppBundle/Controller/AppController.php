<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Conversion;
use AppBundle\Entity\Menu;
use AppBundle\Entity\MenuPlato;
use AppBundle\Entity\Plato;
use AppBundle\Entity\Producto;
use AppBundle\Entity\ProductoPlato;
use AppBundle\Entity\Recepcion;
use AppBundle\Entity\RecepcionProducto;
use AppBundle\Entity\Reservacion;
use AppBundle\Entity\ReservacionMenuPlato;
use AppBundle\Entity\ReservacionVisitante;
use AppBundle\Entity\TipoMenu;
use AppBundle\Entity\Usuario;
use AppBundle\Form\MenuAprobarType;
use AppBundle\Form\MenuType;
use AppBundle\Form\ProductoEntradaType;
use AppBundle\Form\ProductoSalidaType;
use AppBundle\Form\RecepcionarProductoType;
use AppBundle\Form\RecepcionType;
use AppBundle\Form\ReservacionVisitanteType;
use AppBundle\Form\ResetType;
use Doctrine\Common\Collections\ArrayCollection;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Exception\NoSuchMetadataException;

class AppController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('app/index.html.twig');
    }

    /**
     * @Route("/cambiar/clave", name="cambiar_clave")
     */
    public function cambiarClaveAction(Request $request)
    {
        $form = $this->createChangePasswordForm();
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $entity = $this->getDoctrine()
                ->getRepository('AppBundle:Usuario')
                ->find($request->query->get('id'));
            $entityManager = $this->getDoctrine()->getManager();

            $encoded = $this->get('security.password_encoder')
                ->encodePassword($entity, $form->get('claveNueva')->getData());
            $entity->setClave($encoded);
            $entityManager->persist($entity);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('app/reset.html.twig', [
                'form' => $form->createView(),]
        );
    }

    private function createChangePasswordForm()
    {
        $form = $this->createFormBuilder()
            ->add('claveNueva', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_name' => 'clave',
                'first_options' => ['label' => 'Nueva clave'],
                'second_name' => 'confirm',
                'second_options' => ['label' => 'Confirmar clave'],
            ])
            ->getForm();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $form->add('claveActual', PasswordType::class, [
                'constraints' => new UserPassword()
            ]);
        }

        return $form;
    }

    /**
     * @Route("aprobar/dia/{day}", defaults={"day":"monday"}, name="aprobar", options={"expose"=true})
     */
    public function aprobarAction(Request $request, $day)
    {
        $date = new \DateTime("$day next week");
        $entities = $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($date);
        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('aprobar', ['day' => $day]),
        ])->getForm();

        foreach ($entities as $key => $entity) {
            $form->add($key, new MenuAprobarType(), [
                'data' => $entity,
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('aprobar_menu', ['day' => $day]);
        }

        return $this->render('app/aprobar.html.twig', [
            'entities' => $entities,
            'aprobar_form' => $form->createView(),
        ]);
    }

    //@Security("has_role('ROLE_JEFE')")
    /**
     * @Route("aprobar/menu/{day}", defaults={"day":"Monday"}, name="aprobar_menu")
     */
    public function aprobarMenuAction($day)
    {
        return $this->render('app/aprobar_menu.html.twig', [
            'semana' => $this->obtenerSemana($day),
        ]);
    }

    /**
     * @Route("reservar/dia/{day}", defaults={"day":"monday"}, name="reservar", options={"expose"=true})
     */
    public function reservarAction(Request $request, $day)
    {
        $date = new \DateTime("$day next week");
        $reservacion = $this->getDoctrine()
            ->getRepository('AppBundle:Reservacion')
            ->findOneBy([
                'usuario' => $this->getUser(),
                'fecha' => $date,
            ]);
        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:Menu')
            ->findBy([
                'fecha' => $date,
                'aprobado' => true,
            ]);
        $reserMenuAlimIds = $this->getReservMenuPlatosIds($date);
        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('reservar', ['day' => $day]),
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $ids = $this->getIds($request);

            foreach ($ids as $id) {
                //OBTENER ELEMENTOS NECESARIOS PARA CREAR LA RELACION: RESERVACION-MENU-PLATO
                $reservacion = $this->getReservacion($date);
                $menuPlato = $this->getDoctrine()->getRepository('AppBundle:MenuPlato')->find($id);
                $reservacionMenuPlato = $this->getReservacionMenuPlato($reservacion, $menuPlato);

                $entityManager->persist($reservacion);
                $entityManager->persist($reservacionMenuPlato);
                $entityManager->flush();
            }

            return $this->redirectToRoute('reservar_semana', ['day' => $day]);
        }

        return $this->render('app/_reservar.html.twig', [
            'reservacion' => $reservacion ? $reservacion : null,
            'entities' => $entities,
            'form' => $form->createView(),
            'ids' => $reserMenuAlimIds,
        ]);
    }

    /**
     * @Route("reservar/semana/{day}", defaults={"day":"Monday"}, name="reservar_semana")
     */
    public function reservarSemanaAction($day)
    {
        return $this->render('app/reservar_semana.html.twig', [
            'semana' => $this->obtenerSemana($day),
        ]);
    }

    /**
     * @Route("cancelar/dia/{day}", defaults={"day":"monday"}, name="cancelar", options={"expose"=true})
     */
    public function cancelarAction(Request $request, $day)
    {
        $date = new \DateTime("$day next week");
        $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
            'fecha' => $date,
            'usuario' => $this->getUser(),
        ]);
        $entities = $reservacion ? $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($date) : null;
        $reserMenuPlatosIds = $reservacion ? $this->getReservMenuPlatosIds($date) : null;

        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('cancelar', ['day' => $day]),
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
                'fecha' => $date,
                'usuario' => $this->getUser(),
            ]);

            $reservCancelada = $this->getDoctrine()->getRepository('AppBundle:EstadoReservacion')->findOneByNombre('Cancelada');
            $reservacion->setEstado($reservCancelada);
            $entityManager->flush();

            return $this->redirectToRoute('cancelar_semana', ['day' => $day]);
        }

        return $this->render('app/_cancelar.html.twig', [
            'reservacion' => $reservacion ? $reservacion : null,
            'entities' => $entities,
            'form' => $form->createView(),
            'ids' => $reserMenuPlatosIds,
        ]);
    }

    /**
     * @Route("cancelar/semana/{day}", defaults={"day":"Monday"}, name="cancelar_semana")
     */
    public function cancelarSemanaAction($day)
    {
        return $this->render('app/cancelar_semana.html.twig', [
            'semana' => $this->obtenerSemana($day),
        ]);
    }

    /**
     * @Route("/cobrar", name="cobrar")
     */
    public function cobrarAction(Request $request)
    {
        $creada = $this->getDoctrine()->getRepository('AppBundle:EstadoReservacion')->findOneByNombre('Creada');
        $entities = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findByEstado($creada);
        $matrix = [];

        foreach ($entities as $entity) {
            $count = 0;
            $reservMenuPlato = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuPlato')->findByReservacion($entity->getId());

            /** @var ReservacionMenuPlato $rmp */
            foreach ($reservMenuPlato as $rmp) {
                $count += $rmp->getMenuPlato()->getPlato()->getPrecio();
            }

            $matrix[] = [
                'reservacion' => $entity,
                'total' => $count,
            ];
        }

        return $this->render('app/cobrar.html.twig', [
            'matrix' => $matrix,
        ]);
    }

    /**
     * @Route("/efectuar/cobro", name="efectuar_cobro")
     */
    public function efectuarCobroAction(Request $request)
    {
        $entities = null;

        if ($request->query->has('entities')) {
            $ids = unserialize($request->query->get('entities'));
            foreach ($ids as $id) {
                $entities[] = $this->getDoctrine()
                    ->getRepository('AppBundle:Reservacion')->find($id);
            }
        }

        $selectForm = $this->createSelectForm($entities);
        $searchForm = $this->createSearchForm();

        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $entities = $this->getDoctrine()
                ->getRepository('AppBundle:Reservacion')
                ->findEfectuarCobroIds($searchForm->getData());

            return $this->redirectToRoute('efectuar_cobro', [
                'entities' => serialize($entities),
            ]);
        }

        $selectForm->handleRequest($request);
        if ($selectForm->isSubmitted() && $selectForm->isValid()) {
            $entities = [];
            $cobrada = $this->getDoctrine()
                ->getRepository('AppBundle:EstadoReservacion')
                ->findOneByNombre('Cobrada');
            $cobradas = $this->getDoctrine()
                ->getRepository('AppBundle:Reservacion')
                ->findByEstado($cobrada);
            /** @var Reservacion $ultima */
            $ultima = end($cobradas);
            $num = $ultima->getNumerosComprobante();
            $ultimoComprobante = end($num);

            foreach ($selectForm->getData() as $id => $selected) {
                if ($selected) {
                    $entity = $this->getDoctrine()
                        ->getRepository('AppBundle:Reservacion')->find($id);
                    $entity->setEstado($cobrada)
                        ->setFechaCobrada(new \DateTime('today'));
                    $comprobantes = [];
                    //$ultimoComprobante = 100;
                    /** @var TipoMenu $tipoMenu */
                    foreach ($entity->getTiposDeMenu() as $tipoMenu) {
                        $comprobantes[$tipoMenu->getNombre()] = ++$ultimoComprobante;
                    }
                    $entity->setNumerosComprobante($comprobantes);
                    $entities[] = $entity;
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            /*return $this->render('app/comp_pago.html.twig', [
                'entities' => $entities,
            ]);*/

            $html = $this->render('app/comp_pago.html.twig', [
                'entities' => $entities,
            ])->getContent();

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="file.pdf"'
                ]
            );
        }

        return $this->render('app/efectuar_cobro.html.twig', [
            'selectForm' => $selectForm->createView(),
            'searchForm' => $searchForm->createView(),
            'entities' => $entities,
        ]);
    }

    /**
     * @Route("/detalles/cobro/{id}", name="detalles_cobro")
     */
    public function detallesCobroAction(Request $request, Reservacion $entity)
    {
        $menus = $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($entity->getFecha());
        $reserMenuAlimIds = $this->getReservMenuPlatosIds($entity->getFecha(), $entity->getUsuario());
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $cobrada = $this->getDoctrine()->getRepository('AppBundle:EstadoReservacion')->findOneByNombre('Cobrada');

            $entity->setEstado($cobrada);
            //$ultimoNumComp = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findByNumeroComprobante();
            // TODO obtener ultimo numero de comprobante
            $entity->setNumeroComprobante(1);
            $entityManager->flush();

            return $this->redirectToRoute('cobrar');
        }

        return $this->render('app/detalles_cobro.html.twig', [
            'entity' => $entity,
            'menus' => $menus,
            'ids' => $reserMenuAlimIds,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/salida/producto", name="salida_producto")
     */
    public function salidaProductoAction(Request $request)
    {
        $fecha = new \DateTime('today');
        if ($request->query->has('fecha')) {
            $fecha = unserialize($request->query->get('fecha'));
        }

        $searchForm = $this->getSearchFormSalida($fecha);
        $salidas = $this->getSalidas($fecha);
        $formSalidas = $this->getFormSalidas($salidas);

        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            return $this->redirectToRoute('salida_producto', [
                'fecha' => serialize($searchForm->get('fecha')->getData()),
            ]);
        }

        $formSalidas->handleRequest($request);
        if ($formSalidas->isValid() && $formSalidas->isSubmitted()) {
            if ($formSalidas->get('aceptar')->isClicked()) {
                $entityManager = $this->getDoctrine()->getManager();

                foreach ($salidas as $salida) {
                    /** @var Producto $producto */
                    $producto = $salida['producto'];
                    $producto->setCantidad($producto->getCantidad() - $salida['brutoTotal']);
                }

                $entityManager->flush();
                return $this->redirectToRoute('salida_producto');
            } elseif ($formSalidas->get('generar')->isClicked()) {
                $html = $this->render('app/vale_entrega.html.twig', [
                    'salidas' => $salidas,
                ])->getContent();

                return new Response(
                    $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                    Response::HTTP_OK,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="file.pdf"'
                    ]
                );
            }

        }

        return $this->render('app/salida_producto.html.twig', [
            'form' => $formSalidas->createView(),
            'searchForm' => $searchForm->createView(),
            'salidas' => $salidas,
        ]);
    }

    /**
     * @Route("/entrada/producto", name="entrada_producto")
     */
    public function entradaProductoAction(Request $request)
    {
        $productos = $this->getDoctrine()->getRepository('AppBundle:Producto')->findAll();
        $form = $this->createFormBuilder()->getForm();

        foreach ($productos as $key => $entity) {
            $form->add($key, new ProductoEntradaType(), [
                'data_class' => 'AppBundle\Entity\Producto',
                'data' => $entity,
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($productos as $key => $entity) {
                $entity->setCantidad($entity->getCantidad() + $form->get($key)->get('cantidadRecibida')->getData());
            }

            $entityManager->flush();

            return $this->redirectToRoute('entrada_producto');
        }

        return $this->render('app/entrada_producto.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("recepcionar/productos", name="recepcionar_productos")
     */
    public function recepcionarProductosAction(Request $request)
    {
        $form = $this->createForm(RecepcionType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('aceptar')->isClicked()) {
                $entityManager = $this->getDoctrine()->getManager();
                $data = $form->getData();
                $recepcionEntity = new Recepcion();
                $recepcionEntity->setFecha($data['fecha']);

                foreach ($data['recepciones'] as $recepcion) {
                    /** @var Producto $producto */
                    $producto = $recepcion['producto'];
                    $producto->setCantidad($producto->getCantidad() + $recepcion['cantidad']);

                    $recepcionProducto = new RecepcionProducto();
                    $recepcionProducto->setProducto($producto)
                        ->setRecepcion($recepcionEntity)
                        ->setCantidad($recepcion['cantidad']);
                    $entityManager->persist($recepcionProducto);
                }

                $entityManager->persist($recepcionEntity);
                $entityManager->flush();
                return $this->redirectToRoute('recepcionar_productos');
            } elseif ($form->get('imprimir')->isClicked()) {
                $html = $this->render('app/informe_recepcion.html.twig', [
                    'data' => $form->getData(),
                ])->getContent();

                return new Response(
                    $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                    Response::HTTP_OK,
                    [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename="file.pdf"'
                    ]
                );
            }
        }

        return $this->render('app/recepcionar_producto.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/reporte/comprobante/pago", name="reporte_comprobante_pago")
     */
    public function compPagoAction(Request $request)
    {
        $data = $request->query->has('search') ? unserialize($request->query->get('search')) : null;
        $form = $this->getSearchFormRangoFecha($data);
        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:Reservacion')
            ->findPorRangoDeFecha($data);
        $visitantes = $this->getDoctrine()
            ->getRepository('AppBundle:ReservacionVisitante')
            ->findPorRangoDeFecha($data);
        $entities = array_merge($entities, $visitantes);

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            return $this->redirectToRoute('reporte_comprobante_pago', ['search' => serialize($form->getData())]);
        }

        return $this->render('app/reporte_comp_pago.html.twig', [
            'entities' => $entities,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reporte/dinero", name="reporte_dinero")
     */
    public function reporteDineroAction(Request $request)
    {
        $data = $request->query->has('search') ? unserialize($request->query->get('search')) : null;
        $form = $this->getSearchFormRangoFecha($data);
        $matrix = [];
        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:Reservacion')
            ->findPorRangoDeFecha($data);
        $visitantes = $this->getDoctrine()
            ->getRepository('AppBundle:ReservacionVisitante')
            ->findPorRangoDeFecha($data);

        /** @var Reservacion $entity */
        foreach ($entities as $entity) {
            if (!array_key_exists($entity->getFechaCobrada()->format('d/m/Y'), $matrix)) {
                $matrix[$entity->getFechaCobrada()->format('d/m/Y')] = [
                    'cantidad' => 1,
                    'importe' => $entity->getPrecioTotal(),
                ];
            } else {
                $matrix[$entity->getFechaCobrada()->format('d/m/Y')]['cantidad'] =
                    $matrix[$entity->getFechaCobrada()->format('d/m/Y')]['cantidad'] + 1;
                $matrix[$entity->getFechaCobrada()->format('d/m/Y')]['importe'] =
                    $matrix[$entity->getFechaCobrada()->format('d/m/Y')]['importe'] + $entity->getPrecioTotal();
            }
        }

        /** @var ReservacionVisitante $visitante */
        foreach ($visitantes as $visitante) {
            if (!array_key_exists($visitante->getFecha()->format('d/m/Y'), $matrix)) {
                $matrix[$visitante->getFecha()->format('d/m/Y')] = [
                    'cantidad' => 1,
                    'importe' => $visitante->getPrecioTotal(),
                ];
            } else {
                $matrix[$visitante->getFecha()->format('d/m/Y')]['cantidad'] =
                    $matrix[$visitante->getFecha()->format('d/m/Y')]['cantidad'] + 1;
                $matrix[$visitante->getFecha()->format('d/m/Y')]['importe'] =
                    $matrix[$visitante->getFecha()->format('d/m/Y')]['importe'] + $visitante->getPrecioTotal();
            }
        }

        ksort($matrix);

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            return $this->redirectToRoute('reporte_dinero', ['search' => serialize($form->getData())]);
        }

        return $this->render('app/reporte_dinero.html.twig', [
            'matrix' => $matrix,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reporte/solicitud/diaria/desayuno", name="reporte_solicitud_diaria_desayuno")
     */
    public function solicDiariaDesayunoAction()
    {
        $matrix = [];
        $first = new \DateTime('monday next week midnight');
        $last = new \DateTime('sunday next week midnight');
        $dateInterval = \DateInterval::createFromDateString('1 day');

        for ($date = $first; $date <= $last; $date->add($dateInterval)) {
            $cantPan = $this->getDoctrine()
                ->getRepository('AppBundle:Reservacion')
                ->findPorTipoPlato('Desayuno', 'Pan', $date);

            $cantLeche = $this->getDoctrine()
                ->getRepository('AppBundle:Reservacion')
                ->findPorTipoPlato('Desayuno', 'Leche', $date);

            $matrix[] = [
                'fecha' => clone $date,
                'pan' => $cantPan,
                'leche' => $cantLeche,
            ];
        }

        return $this->render('app/solic_diaria_desayuno.html.twig', [
            'matrix' => $matrix,
        ]);
    }


    /**
     * @Route("/crear/menu", name="crear_menu")
     */
    /*public function crearMenuAction(Request $request)
    {
        $entity = new Menu();
        $entity->setFecha(new \DateTime('today'));
        $form = $this->createForm(MenuType::class, $entity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($entity);
            $entityManager->flush();

            $this->addFlash('success', 'menu creado');
            return $this->redirectToRoute('admin');
        }

        return $this->render('app/crear_menu.html.twig', [
            'form' => $form->createView(),
        ]);
    }*/

    /**
     * @Route("/get/plato/{id}", name="get_plato", options={"expose"=true})
     */
    public function getPlatoAction(Plato $entity)
    {
        return new JsonResponse([
            'nombre' => $entity->getNombre(),
            'norma' => $entity->getNorma(),
            'precio' => $entity->getPrecio(),
        ]);
    }

    /**
     * @Route("/get/producto/{id}", name="get_producto", options={"expose"=true})
     */
    public function getProductoAction(Producto $entity)
    {
        return new JsonResponse([
            'codigo' => $entity->getCodigo(),
            'um' => $entity->getUnidadMedida()->getAbreviatura(),
        ]);
    }

    /**
     * @Route("/menus/anteriores/{dia}/{mes}/{anno}", name="menus_anteriores", options={"expose"=true})
     */
    public function menusAnterioresAction($dia, $mes, $anno)
    {
        $date = \DateTime::createFromFormat('d/m/Y', "$dia/$mes/$anno");

        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:Menu')->findByFecha($date);

        return $this->render('app/menus_anteriores.html.twig', [
            'entities' => $entities,
        ]);
    }

    /**
     * @Route("/carta/tecnica/{id}", name="carta_tecnica", defaults={"id":0})
     */
    public function cartaTecnicaAction(Request $request, $id)
    {
        $entity = null;
        if ($id != 0) {
            $entity = $this->getDoctrine()->getRepository('AppBundle:Plato')->find($id);
        }

        $form = $this->createFormBuilder()
            /*->add('platos', EntityType::class, [
                'class' => Plato::class,
                'data' => $entity,
            ])*/
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $html = $this->render('app/carta_tecnica_pdf.html.twig', [
                'entity' => $entity,
            ])->getContent();

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="file.pdf"'
                ]
            );
        }

        return $this->render('app/carta_tecnica.html.twig', [
            'entity' => $entity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cobrar/reservacion/visitante", name="cobrar_visitante")
     */
    public function cobrarVisitanteAction(Request $request)
    {
        $entity = new ReservacionVisitante();
        $entity->setFecha(new \DateTime('today'));
        //$entity->setFecha(\DateTime::createFromFormat('d/m/Y', '16/6/2016'));
        $form = $this->createForm(ReservacionVisitanteType::class, $entity);
        $almuerzo = $this->getDoctrine()
            ->getRepository('AppBundle:TipoMenu')
            ->findOneByNombre('Almuerzo');
        /** @var Menu $menu */
        $menu = $this->getDoctrine()
            ->getRepository('AppBundle:Menu')->findOneBy([
                'fecha' => new \DateTime('today'),
                //'fecha' => \DateTime::createFromFormat('d/m/Y', '16/6/2016'),
                'tipoMenu' => $almuerzo,
            ]);

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($entity);
            $entityManager->flush();

            $html = $this->render('app/comp_pago_visitante.html.twig', [
                'entity' => $entity,
            ])->getContent();

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="file.pdf"'
                ]
            );
        }

        return $this->render('app/cobrar_visitante.html.twig', [
            'form' => $form->createView(),
            'importe' => is_null($menu) ? 0 : $menu->getPrecioPlatos(),
        ]);
    }

    /**
     * @Route("existencia/almacen", name="reporte_existencia_almacen")
     */
    public function existenciaAlmacenAction(Request $request)
    {
        $entities = $this->getDoctrine()->getRepository('AppBundle:Producto')->findAll();

        if ($request->query->has('imprimir')) {
            $html = $this->render('app/imprimir_existencia_alm.html.twig', [
                'entities' => $entities,
            ])->getContent();

            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="file.pdf"'
                ]
            );
        } else {
            return $this->render('app/existencia_almacen.html.twig', [
                'entities' => $entities,
            ]);
        }
    }

    /**
     * @Route("/menu/semanal", name="menu_semanal")
     */
    public function getMenuSemanalAction()
    {
        $semana = [];
        $first = new \DateTime('monday next week');
        $last = new \DateTime('sunday next week');
        $dateInterval = \DateInterval::createFromDateString('1 day');

        for ($date = $first; $date <= $last; $date->add($dateInterval)) {
            //$active = $date->format('l') == $day ? true : false;
            $menus = $this->getDoctrine()
                ->getRepository('AppBundle:Menu')
                ->findByFecha($date);

            $semana[] = [
                'day' => clone $date,
                'menus' => $menus,
            ];
        }

        return $this->render('app/menu_semanal.html.twig', [
            'semana' => $semana,
        ]);
    }

    /**
     * @Route("/reporte/platos/{grafico}", name="reporte_platos", defaults={"grafico":false})
     */
    public function reportePlatosAction(Request $request, $grafico)
    {
        $platos = [];
        $data = $request->query->has('search') ? unserialize($request->query->get('search')) : [
            'inicio' => new \DateTime('Monday last week'),
            'fin' => new \DateTime('Friday last week')
        ];
        $form = $this->getSearchFormRangoFecha($data);
        $form->add('graficar', SubmitType::class);

        $reservaciones = $this->getDoctrine()
            ->getRepository('AppBundle:Reservacion')
            ->findPorRangoDeFecha($data);

        /** @var Reservacion $reservacion */
        foreach ($reservaciones as $reservacion) {
            /** @var Plato $plato */
            foreach ($reservacion->getPlatos() as $plato) {
                if (!array_key_exists($plato->getId(), $platos)) {
                    $platos[$plato->getId()] = [
                        'plato' => $plato,
                        'aceptacion' => 1,
                    ];
                } else {
                    $platos[$plato->getId()] = [
                        'plato' => $plato,
                        'aceptacion' => $platos[$plato->getId()]['aceptacion'] + 1,
                    ];
                }
            }
        }

        /*if ($grafico) {
            return $this->render('app/reporte_grafico_platos.html.twig', [
                'data' => serialize($data),
            ]);
        }*/

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            if ($form->get('graficar')->isClicked()) {
                return $this->render('app/reporte_grafico_platos.html.twig', [
                    'data' => serialize($form->getData()),
                ]);
            }

            return $this->redirectToRoute('reporte_platos', [
                'search' => serialize($form->getData())
            ]);
        }

        return $this->render('app/reporte_platos.html.twig', [
            'platos' => $platos,
            'reservaciones' => count($reservaciones),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reporte/productos", name="reporte_productos")
     */
    public function reporteProductosAction(Request $request)
    {
        $data = $request->query->has('search') ? unserialize($request->query->get('search')) : [
            'inicio' => new \DateTime('Monday last week'),
            'fin' => new \DateTime('Friday last week')
        ];

        $form = $this->getSearchFormRangoFecha($data);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            return $this->redirectToRoute('reporte_productos', ['search' => serialize($form->getData())]);
        }

        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:RecepcionProducto')
            ->findPorRangoDeFecha($data);

        return $this->render('app/reporte_productos.html.twig', [
            'entities' => $entities,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/get/platos", name="get_platos", options={"expose"=true})
     */
    public function getPlatosAction(Request $request)
    {
        $platos = [];
        $aceptacion = [];

        $data = $request->query->has('data') ? unserialize($request->query->get('data')) : [
            'inicio' => new \DateTime('Monday last week'),
            'fin' => new \DateTime('Friday last week')
        ];

        $reservaciones = $this->getDoctrine()
            ->getRepository('AppBundle:Reservacion')
            ->findPorRangoDeFecha($data);

        /** @var Reservacion $reservacion */
        foreach ($reservaciones as $reservacion) {
            /** @var Plato $plato */
            foreach ($reservacion->getPlatos() as $plato) {
                if (!array_key_exists($plato->getId(), $platos)) {
                    $platos[$plato->getId()] = $plato->getNombre();
                    $aceptacion[$plato->getId()] = 1;
                } else {
                    $aceptacion[$plato->getId()] = $aceptacion[$plato->getId()] + 1;
                }
            }
        }

        $aux = [];
        foreach ($platos as $plato) {
            $aux[] = $plato;
        }

        $aux2 = [];
        foreach ($aceptacion as $value) {
            $aux2[] = $value;
        }

        return new JsonResponse([
            'platos' => $aux,
            'aceptacion' => $aux2,
        ]);
    }

    /**
     * @Route("/reporte/cobro/excel/{id}", name="reporte_cobro_excel")
     */
    public function crearExcelAction(Reservacion $entity)
    {
        // create an empty object
        $phpExcelObject = $this->createXSLObject($entity);
        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=dietas.xls');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    private function createXSLObject(Reservacion $entity)
    {
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $htmlHelper = $this->get('phpexcel')->createHelperHTML();

        $phpExcelObject->getProperties()->setCreator('Labiofam')
            ->setTitle('Reservacion')
            ->setSubject('Reservacion')
            ->setDescription('Reservacion');

        $phpExcelObject->setActiveSheetIndex(0)
            //Encabezado
            ////$htmlHelper->toRichTextObject('<b>In Bold!</b>')
            ->setCellValue('A1', 'Fecha')
            ->setCellValue('B1', 'Usuario')
            ->setCellValue('C1', 'Estado')/*->setCellValue('D1', 'Dias')
            ->setCellValue('D2', 'Estimado')
            ->setCellValue('E2', 'Real')
            ->setCellValue('F1', 'Fecha')
            ->setCellValue('F2', 'Pago')
            ->setCellValue('G2', 'Salida Estimada')
            ->setCellValue('H2', 'Regreso Estimado')
            ->setCellValue('I2', 'Salida Real')
            ->setCellValue('J2', 'Regreso Real')
            ->setCellValue('K2', 'Liquidada')
            ->setCellValue('L1', 'Vencimiento')
            ->setCellValue('L2', 'Estimado')
            ->setCellValue('M2', 'Real')
            ->setCellValue('N1', 'Importe')
            ->setCellValue('N2', 'Entregado')
            ->setCellValue('O2', 'Utilizado')
            ->setCellValue('P2', 'A entregar')
            ->setCellValue('Q1', 'Beneficiario')
            ->setCellValue('Q2', 'Nombre')
            ->setCellValue('R2', 'Area')
            ->setCellValue('S1', 'Labor a realizar')*/
        ;

        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A2', $entity->getFecha())
            ->setCellValue('B2', $entity->getUsuario()->getUsuario())
            ->setCellValue('C2', $entity->getEstado());

        /*
        $current = 3;
        foreach ($ids as $id) {
            $entity = $this->getDoctrine()->getRepository('AppBundle:Dieta')->find($id);
            $impEntCUP = $entity->getImpEntCUP()['d'] + $entity->getImpEntCUP()['a'] + $entity->getImpEntCUP()['c'] + $entity->getImporteEntregadoOtrosCUP();
            $entity->getFormaPagoHospedajeCUP() == 'Efectivo' ? $impEntCUP += $entity->getImporteEntregadoHospedajeCUP() : '';


            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A' . $current, $entity->getNumeroSolicitud())
                ->setCellValue('B' . $current, $entity->getNumero())
                ->setCellValue('C' . $current, $entity->getNumeroReembolso())
                ->setCellValue('D' . $current, $entity->getDiffDiasEstimado())
                ->setCellValue('E' . $current, $entity->getDiffDiasReal())
                ->setCellValue('F' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaPago()))
                ->setCellValue('G' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaSalidaEstimada()))
                ->setCellValue('H' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaRegresoEstimada()))
                ->setCellValue('I' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaSalidaReal()))
                ->setCellValue('J' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaRegresoReal()))
                ->setCellValue('K' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaLiquidado()))
                ->setCellValue('L' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaRegresoEstimada()->add(\DateInterval::createFromDateString('3 days'))))
                ->setCellValue('M' . $current, \PHPExcel_Shared_Date::PHPToExcel($entity->getFechaRegresoReal() ? $entity->getFechaRegresoReal()->add(\DateInterval::createFromDateString('3 days')) : $entity->getFechaRegresoReal()))
                ->setCellValue('N' . $current, $impEntCUP)
                ->setCellValue('O' . $current, $entity->getImporteUtilizadoCUP())
                ->setCellValue('P' . $current, $impEntCUP - $entity->getImporteUtilizadoCUP())
                ->setCellValue('Q' . $current, $entity->getNombreBeneficiario())
                ->setCellValue('R' . $current, $entity->getAreaBeneficiario())
                ->setCellValue('S' . $current, $entity->getLaborRealizar());

            /*$phpExcelObject->getActiveSheet()->getStyle('C'.$current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
            $phpExcelObject->getActiveSheet()->getStyle('D'.$current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
            */
        /*$phpExcelObject->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('B')->setAutoSize(false);
        $phpExcelObject->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $phpExcelObject->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
        $phpExcelObject->getActiveSheet()->getStyle('F' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->getStyle('G' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->getStyle('H' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->getStyle('I' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->getStyle('J' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->getStyle('K' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->getStyle('L' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->getStyle('M' . $current)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
        $phpExcelObject->getActiveSheet()->mergeCells('A1:A2');
        $phpExcelObject->getActiveSheet()->mergeCells('B1:B2');
        $phpExcelObject->getActiveSheet()->mergeCells('C1:C2');
        $phpExcelObject->getActiveSheet()->mergeCells('D1:E1');
        $phpExcelObject->getActiveSheet()->mergeCells('F1:K1');
        $phpExcelObject->getActiveSheet()->mergeCells('L1:M1');
        $phpExcelObject->getActiveSheet()->mergeCells('N1:P1');
        $phpExcelObject->getActiveSheet()->mergeCells('Q1:R1');
        $phpExcelObject->getActiveSheet()->mergeCells('Q1:R1');
        $phpExcelObject->getActiveSheet()->mergeCells('S1:S2');

        $current++;
    }*/

        $phpExcelObject->getActiveSheet()->setTitle('Reporte Cobro');
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        return $phpExcelObject;
    }

    /////////////////////////////////////////////////METODOS AUXILIARES///////////////////////////////////////////////////////////////

    /**
     * @param $reservacion
     * @param $menuPlato
     *
     * @return ReservacionMenuPlato
     */
    private function getReservacionMenuPlato($reservacion, $menuPlato)
    {
        $reservacionMenuPlato = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuPlato')->findOneBy([
            'reservacion' => $reservacion,
            'menuPlato' => $menuPlato,
        ]);
        if (!$reservacionMenuPlato) {
            $reservacionMenuPlato = new ReservacionMenuPlato();
            $reservacionMenuPlato->setReservacion($reservacion)
                ->setMenuPlato($menuPlato);

            return $reservacionMenuPlato;
        }

        return $reservacionMenuPlato;
    }

    /**
     * @param $date
     *
     * @return Reservacion
     */
    private function getReservacion($date)
    {
        $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
            'usuario' => $this->getUser(),
            'fecha' => $date,
        ]);
        if (!$reservacion) {
            $reservacion = new Reservacion();
            $reservacion->setFecha($date)
                ->setUsuario($this->getUser())
                ->setEstado($this->getDoctrine()->getRepository('AppBundle:EstadoReservacion')->findOneByNombre('Creada'));

            return $reservacion;
        }

        return $reservacion;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getIds(Request $request)
    {
        $ids = [];

        foreach ($request->request->all() as $attr) {
            if (!is_array($attr)) {
                $ids[] = $attr;
            }
        }

        return $ids;
    }

    /**
     * @param $date
     * @param null $user
     *
     * @return array
     */
    private function getReservMenuPlatosIds($date, $user = null)
    {
        $reserMenuPlatosIds = [];
        $user = is_null($user) ? $this->getUser() : $user;

        //REVISO SI EL USUSARIO TIENE UNA RESERVACION PARA ESA FECHA,
        //DE SER ASI OBTENGO LOS IDS DE LOS PLATOS DEL MENU DE ESE DIA QUE FUERON RESERVADOS
        $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
            'usuario' => $user,
            'fecha' => $date,
        ]);
        if ($reservacion) {
            $reservacionMenuPlatos = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuPlato')->findBy([
                'reservacion' => $reservacion,
            ]);

            foreach ($reservacionMenuPlatos as $reservacionMenuPlato) {
                $reserMenuPlatosIds[] = $reservacionMenuPlato->getMenuPlato()->getId();
            }

            return $reserMenuPlatosIds;
        }

        return $reserMenuPlatosIds;
    }

    private function obtenerSemana($day)
    {
        $week = [];
        $first = new \DateTime('monday next week');
        $last = new \DateTime('sunday next week');
        $dateInterval = \DateInterval::createFromDateString('1 day');

        for ($date = $first; $date <= $last; $date->add($dateInterval)) {
            $active = $date->format('l') == $day ? true : false;

            $week[] = [
                'day' => clone $date,
                'active' => $active,
            ];
        }

        return $week;
    }

    /**
     * @param $entities
     * @return \Symfony\Component\Form\Form
     */
    private function createSelectForm($entities)
    {
        $selectForm = $this->get('form.factory')->createNamedBuilder('selectForm')->getForm();

        if (!is_null($entities)) {
            foreach ($entities as $entity) {
                $selectForm->add($entity->getId(), CheckboxType::class, [
                    'value' => $entity->getId(),
                    'required' => false,
                    'label' => false,
                ]);
            }
        }

        return $selectForm;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function createSearchForm()
    {
        $searchForm = $this->get('form.factory')->createNamedBuilder('searchForm')
            ->add('solapin', IntegerType::class, [
                'required' => false,
            ])
            ->getForm();

        return $searchForm;
    }

    /**
     * @param $fecha
     * @return array
     */
    private function getSalidas($fecha)
    {
        $salidas = [];
        $fecha = is_null($fecha) ? \DateTime::createFromFormat('d/m/Y', '30/05/2016') : $fecha;

        $reservaciones = $this->getDoctrine()
            ->getRepository('AppBundle:Reservacion')
            ->findByFecha($fecha);

        /** @var Reservacion $reservacion */
        foreach ($reservaciones as $reservacion) {
            /** @var Plato $plato */
            foreach ($reservacion->getPlatos() as $plato) {
                /** @var ProductoPlato $productoPlato */
                foreach ($plato->getProductosPlato() as $productoPlato) {
                    $id = $productoPlato->getProducto()->getId();
                    $conversion = $this->getConversionPesoBruto($productoPlato);

                    // Si existe en el listado debo añadir el Peso Bruto
                    // de lo contrario lo creo
                    if (isset($salidas[$id])) {
                        $conversion += $salidas[$id]['brutoTotal'];
                    }

                    $salidas[$id] = [
                        'producto' => $productoPlato->getProducto(),
                        'brutoTotal' => $conversion,
                    ];
                }
            }
        }


        return $salidas;
    }

    /**
     * @param ProductoPlato $productoPlato
     * @return mixed
     */
    private function getConversionPesoBruto($productoPlato)
    {
        // Obtener el factor de conversion de la UM del Plato a la UM del Producto en almacen
        // Obtengo las posibles conversiones y busco el factor de la relacion correcta
        $conversiones = $productoPlato->getUnidadMedida()->getConversionesPlato();
        $factor = 0;
        /** @var Conversion $conv */
        foreach ($conversiones as $conv) {
            if ($conv->getUnidadMedidaProducto() == $productoPlato->getProducto()->getUnidadMedida()) {
                $factor = $conv->getFactor();
            }
        }

        $conversion = $productoPlato->getPesoBruto() * $factor;
        return $conversion;
    }

    /**
     * @param $salidas
     * @return Form
     */
    private function getFormSalidas($salidas)
    {
        return $this->createFormBuilder()
            ->add('aceptar', SubmitType::class)
            ->add('generar', SubmitType::class, [
                'label' => 'Generar comprobante'
            ])
            ->getForm();
    }

    /**
     * @param $fecha
     * @return Form
     */
    private function getSearchFormSalida($fecha)
    {
        $searchForm = $this->createFormBuilder()
            ->add('fecha', DateType::class, [
                'required' => false,
                'data' => $fecha,
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'date datepicker']
            ])
            ->add('buscar', SubmitType::class)
            ->getForm();
        return $searchForm;
    }

    /**
     * @param $data
     * @return Form
     */
    private function getSearchFormRangoFecha($data)
    {
        $form = $this->createFormBuilder($data)
            ->add('inicio', DateType::class, [
                'data' => is_null($data) ? new \DateTime('Monday next week') : $data['inicio'],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'date datepicker'],
            ])
            ->add('fin', DateType::class, [
                'data' => is_null($data) ? new \DateTime('Sunday next week') : $data['fin'],
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd/MM/yyyy',
                'attr' => ['class' => 'date datepicker']
            ])
            ->add('buscar', 'submit')
            ->getForm();

        return $form;
    }
}
