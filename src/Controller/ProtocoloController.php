<?php
namespace App\Controller;

use App\Entity\Protocolo;
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
     $protocolos = $em->getRepository("App:Protocolo")->findAll();
     return new Response($serializer->serialize($protocolos, "json"));
   }

    /**
     * @Rest\Post("/nuevo", name="nuevo_protocolo")
     * @Rest\RequestParam(name="responsable",nullable=false)
     * @Rest\RequestParam(name="proyecto",nullable=false)
     * @Rest\RequestParam(name="actividades",nullable=false)
     * @Rest\RequestParam(name="nombre",nullable=false)
     * @Rest\RequestParam(name="orden",nullable=true)
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
        $responsable = $em->getRepository('App:User')->find($paramFetcher->get('responsable'));
        $proyecto = $em->getRepository('App:Proyecto')->find($paramFetcher->get('proyecto'));
        $nombre = $paramFetcher->get('nombre');
        $orden = $paramFetcher->get('orden');
        $local = $paramFetcher->get('local');
        $protocolo = new Protocolo($nombre,$responsable,$proyecto,$orden,$local);
        $actividades = json_decode($paramFetcher->get('actividades'));
        foreach ($actividades as $value) {
          $actividad = new Actividad($value);
          $em->persist($actividad);
          $actividadProtocolo = new ActividadProtocolo($protocolo,$actividad);
          $em->persist($actividadProtocolo);
        }
        $em->persist($protocolo);
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
     * @Rest\Get("/test", name="test")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Parameter(name="_protocolo",in="body",type="string",description="protocolo",schema={})
     * @SWG\Tag(name="Protocolo")
     */
    public function test(BonitaService $bonita)
    {
      //
      $serializer = $this->get('jms_serializer');
      $data = $bonita->loginService();
      $response = [ 'code'=>200,
                    'data'=>$data];
      return new Response($serializer->serialize($response, "json"));
    }



}
