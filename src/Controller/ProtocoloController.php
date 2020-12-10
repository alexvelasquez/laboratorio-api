<?php
namespace App\Controller;

use App\Entity\Protocolo;
use App\Entity\ActividadProtocolo;
use App\Entity\User;
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
 * @Route("/api/protocolos")
 */
class ProtocoloController extends FOSRestController
{

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
    * @Rest\Get("/responsable/{responsable}", name="protocolos_responsable")
    * @SWG\Response(response=201,description="User was successfully registered")
    * @SWG\Response(response=500,description="User was not successfully registered")
    * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
    * @SWG\Tag(name="Protocolo")
    */
   public function protocolosResponsable(User $responsable) {
     try {

       $serializer = $this->get('jms_serializer');
       $em = $this->getDoctrine()->getManager();
       $protocolos = $em->getRepository("App:Protocolo")->findBy(["responsable"=>$responsable, "fechaInicio" => NULL]);
       foreach ($protocolos as $value) {
         $protocolosProyecto = $em->getRepository("App:Proyecto")->protocolosProyecto($value->getProyecto());
         $em->getRepository("App:Proyecto")->configurarEjecucion($protocolosProyecto,$value);
       }
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
    public function realizarProtocolo(ParamFetcher $paramFetcher, Protocolo $protocolo) {
      try {
        $serializer = $this->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $puntaje = $paramFetcher->get('puntaje');
        $protocolo->setPuntaje($puntaje);
        $protocolo->setFechaInicio(new \DateTime());
        $protocolo->setFechaFin(new \DateTime());
        $protocolo->setActual('N');

        $protocoloActual = $em->getRepository('App:Protocolo')->findOneBy(['proyecto'=>$protocolo->getProyecto(),'fechaInicio'=>NULL,'puntaje'=>NULL],['orden'=>'ASC']);
        if(!empty($protocoloActual)){
          $protocoloActual->setActual('S');
        }
        /** configuracion bonita **/
        $em->flush();
        $reponse = $this->ejecutarProtocoloBonita($bonita,$puntaje,$protocoloActual);

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
      $em = $this->getDoctrine()->getManager();
      $procotolo->setFechaFin(NULL);
      $protocolo->setFechaInicio(NULL);
      $em->flush();
      $response = [ 'code'=>200,
                    'data'=>$protocolo];
      return new Response($serializer->serialize($response, "json"));
    }


    public function ejecutarProtocoloBonita($bonita,$puntaje,$protocolo)
    {
      $caso =$protocolo->getProyecto()->getCasoId();
      $bonita->setVariableCase($caso,'resultado',$puntaje,'java.lang.Integer');
      if(!empty($protocolo)){
        $dataProtocolo = json_encode(['id_protocolo'=>$protocolo->getProtocoloId(),'es_local'=>$protocolo->getEsLocal()]);
        $bonita->setVariableCase($caso,'protocolo',$dataProtocolo,'java.lang.String');
      }
      else{
        $bonita->setVariableCase($caso,'protocolo','','java.lang.String');
      }

      $actividad= $bonita->getActivityCurrent($caso);
      // dd($actividad);
      $bonita->executeActivity($actividad->id);
      return 'Ejecutado'
    }



}
