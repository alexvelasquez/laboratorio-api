<?php

namespace App\Repository;


use Doctrine\ORM\EntityRepository;

class ProtocoloRepository extends EntityRepository
{
  public function configurarProtocolosAcutales($protocolos)
  {
    $em = $this->getEntityManager();
    foreach ($protocolos as $value) {
      $protocolos = $this->protocolosProyecto($value);
    }
    return $response;
  }
}
