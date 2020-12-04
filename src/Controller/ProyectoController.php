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
       $proyectos = $em->getRepository("App:Proyecto")->findAll();
       return new Response($serializer->serialize($proyectos, "json"));
     }

     /**
      * @Rest\Get("/vertodos", name="proyectos")
      *
      * @SWG\Response(response=201,description="User was successfully registered")
      * @SWG\Response(response=500,description="User was not successfully registered")
      * @SWG\Tag(name="Proyecto")
      */
      public function proyectosConProtocolos()
      {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $proyectos = $em->getRepository("App:Proyecto")->proyectosConProtocolos();
        return new Response($serializer->serialize($proyectos, "json"));
      }


    /**
     * @Rest\Post("/alta", name="nuevo")
     * @Rest\RequestParam(name="nombre",nullable=false)
     * @Rest\RequestParam(name="fecha_fin",nullable=true)
     * @Rest\RequestParam(name="protocolos",nullable=false)
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Proyecto")
     */
    public function nuevoProyecto(ParamFetcher $paramFetcher, BonitaService $bonita) {
      try {
          $serializer = $this->get('jms_serializer');
          $em = $this->getDoctrine()->getManager();
          $responsable = $this->getUser();
          $nombre = $paramFetcher->get('nombre');
          $protocolos = $paramFetcher->get('protocolos');
          $fechaFin = !empty($paramFetcher->get('fecha_fin')) ? new \DateTime($paramFetcher->get('fecha_fin')) : NULL;
          $proyecto = new Proyecto($nombre,$responsable,$fechaFin);
          $em->persist($proyecto);
          /** seteo los protocolos para con el proyecto creado **/
          foreach ($protocolos as $value) {
            $responsable = $em->getRepository("App:User")->find($value['responsable']);
            $protocolo = $em->getRepository("App:Protocolo")->find($value['protocolo_id']);
            $protocolo->setResponsable($responsable);
            $protocolo->setOrden($value['orden']);
            $protocolo->setProyecto($proyecto);
            $protocolo->setActual('N');
            // $em->getRepository("App:Proyecto")->configurarEjecucion($protocolosProyecto,$value);
          }
          $em->flush();
          $procotolo = $em->getRepository("App:Protocolo")->findBy([],['orden'=>'ASC'])[0];
          $procotolo->setActual('S');
          $em->flush();

          /** BonitaService **/
          $bonita->loginService($this->getUser()->getUsername());//me logeo en bonita;
          $caso = $bonita->createCase('Aprobacion de un medicamento');
          // $bonita->setVariableCase($caso->id,'protocolo',$serializer->serialize($protocolo, "json"));
          
          // $actividad = $bonita->getActivityCurrent($caso->id);
          // if(!empty($actividad)){
          //   $bonita->executeActivity($actividad[0]->id);//ejecuto la actividad
          // }

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
