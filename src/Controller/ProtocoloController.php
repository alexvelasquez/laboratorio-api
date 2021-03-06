<?php
namespace App\Controller;

use App\Entity\Protocolo;
use App\Entity\ActividadProtocolo;
use App\Entity\User;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Extensions\BonitaUtilitiesTrait as ExtensionsBonitaUtilitiesTrait;
use Swagger\Annotations as SWG;

use App\Service\BonitaService;
use App\Service\CloudService;
/**
 * Class ApiController
 *
 * @Route("/api/protocolos")
 */
class ProtocoloController extends FOSRestController
{
  use ExtensionsBonitaUtilitiesTrait;
  /**
   * @Rest\Get("", name="protocolos")
   *
   * @SWG\Response(response=201,description="User was successfully registered")
   * @SWG\Response(response=500,description="User was not successfully registered")
   * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
   * @SWG\Tag(name="Protocolo")
   */
   public function protocolos()
   {
     $serializer = $this->get('jms_serializer');
     $em = $this->getDoctrine()->getManager();
     $protocolos = $em->getRepository("App:Protocolo")->findBy(['proyecto' => NULL]);
     return new Response($serializer->serialize($protocolos, "json"));
   }

  /**
   * @Rest\Get("/vertodos", name="protocolos_vertodos")
   *
   * @SWG\Response(response=201,description="User was successfully registered")
   * @SWG\Response(response=500,description="User was not successfully registered")
   * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
   * @SWG\Tag(name="Protocolo")
   */
   public function verTodos()
   {
     $serializer = $this->get('jms_serializer');
     $em = $this->getDoctrine()->getManager();
     $protocolos = $em->getRepository("App:Protocolo")->findAll();
     return new Response($serializer->serialize($protocolos, "json"));
   }

   /**
    * @Rest\Get("/actividades/{protocolo}", name="actividades_protocolos")
    *
    * @SWG\Response(response=201,description="User was successfully registered")
    * @SWG\Response(response=500,description="User was not successfully registered")
    * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
    * @SWG\Tag(name="Protocolo")
    */
    public function actividadProtocolos(Protocolo $protocolo)
    {
      $serializer = $this->get('jms_serializer');
      $em = $this->getDoctrine()->getManager();
      $protocolos = $em->getRepository("App:ActividadProtocolo")->findBy(['protocolo' => $protocolo]);
      return new Response($serializer->serialize($protocolos, "json"));
    }

    /**
    * @Rest\Put("/actualizarRemotos", name="actividades_protocolos")
    *
    * @SWG\Response(response=201,description="User was successfully registered")
    * @SWG\Response(response=500,description="User was not successfully registered")
    * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
    * @SWG\Tag(name="Protocolo")
    */
    public function actualizarRemotos(CloudService $cloud)
    {
      try{
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        //buscar todos los protocolos remotos actuales
        $remotos = $em->getRepository("App:Protocolo")->findBy(["fechaInicio" => NULL,"puntaje" => NULL,"esLocal"=>'N',"actual"=>'S']);
        foreach ($remotos as $value) {
            //pregunto por el estado de cada protocolo
            $response = $cloud->estado($value->getProtocoloId());
            if($response->status == 'Protocolo finalizado' && $response->protocolo->puntaje >= 6){ //aprobo el protocolo
              $value->setFechaInicio(new \Datetime($response->protocolo->fecha_inicio));
              $value->setFechaFin(new \Datetime($response->protocolo->fecha_fin));
              $value->setPuntaje($response->protocolo->puntaje);
              $value->setActual('N');
              $em->flush();
              /** busco el protocolo actual y lo seteo en S */
              $protocolo = $em->getRepository("App:Protocolo")->findOneBy(['proyecto'=>$value->getProyecto(),'fechaInicio'=>NULL,'puntaje'=>NULL],['orden'=>'ASC']);
              $protocolo->setActual('S');
              $em->flush();
            }
            elseif($response->status == 'Protocolo finalizado' && $response->protocolo->puntaje < 6){
              $value->setFechaInicio(new \Datetime($response->protocolo->fecha_inicio));
              $value->setFechaFin(new \Datetime($response->protocolo->fecha_fin));
              $value->setPuntaje($response->protocolo->puntaje);
              $value->setActual('N');
              $value->setError('S');
              $em->flush();
            }
        }
        $response = [ 'code'=>200,
        'data'=>'Protocolo actualizados'];
        return new Response($serializer->serialize($response, "json"));
        } catch (\Exception $e) {
        $response = ['code'=>500,
                'message'=>$e->getMessage()];
        return new Response($serializer->serialize($response, "json"));
        }
    }


   /**
    * @Rest\Get("/responsable/{responsable}", name="protocolos_responsable")
    * @SWG\Response(response=201,description="User was successfully registered")
    * @SWG\Response(response=500,description="User was not successfully registered")
    * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
    * @SWG\Tag(name="Protocolo")
    */
   public function protocolosResponsable(User $responsable, CloudService $cloud) {
     try {

      $response = $cloud->estado(1);
      $serializer = $this->get('jms_serializer');
      $em = $this->getDoctrine()->getManager();
      $protocolos = $em->getRepository("App:Protocolo")->findBy(["responsable"=>$responsable, "fechaInicio" => NULL, "esLocal" => "S", "actual" => "S"]); //Todos los protocolos que el responsable tiene asignados
      //  foreach ($protocolos as $value) {
      //   //   Por cada protocolo, recupero el proyecto al que pertenece
      //    $protocolosProyecto = $em->getRepository("App:Proyecto")->protocolosProyecto($value->getProyecto());
      //    $em->getRepository("App:Proyecto")->configurarEjecucion($protocolosProyecto,$value);
      //  }
       $response = [ 'code'=>200,
                     'data'=>$protocolos];
       return new Response($serializer->serialize($response, "json"));
     } catch (\Exception $e) {
         $response = ['code'=>500,
                      'message'=>$e->getMessage()];
         return new Response($serializer->serialize($response, "json"));
     }
   }


    /**
     * @Rest\Post("/alta", name="nuevo_protocolo")
     * @Rest\RequestParam(name="actividades",nullable=false)
     * @Rest\RequestParam(name="nombre",nullable=false)
     * @Rest\RequestParam(name="local",nullable=true)
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Protocolo")
     */
    public function nuevoProtocolo(ParamFetcher $paramFetcher) {
      try {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $nombre = $paramFetcher->get('nombre');
        $local = $paramFetcher->get('local');
        $protocolo = new Protocolo($nombre,$local);
        $em->persist($protocolo);
        $actividades = $paramFetcher->get('actividades');
        foreach ($actividades as $value) {
          $actividad = $em->getRepository("App:Actividad")->find($value);
          $actividadProtocolo = new ActividadProtocolo($protocolo,$actividad);
          $em->persist($actividadProtocolo);
        }
        $em->flush();
        $response = [ 'code'=>200,
                      'data'=>$protocolo];
        return new Response($serializer->serialize($response, "json"));
      } catch (\Exception $e) {
          $response = ['code'=>500,
                       'message'=>$e->getMessage()];
          return new Response($serializer->serialize($response, "json"));
      }
    }



    /**
     * @Rest\Post("/realizarProtocolo/{protocolo}", name="realizar_protocolo")
     * @Rest\RequestParam(name="puntaje",nullable=false)
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Protocolo")
     */
    public function realizarProtocolo(ParamFetcher $paramFetcher, Protocolo $protocolo, BonitaService $bonita) {
      try {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $puntaje = $paramFetcher->get('puntaje');
        $proyecto_id = $protocolo->getProyecto()->getProyectoId();
        $protocolo->setPuntaje($puntaje);
        $protocolo->setFechaInicio(new \DateTime());
        $protocolo->setFechaFin(new \DateTime());
        $protocolo->setActual('N');
        $em->flush();
        $siguiente = null;
        if ($puntaje < 6) {
          # Si el puntaje no es suficiente, se setea el actual en N y error en S. No se inicializa el siguiente. Corroborar que se haga si se resuelve la opcion "continuar"
          $protocolo->setError("S");
        } else {
          $repo = $em->getRepository("App:Protocolo");
          $siguiente = $repo->findOneBy(['proyecto'=>$protocolo->getProyecto(),'fechaInicio'=>NULL,'puntaje'=>NULL],['orden'=>'ASC']);
          if (!empty($siguiente)) {
            $siguiente->setActual("S");
          } else {
            $repo = $em->getRepository("App:Proyecto");
            $proyecto = $repo->find($proyecto_id);
            $proyecto->setFechaFin(new \DateTime);
          }
        }
        $em->flush();
        /** configuracion bonita **/
        $this->setVariablesBonita($bonita,$protocolo->getProyecto(),$siguiente,$puntaje);
        $response = [ 'code'=>200,
                      'data'=>$protocolo];
        return new Response($serializer->serialize($response, "json"));
      } catch (\Exception $e) {
          $response = ['code'=>500,
                       'message'=>$e->getMessage()];
          return new Response($serializer->serialize($response, "json"));
      }
    }

    /**
     * @Rest\Post("/reestablecer/{protocolo}", name="reestablecer_protocolo")
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Protocolo")
     */
    public function reestablecerProtocolo(Protocolo $protocolo)
    {
      $serializer = $this->get('jms_serializer');
      $em = $this->getDoctrine()->getManager();
      $protocolo->setFechaFin(NULL);
      $protocolo->setFechaInicio(NULL);
      $em->flush();
      $response = [ 'code'=>200,
                    'data'=>$protocolo];
      return new Response($serializer->serialize($response, "json"));
    }

}
