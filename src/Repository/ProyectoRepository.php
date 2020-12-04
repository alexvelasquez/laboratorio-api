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
      $protocolos = $this->protocolosProyecto($value);
      $value->setProtocolos($protocolos);
      $response [] = $value;
    }
    return $response;
  }

  public function protocolosProyecto($proyecto){
    $em = $this->getEntityManager();
    return $em->getRepository("App:Protocolo")->findBy(['proyecto'=>$proyecto],['orden'=>'ASC']);
  }

  public function configurarEjecucion($protocolos,&$protocolo){
    $protocoloAnterior = null;
    $protocolo->setEjecutable('N');
    foreach ($protocolos as $value) {
      if($value->getProtocoloId() == $protocolo->getProtocoloId()){
        if(empty($protocoloAnterior)){
          $protocolo->setEjecutable('S');
        }
        elseif(!empty($protocoloAnterior->getFechaInicio()) && !empty($protocoloAnterior->getFechaDesde())){
          $protocolo->setEjecutable('S');
        }
        break;
      }
      $protocoloAnterior = $value;
    }
  }

  public function reestablecerProyecto($proyecto){
    $em = $this->getEntityManager();
    $protocolos = $em->getRepository("App:Protocolo")->findBy(['proyecto'=>$proyecto],['orden'=>'ASC']);
    foreach ($protocolos as $value) {
        $value->setFechaInicio(NULL);
        $value->setFechaFin(NULL);
    }
    $em->flush();
  }
}
