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
 * @Route("/api/proyectos")
 */
class ProyectoController extends FOSRestController
{
    use ExtensionsBonitaUtilitiesTrait;
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
            $responsable = !empty($value['responsable']) ? $em->getRepository("App:User")->find($value['responsable']) : null;
            $protocolo = $em->getRepository("App:Protocolo")->find($value['protocolo_id']);
            $protocolo->setResponsable($responsable);
            $protocolo->setOrden($value['orden']);
            $protocolo->setProyecto($proyecto);
            // $protocolo->setActual('N');
            // $protocolo->setError('N');
            // $em->getRepository("App:Proyecto")->configurarEjecucion($protocolosProyecto,$value);
          }
          $em->flush();
          /** obtengo el primero a jecutar y le seteo el estado **/
          $procotolo = $em->getRepository("App:Protocolo")->findOneBy(['proyecto'=>$proyecto,'fechaInicio'=>NULL,'puntaje'=>NULL, "esLocal" => "S"],['orden'=>'ASC']);
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
        $protocolo = $em->getRepository('App:Protocolo')->findOneBy(['actual'=> "S"]);
        $this->setVariablesBonita($bonita,$proyecto,$protocolo,null,null);
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

          $proyecto = $em->getRepository("App:Proyecto")->find($paramFetcher->get('proyecto')['proyecto_id']);
          $decision = $paramFetcher->get('decision');

          // $responsable = $em->getRepository("App:Protocolo")->find(["puntaje" => 6]);
          $res = null;

          switch ($decision){

            case 'continuar':
              # Si continua, da por finalizado el protocolo. Y setea el siguiente como el actual
              $repo = $em->getRepository("App:Protocolo");
              // Busco el protocolo que tuvo error y lo modifico
              $actual = $repo->findOneBy(["proyecto" => $proyecto, "error" => "S"], []);
              // $actual->setActual("N");
              $actual->setError("N");

              //  Busco el protocolo que deberia ir a continuacion
              $siguiente = $repo->findOneBy(["proyecto" => $proyecto, "fechaInicio" => null, "puntaje" => null ], ["orden" => "ASC"]);
              if (!empty($siguiente)) {
                # code...
                $siguiente->setActual("S");
              }
              $this->setVariablesBonita($bonita,$proyecto,$siguiente,null,'S'); //'S' para CONTINUAR
            
              /** configuracion bonita **/

              $em->flush();
              $res = "Se ha omitido el error.";
            break;

            case 'r_proyecto':
              # Reinicia todos los protocolos, sus fechas de inicio, puntaje, etc
              $protocolos = $paramFetcher->get('proyecto')['protocolos'];
              $repo = $em->getRepository("App:Protocolo");

              foreach ($protocolos as $value) {
                # Recupero uno por uno los protocolos de la bd y los modifico
                $p = $repo->find($value["protocolo_id"]);
                // $res = $p->getProtocoloId();
                $p->setFechaInicio(null);
                $p->setFechaFin(null);
                $p->setPuntaje(null);
                $p->setActual("N");
                $p->setError("N");
              };

              $em->flush();
              // Recupero el primer protocolo a ejecutar de nuevo y lo habilito
              
              $protocoloActual = $repo->findBy(['proyecto'=>$proyecto,'fechaInicio'=>NULL,'puntaje'=>NULL], ["orden" => "ASC"])[0];
              $protocoloActual->setActual("S");
              /** bonita **/
              $this->setVariablesBonita($bonita,$proyecto, $protocoloActual,null,'S');
              $em->flush();

              // dd($protocoloActual);
              $res = "El proyecto se ha reiniciado con exito.";
              break;

            case 'r_protocolo':
              # Idem anterior pero solo con el protocolo con error
              $repo = $em->getRepository("App:Protocolo");

              // Recupero el protocolo que dio mal y lo reinicio
              $p = $repo->findOneBy(["proyecto" => $proyecto, "error" => "S"]);
              // $res = $p->getProtocoloId();
              $p->setFechaInicio(null);
              $p->setFechaFin(null);
              $p->setPuntaje(null);
              $p->setError("N");
              $p->setActual("S");

              $em->flush();
              /** BONITA **/
              $this->setVariablesBonita($bonita,$proyecto, $p,null,'S');
              $res = "El protocolo se ha reiniciado con exito.";
              break;

            case 'cancelar':
              # Da por finalizado el protocolo pero con error
              $protocolos = $paramFetcher->get('proyecto')['protocolos'];
              $repo = $em->getRepository("App:Protocolo");

              // Si lo cancela, el protocolo igualmente queda seteado con valor S en el campo Error
              foreach ($protocolos as $value) {
                # code...
                $p = $repo->find($value["protocolo_id"]);
                $p->setActual("N");
              }
              $proyecto->setFechaFin(new \DateTime);

              $em->flush();
              /** BONITA **/
              $this->setVariablesBonita($bonita,$proyecto, null,null,'N');
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


}
