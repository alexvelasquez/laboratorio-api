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
 * @Route("/api/usuarios")
 */
class UsuarioController extends FOSRestController
{

    /**
     * @Rest\Get("/responsables", name="responsables")
     *
     * @SWG\Response(response=201,description="User was successfully registered")
     * @SWG\Response(response=500,description="User was not successfully registered")
     * @SWG\Tag(name="Actividad")
     */
     public function Responsables()
     {
       $serializer = $this->get('jms_serializer');
       $em = $this->getDoctrine()->getManager();
       $usuarios = $em->getRepository("App:User")->findAll();
       $values = [];
       foreach ($usuarios as $value) {
           if($value->getRoles()[0] == 'ROLE_RESPONSABLE_PROTOCOLO'){
             $values[]=$value;
           }
        }
         $response = [ 'code'=>200,
                       'data'=>$values];
       return new Response($serializer->serialize($response, "json"));
     }



}
