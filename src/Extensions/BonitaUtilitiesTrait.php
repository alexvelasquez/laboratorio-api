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
        $bonita->setVariableCase($caso,'protocolo_local',$protocolo->getEsLocal(),'java.lang.String');
      }
      else{
        $bonita->setVariableCase($caso,'protocolo_local','null','java.lang.String');
      }
      $actividad= $bonita->getActivityCurrent($caso);
      $bonita->executeActivity($actividad->id);
    }

}
