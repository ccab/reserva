<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Reservacion;
use AppBundle\Entity\ReservacionMenuAlimento;
use AppBundle\Form\MenuAprobarType;
use AppBundle\Form\ProductoEntradaType;
use AppBundle\Form\ProductoSalidaType;
use AppBundle\Form\ResetType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route("/reset", name="reset")
     */
    public function resetAction(Request $request)
    {
        $id = $request->query->get('id');
        $entity = $this->getDoctrine()->getRepository('AppBundle:Usuario')->find($id);

        $form = $this->createForm(new ResetType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();

            $encoded = $this->get('security.password_encoder')->encodePassword($entity, $entity->getClave());
            $entity->setClave($encoded);

            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('admin', [
                'view' => 'list',
                'entity' => $request->get('entity'),
            ]);
        }

        return $this->render('app/reset.html.twig', [
                'entity' => $entity,
                'form' => $form->createView(), ]
        );
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
     * @Route("aprobar/menu/{day}", defaults={"day":"monday"}, name="aprobar_menu")
     */
    public function aprobarMenuAction($day)
    {
        $semana = $this->obtenerSemana($day);

        return $this->render('app/aprobar_menu.html.twig', [
            'semana' => $semana,
        ]);
    }

    /**
     * @Route("reservar/dia/{day}", defaults={"day":"monday"}, name="reservar", options={"expose"=true})
     */
    public function reservarAction(Request $request, $day)
    {
        $date = new \DateTime("next $day");
        $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
            'usuario' => $this->getUser(),
            'fecha' => $date,
        ]);
        $entities = $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($date);
        $reserMenuAlimIds = $this->getReservMenuAlimIds($date);
        $form = $this->createFormBuilder(null, [
            'action' => $this->generateUrl('reservar', ['day' => $day]),
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $entityManager = $this->getDoctrine()->getManager();
            $ids = $this->getIds($request);

            foreach ($ids as $id) {
                //OBTENER ELEMENTOS NECESARIOS PARA CREAR LA RELACION: RESERVACION-MENU-ALIMENTO
                $reservacion = $this->getReservacion($date);
                $menuAlimento = $this->getDoctrine()->getRepository('AppBundle:MenuAlimento')->find($id);
                $reservacionMenuAlimento = $this->getReservacionMenuAlimento($reservacion, $menuAlimento);

                $entityManager->persist($reservacion);
                $entityManager->persist($reservacionMenuAlimento);
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
     * @Route("reservar/semana/{day}", defaults={"day":"monday"}, name="reservar_semana")
     */
    public function reservarSemanaAction($day)
    {
        $semana = $this->obtenerSemana($day);

        return $this->render('app/reservar_semana.html.twig', [
            'semana' => $semana,
        ]);
    }

    /**
     * @Route("cancelar/dia/{day}", defaults={"day":"monday"}, name="cancelar", options={"expose"=true})
     */
    public function cancelarAction(Request $request, $day)
    {
        $date = new \DateTime("next $day");
        $reservCreada = $this->getDoctrine()->getRepository('AppBundle:EstadoReservacion')->findOneByNombre('Creada');
        $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
            'fecha' => $date,
            'usuario' => $this->getUser(),
            //'estado' => $reservCreada,
        ]);
        $entities = $reservacion ? $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($date) : null;
        $reserMenuAlimIds = $reservacion ? $this->getReservMenuAlimIds($date) : null;
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
            'ids' => $reserMenuAlimIds,
        ]);
    }

    /**
     * @Route("cancelar/semana/{day}", defaults={"day":"monday"}, name="cancelar_semana")
     */
    public function cancelarSemanaAction($day)
    {
        $semana = $this->obtenerSemana($day);

        return $this->render('app/cancelar_semana.html.twig', [
            'semana' => $semana,
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
            $reservMenuAlim = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuAlimento')->findByReservacion($entity->getId());
            foreach ($reservMenuAlim as $rma) {
                $count += $rma->getMenuAlimento()->getAlimento()->getPrecio();
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
     * @Route("/detalles/cobro/{id}", name="detalles_cobro")
     */
    public function detallesCobroAction(Request $request, Reservacion $entity)
    {
        $menus = $this->getDoctrine()->getRepository('AppBundle:Menu')->findByFecha($entity->getFecha());
        $reserMenuAlimIds = $this->getReservMenuAlimIds($entity->getFecha(), $entity->getUsuario());
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
    public function reporteCompPagoAction(Request $request)
    {
        $data = null;
        $matrix = [];

        if ($request->query->has('search')) {
            $data = unserialize($request->query->get('search'));
            $entities = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findPorRangoDeFecha($data);
        } else {
            $entities = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findAll();
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
            $reservMenuAlim = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuAlimento')->findByReservacion($entity->getId());
            foreach ($reservMenuAlim as $rma) {
                $count += $rma->getMenuAlimento()->getAlimento()->getPrecio();
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
            ->setCellValue('C1', 'Estado')
            /*->setCellValue('D1', 'Dias')
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
            ->setCellValue('S1', 'Labor a realizar')*/;

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
     * @param $menuAlimento
     *
     * @return ReservacionMenuAlimento
     */
    private function getReservacionMenuAlimento($reservacion, $menuAlimento)
    {
        $reservacionMenuAlimento = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuAlimento')->findOneBy([
            'reservacion' => $reservacion,
            'menuAlimento' => $menuAlimento,
        ]);
        if (!$reservacionMenuAlimento) {
            $reservacionMenuAlimento = new ReservacionMenuAlimento();
            $reservacionMenuAlimento->setReservacion($reservacion)
                ->setMenuAlimento($menuAlimento);

            return $reservacionMenuAlimento;
        }

        return $reservacionMenuAlimento;
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
     *
     * @return array
     */
    private function getReservMenuAlimIds($date, $user = null)
    {
        $reserMenuAlimIds = [];
        $user = is_null($user) ? $this->getUser() : $user;

        //REVISO SI EL USUSARIO TIENE UNA RESERVACION PARA ESA FECHA,
        //DE SER ASI OBTENGO LOS IDS DE LOS ALIMENTOS DEL MENU DE ESE DIA QUE FUERON RESERVADOS
        $reservacion = $this->getDoctrine()->getRepository('AppBundle:Reservacion')->findOneBy([
            'usuario' => $user,
            'fecha' => $date,
        ]);
        if ($reservacion) {
            $reservacionMenuAlimentos = $this->getDoctrine()->getRepository('AppBundle:ReservacionMenuAlimento')->findBy([
                'reservacion' => $reservacion,
            ]);

            foreach ($reservacionMenuAlimentos as $reservMenuAlimento) {
                $reserMenuAlimIds[] = $reservMenuAlimento->getMenuAlimento()->getId();
            }

            return $reserMenuAlimIds;
        }

        return $reserMenuAlimIds;
    }

    /**
     * @param $day
     *
     * @return array
     */
    private function obtenerSemana($day)
    {
        $semana = [
            'monday' => ['es' => 'Lunes', 'active' => $day == 'monday' ? true : false],
            'tuesday' => ['es' => 'Martes', 'active' => $day == 'tuesday' ? true : false],
            'wednesday' => ['es' => 'Miercoles', 'active' => $day == 'wednesday' ? true : false],
            'thursday' => ['es' => 'Jueves', 'active' => $day == 'thursday' ? true : false],
            'friday' => ['es' => 'Viernes', 'active' => $day == 'friday' ? true : false],
            'saturday' => ['es' => 'Sabado', 'active' => $day == 'saturday' ? true : false],
            'sunday' => ['es' => 'Domingo', 'active' => $day == 'sunday' ? true : false],
        ];

        return $semana;
    }
}
