<?php
namespace App\Controller;

use App\Entity\Actividad;
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
 * @Route("/api/actividades")
 */
class ActividadController extends FOSRestController
{

    /**
     * @Rest\Get("", name="actividades")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Tag(name="Actividad")
     */
     public function Actvidades()
     {
       $serializer = $this->get('jms_serializer');
       $em = $this->getDoctrine()->getManager();
       $proyectos = $em->getRepository("App:Actividad")->findAll();
       return new Response($serializer->serialize($proyectos, "json"));
     }

    /**
     * @Rest\Post("/alta", name="alta_actividades")
     * @Rest\RequestParam(name="nombre",nullable=false)
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Activos")
     */
    public function nuevaActividad(ParamFetcher $paramFetcher) {
      try {
          $serializer = $this->get('jms_serializer');
          $em = $this->getDoctrine()->getManager();
          $responsable = $this->getUser();
          $nombre = $paramFetcher->get('nombre');
          $actividad = new Actividad($nombre);
          $em->persist($actividad);
          $em->flush();
          $response = [ 'code'=>200,
                        'data'=>$actividad];
          return new Response($serializer->serialize($response, "json"));
      } catch (\Exception $e) {
          $response = ['code'=>500,
                       'message'=>$e->getMessage()];
          return new Response($serializer->serialize($response, "json"));
      }
    }

}
