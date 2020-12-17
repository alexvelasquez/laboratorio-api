<?php

namespace App\Controller;

use App\Entity\Proyecto;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use App\Service\BonitaService;
use App\Extensions\BonitaUtilitiesTrait as ExtensionsBonitaUtilitiesTrait;

/**
 * Class ApiController
 *
 * @Route("/api/gerenciales")
 */
class GerencialesController extends FOSRestController
{
    use ExtensionsBonitaUtilitiesTrait;
    /**
     * @Rest\Get("/protocolosAprobados", name="cantidadProtocolosAprobados")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Tag(name="Gerenciales")
     */
    public function protocolosAprobados()
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $proyectos = $em->getRepository("App:Proyecto")->findAll();
        return new Response($serializer->serialize($proyectos, "json"));
    }

    /**
     * @Rest\Get("/proyectosEnCursoProtocolosAprobados", name="proyectosEnCursoProtocolosAprobados")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Tag(name="Gerenciales")
     */
    public function proyectosEnCursoProtocolosAprobados()
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $proyectos = $em->getRepository("App:Proyecto")->proyectosConProtocolos();
        $response = [];
        foreach ($proyectos as $key => $proyecto) {
            if ($proyecto->getCasoId() != NULL && $proyecto->getFechaFin() == NULL) {
                // unset($proyectos[$key]);
                $response [] = $proyecto;
            }
        };
        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Get("/mayorCantidadProtocolosAprobados", name="mayorCantidadProtocolosAprobados")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Tag(name="Gerenciales")
     */
    public function mayorCantidadProtocolosAprobados()
    {   
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $proyectos = $em->getRepository("App:Proyecto")->proyectosConProtocolos();
        $response = [];

        foreach ($proyectos as $key => $proyecto) {
            # code...
            $aprobados = 0;
            foreach ($proyecto->getProtocolos() as $key => $p) {
                # code...
                if ($p->getPuntaje() > 6) {
                    # code...
                    $aprobados++;
                }
            }
            $response [] = ["proyecto" => $proyecto, "protocolos_aprobados" => $aprobados];
        };
        dd($response);
        return new Response($serializer->serialize($response, "json"));
    }

    /**
     * @Rest\Get("/proyectosEnCursoProtocolosAtrasados", name="proyectosEnCursoProtocolosAtrasados")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Tag(name="Gerenciales")
     */
    public function proyectosEnCursoProtocolosAtrasados()
    {
        //  Hay un problema y es que si seteamos una fecha fin al proyecto. Seguro cagamos alguna validacion que estemos haciendo por fechafin del proyecto
        // Sino agregar fecha fin a los protocolos
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $proyectos = $em->getRepository("App:Proyecto")->proyectosConProtocolos();
        $response = [];
        foreach ($proyectos as $proyecto) {
            if ($proyecto->getCasoId() != NULL && $proyecto->getFechaFin() != NULL) {
                // unset($proyectos[$key]);
                $atrasados = 0;
                foreach ($proyecto->getProtocolos() as $p) {
                    # Comparo si la fechafin del protocolo es mayor a la del proyecto
                    if ($p->getFechaFin() >= $proyecto->getFechaFin()) {
                        # Probablemente la condicion cambie
                        $atrasados++;
                    }
                }
                if ($atrasados > 0) {
                    $response [] = ["proyecto" => $proyecto, "protocolos_atrasados" => $atrasados];
                }
            }
        };
        // dd($response);
        return new Response($serializer->serialize($response, "json"));
    }
}
