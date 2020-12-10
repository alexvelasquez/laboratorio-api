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

          /** Creo el caso de bonita **/
          $caso = $bonita->createCase('Aprobacion de un medicamento');
          $proyecto = new Proyecto($nombre,$responsable,$fechaFin,$caso->id);
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
          /** obtengo el primero a jecutar y le seteo el estado **/
          $procotolo = $em->getRepository("App:Protocolo")->findBy([],['orden'=>'ASC'])[0];
          $procotolo->setActual('S');
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


    /**
     * @Rest\Post("/configurarProyecto/{proyecto}", name="configurar")
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Proyecto")
     */
    public function configurarProyecto(Proyecto $proyecto, BonitaService $bonita)
    {
      try {
    
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $protocolo = $em->getRepository('App:Protocolo')->findOneBy(['proyecto'=>$proyecto,'fechaInicio'=>NULL,'puntaje'=>NULL],['orden'=>'ASC']);
        $caso = $proyecto->getCasoId();
        $dataProtocolo = json_encode(['id_protocolo'=>$protocolo->getProtocoloId(),'es_local'=>$protocolo->getEsLocal()]);
        $bonita->setVariableCase($caso,'protocolo',$dataProtocolo,'java.lang.String');
        $actividad= $bonita->getActivityCurrent($caso);
        $bonita->executeActivity($actividad->id);
        $response = [ 'code'=>200,
                      'data'=>'Tarea ejecutada'];
        return new Response($serializer->serialize($response, "json"));
      } catch (\Exception $e) {
        $response = ['code'=>500,
                     'message'=>$e->getMessage()];
        return new Response($serializer->serialize($response, "json"));
      }

    }

    /**
     * @Rest\Post("/efectuarcambios", name="efectuarcambios")
     * @Rest\RequestParam(name="proyecto",nullable=false)
     * @Rest\RequestParam(name="decision",nullable=false)
     * @SWG\Response(response=201,description="Los cambios se han realizado con exito")
     * @SWG\Response(response=500,description="Ha ocurrido un error al intentar realizar los cambios")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Proyecto")
     */
    public function efectuarCambios(ParamFetcher $paramFetcher, BonitaService $bonita) {
      try {
          $serializer = $this->get('jms_serializer');
          $em = $this->getDoctrine()->getManager();

          $proyecto = $paramFetcher->get('proyecto');
          $decision = $paramFetcher->get('decision');

          // $responsable = $em->getRepository("App:Protocolo")->find(["puntaje" => 6]);
          $res = null;

          switch ($decision){

            case 'continuar':
              # Si continua, da por finalizado el protocolo. Y setea el siguiente como el actual
              $proyecto_id = $paramFetcher->get('proyecto')['proyecto_id'];
              $repo = $em->getRepository("App:Protocolo");

              $actual = $repo->findOneBy(["proyecto" => $proyecto_id, "actual" => "S"], []);
              $actual->setActual("N");

              $siguiente = $repo->findOneBy(["proyecto" => $proyecto_id, "fechaInicio" => null, "puntaje" => null ], ["orden" => "ASC"]);
              if (!empty($siguiente)) {
                # code...
                $siguiente->setActual("S");
                $this->setProtocoloBonita($bonita,$siguiente);
              }
              else{
                $this->setProtocoloBonita($bonita,$siguiente,true);
              }
              /** configuracion bonita **/

              $em->flush();
              $res = "Se ha omitido el error.";
            break;

            case 'r_proyecto':
              # Reinicia todos los protocolos, sus fechas de inicio, puntaje, etc
              $protocolos = $paramFetcher->get('proyecto')['protocolos'];
              $proyecto_id = $paramFetcher->get('proyecto')['proyecto_id'];
              $repo = $em->getRepository("App:Protocolo");

              foreach ($protocolos as $value) {
                # code...
                $p = $repo->find($value["protocolo_id"]);
                // $res = $p->getProtocoloId();
                $p->setFechaInicio(null);
                $p->setFechaFin(null);
                $p->setPuntaje(null);
                $p->setActual("N");
              };

              $em->flush();
              $proyecto = $em->getRepository('App:Proyecto')->find($proyecto_id);
              $protocoloActual = $repo->findBy(['proyecto'=>$proyecto,'fechaInicio'=>NULL,'puntaje'=>NULL], ["orden" => "ASC"])[0];
              $protocoloActual->setActual("S");
              /** bonita **/
              $bonita->setVariableCase($caso,'continuar','S','java.lang.String');
              $this->setProtocoloBonita($bonita,$protocoloActual);
              $em->flush();

              // dd($protocoloActual);
              $res = "El proyecto se ha reiniciado con exito.";
              break;

            case 'r_protocolo':
              # Idem anterior pero solo con el protocolo con error
              $proyecto_id = $paramFetcher->get('proyecto')['proyecto_id'];
              $repo = $em->getRepository("App:Protocolo");

              $p = $repo->findOneBy(["proyecto" => $proyecto_id, "error" => "S"]);
              // $res = $p->getProtocoloId();
              $p->setFechaInicio(null);
              $p->setFechaFin(null);
              $p->setPuntaje(null);

              $em->flush();
              /** BONITA **/
              $bonita->setVariableCase($caso,'continuar','S','java.lang.String');
              $this->setProtocoloBonita($bonita,$p);
              $res = "El protocolo se ha reiniciado con exito.";
              break;

            case 'cancelar':
              # Da por finalizado el protocolo pero con error
              $protocolos = $paramFetcher->get('proyecto')['protocolos'];
              $proyecto_id = $paramFetcher->get('proyecto')['proyecto_id'];
              $repo = $em->getRepository("App:Protocolo");

              foreach ($protocolos as $value) {
                # code...
                $p = $repo->find($value["protocolo_id"]);
                $p->setActual("N");
              };

              $repo = $em->getRepository("App:Proyecto");
              $proyecto = $repo->find($protocolo_id);

              $proyecto->setFechaFin(new \DateTime);

              $em->flush();
              $res = "El Proyecto se ha cancelado.";
              break;

            default:
              # Por si acaso
              break;
          };

          $response = [ 'code'=>200,
                        'data'=>$res];
          return new Response($serializer->serialize($response, "json"));
      } catch (\Exception $e) {
          $response = ['code'=>500,
                       'message'=>$e->getMessage()];
          return new Response($serializer->serialize($response, "json"));
      }
    }


    public function setProtocoloBonita($bonita,$protocolo,$restart=false){
      $caso = $protocolo->getProyecto()->getCasoId();
      if($restart){
        $bonita->setVariableCase($caso,'protocolo','','java.lang.String');
      }
      else{
        $dataProtocolo = json_encode(['id_protocolo'=>$protocolo->getProtocoloId(),'es_local'=>$protocolo->getEsLocal()]);
        $bonita->setVariableCase($caso,'protocolo',$dataProtocolo,'java.lang.String');
      }
      $actividad= $bonita->getActivityCurrent($caso);
      $bonita->executeActivity($actividad->id);
    }

}
