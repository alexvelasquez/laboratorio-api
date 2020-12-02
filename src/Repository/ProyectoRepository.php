<?php

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class ProyectoRepository extends EntityRepository
{
  /** Retorno los tipos de comprobantes dada la condicion frente al IVA del cliente */
  public function proyectosConProtocolos()
  {
    $em = $this->getEntityManager();
    $proyectos = $em->getRepository("App:Proyecto")->findAll();
    $response =[];
    foreach ($proyectos as $value) {
      $qb = $em->createQueryBuilder();
      $qb->select('pt')
          ->from('App:Proyecto', 'py')
          ->innerJoin('App:Protocolo','pt','WITH','pt.proyecto = py')
          ->where('py = :proyecto')
          ->setParameter(':proyecto',$value);
      $protocolos = $qb->getQuery()->getArrayResult();
      $value->setProtocolos($protocolos);
      $response [] = $value;
    }
    return $response;
  }

}
