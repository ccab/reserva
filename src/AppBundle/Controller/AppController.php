<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Menu;
use AppBundle\Entity\MenuPlato;
use AppBundle\Entity\Plato;
use AppBundle\Entity\Reservacion;
use AppBundle\Entity\ReservacionMenuPlato;
use AppBundle\Entity\ReservacionVisitante;
use AppBundle\Entity\TipoMenu;
use AppBundle\Entity\Usuario;
use AppBundle\Form\MenuAprobarType;
use AppBundle\Form\MenuType;
use AppBundle\Form\ProductoEntradaType;
use AppBundle\Form\ProductoSalidaType;
use AppBundle\Form\ReservacionVisitanteType;
use AppBundle\Form\ResetType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

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
                'first_options' => ['label' => 'Clave Nueva'],
                'second_options' => ['label' => 'Confirmar Clave'],
            ])
            ->getForm();

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $form ->add('claveActual', PasswordType::class, [
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
        $date = new \DateTime("next $day");
        $entities = $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($date);
        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('aprobar', ['day' => $day]),
        ])->getForm();

        foreach ($entities as $key => $entity) {
            $form->add($key, new MenuAprobarType(), [
                'data_class' => 'AppBundle\Entity\Menu',
                'data' => $entity,
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($entities as $key => $entity) {
                $entityManager->persist($form->get($key)->getData());
            }

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
        $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
            'usuario' => $this->getUser(),
            'fecha' => $date,
        ]);
        $entities = $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($date);
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
        $entities = $this->getDoctrine()
            ->getRepository('AppBundle:Reservacion')->findAll();

        $selectForm = $this->createSelectForm($entities);
        $searchForm = $this->createSearchForm();

        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $entities = $this->getDoctrine()
                ->getRepository('AppBundle:Reservacion')
                ->findEfectuarCobro($searchForm->getData());

            $selectForm = $this->createSelectForm($entities);
        }

        $selectForm->handleRequest($request);
        if ($selectForm->isSubmitted() && $selectForm->isValid()) {
            $entities = [];
            foreach ($selectForm->getData() as $id => $selected) {
                if ($selected) {
                    $entities[] = $this->getDoctrine()
                        ->getRepository('AppBundle:Reservacion')->find($id);
                }
            }

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

            /*return $this->render('app/comp_pago.html.twig', [
                'entities' => $entities,
            ]);*/
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
        $productos = $this->getDoctrine()->getRepository('AppBundle:Producto')->findAll();
        $form = $this->createFormBuilder()->getForm();

        foreach ($productos as $key => $entity) {
            $form->add($key, new ProductoSalidaType(), [
                'data_class' => 'AppBundle\Entity\Producto',
                'data' => $entity,
            ]);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($productos as $key => $entity) {
                $entity->setCantidad($entity->getCantidad() - $form->get($key)->get('cantidadSalida')->getData());
            }

            $entityManager->flush();

            return $this->redirectToRoute('salida_producto');
        }

        return $this->render('app/salida_producto.html.twig', [
            'form' => $form->createView(),
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
     * @Route("/reporte/comprobante/pago", name="reporte_comprobante_pago")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function compPagoAction(Request $request)
    {
        $data = null;
        $matrix = [];

        if ($request->query->has('search')) {
            $data = unserialize($request->query->get('search'));
            $entities = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findPorRangoDeFecha($data);
        } else {
            $cobrada = $this->getDoctrine()->getRepository('AppBundle:EstadoReservacion')->findOneByNombre('Cobrada');
            $entities = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findByEstado($cobrada);
        }

        $form = $this->createFormBuilder($data)
            ->add('inicio', 'date')
            ->add('fin', 'date')
            ->add('enviar', 'submit')
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            return $this->redirectToRoute('reporte_comprobante_pago', ['search' => serialize($form->getData())]);
        }

        foreach ($entities as $entity) {
            $count = 0;
            $reservMenuPlato = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuPlato')->findByReservacion($entity->getId());

            /** @var ReservacionMenuPlato $rmp */
            foreach ($reservMenuPlato as $rmp) {
                $count += $rmp->getMenuPlato()->getPlato()->getPrecio();
            }

            $matrix[] = [
                'entity' => $entity,
                'total' => $count,
            ];
        }

        return $this->render('app/reporte_comp_pago.html.twig', [
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
    public function crearMenuAction(Request $request)
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
    }

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
            ->add('platos', EntityType::class, [
                'class' => Plato::class,
                'data' => $entity,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $this->getDoctrine()
                ->getRepository('AppBundle:Plato')
                ->find($form->get('platos')->getData());

            return $this->redirectToRoute('carta_tecnica', ['id' => $entity->getId()]);
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
        $form = $this->createForm(ReservacionVisitanteType::class, $entity);
        
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            
            $entityManager->persist($entity);
            $entityManager->flush();

            /*return $this->render('app/comp_pago_visitante.html.twig', [
                'entity' => $entity,
            ]);*/

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

        foreach ($entities as $entity) {
            $selectForm->add($entity->getId(), CheckboxType::class, [
                'value' => $entity->getId(),
                'required' => false,
                'label' => false,
            ]);
        }

        return $selectForm;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    private function createSearchForm()
    {
        $searchForm = $this->get('form.factory')->createNamedBuilder('searchForm')
            ->add('usuario', EntityType::class, [
                'class' => Usuario::class,
                'choice_label' => 'noSolapin',
                'required' => false,
            ])
            ->add('fechaInicial', DateType::class, [
                'data' => new \DateTime('monday next week')
            ])
            ->add('fechaFinal', DateType::class, [
                'data' => new \DateTime('sunday next week')
            ])
            ->add('tipoMenu', EntityType::class, [
                'class' => TipoMenu::class,
                'required' => false,
            ])
            ->getForm();

        return $searchForm;
    }
}
