<?php

namespace App\Extensions;
trait BonitaUtilitiesTrait
{

    protected function setVariablesBonita($bonita,$proyecto, $protocolo,$puntaje=null,$continuar = null)
    {
      $caso =$proyecto->getCasoId();
      if(!empty($continuar)){
        $bonita->setVariableCase($caso,'continuar',$continuar,'java.lang.String');  
      }
      if(!empty($puntaje)){
        $bonita->setVariableCase($caso,'protocolo_puntaje',$puntaje,'java.lang.Integer');
      }
      if(!empty($protocolo)){
        $protocolo = json_encode(['protocolo_id'=>$protocolo->getProtocoloId(),
                                  'nombre'=>$protocolo->getNombre(),
                                  'es_local'=>$protocolo->getEsLocal()]);
        $bonita->setVariableCase($caso,'protocolo',$protocolo,'java.lang.String');
      }
      else{
        $bonita->setVariableCase($caso,'protocolo','null','java.lang.String');
      }
      $actividad= $bonita->getActivityCurrent($caso);
      $bonita->executeActivity($actividad->id);
    }

}
