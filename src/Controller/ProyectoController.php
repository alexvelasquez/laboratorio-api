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

/**
 * Class ApiController
 *
 * @Route("/api/proyectos")
 */
class ProyectoController extends FOSRestController
{

    /**
     * @Rest\Get("", name="proyectos")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Tag(name="Proyecto")
     */
     public function proyectos()
     {
       $serializer = $this->get('jms_serializer');
       $em = $this->getDoctrine()->getManager();
       $proyectos = $em->getRepository("App:Proyectos")->findAll();
       return new Response($serializer->serialize($protocolos, "json"));
     }

    /**
     * @Rest\Post("/nuevo", name="nuevo")
     * @Rest\RequestParam(name="nombre",nullable=false)
     * @Rest\RequestParam(name="fecha_fin",nullable=true)
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Proyecto")
     */
    public function nuevoProyecto(ParamFetcher $paramFetcher) {
      try {
          $serializer = $this->get('jms_serializer');
          $em = $this->getDoctrine()->getManager();
          $responsable = $this->getUser();
          $nombre = $paramFetcher->get('nombre');
          $fechaFin = !empty($paramFetcher->get('fecha_fin')) ? new \DateTime($paramFetcher->get('fecha_fin')) : NULL;

          $proyecto = new Proyecto($nombre,$responsable,$fechaFin);
          $em->persist($proyecto);
          $em->flush();
          $response = [ 'code'=>200,
                        'data'=>$proyecto];
          return new Response($serializer->serialize($response, "json"));
      } catch (\Exception $e) {
          $response = ['code'=>500,
                       'message'=>$e->getMessage()];
          return new Response($serializer->serialize($response, "json"));
      }
    }

}
