<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;


class BonitaService
{
  private $client;
  private $baseUrl;
  private $cacheNamespace;


  public function __construct(HttpClientInterface $client,string $baseUrl,string $cacheNamespace)
  {
      $this->client = $client;
      $this->baseUrl = $baseUrl;
      $this->cacheNamespace = $cacheNamespace;
  }
  public function loginService($username){
    $cache = new FilesystemAdapter();
    /** elimino la cache **/
    $cache->delete($this->cacheNamespace);
    /** agrego las nuevas credenciales en la cache**/
    $cacheBonita = $cache->get($this->cacheNamespace, function (ItemInterface $item) {
      $item->expiresAfter(43200);
      $response = $this->client->request('POST',$this->url('/loginservice'),[
                  'body' => 'username=jefe.proyecto&password=bpm&redirect=false&redirectURL=']);
      $cookie='';
      foreach ($response->getHeaders()['set-cookie'] as $value) {
        $value = explode(";", $value)[0];
        $value = explode("=",$value);
        $data[$value[0]]=$value[1];
        $cookie .= $value[0].'='.$value[1].';';
      }
      $data['cookie'] = substr($cookie, 0, -1);
      return $data;
    });
    return $cache->getItem($this->cacheNamespace)->get();
  }

 /** busca un proceso por nombre **/
  public function findProcessByName($nameProcess){
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/bpm/process?f=name='.$nameProcess),[ //('Pool' nombre de un ejemplo de proceso) El nombre real  seria Aprobacion de medicamento
                'headers'=> [ 'Content-Type'=>'application/json',
                              'Cookie'=>$credenciales['cookie']]
                ]);
    return json_decode($response->getContent())[0];
  }

  /** Crea un caso para un proceso pasado como parametro**/
  public function createCase($nameProcess)
  {
    $proceso = $this->findProcessByName($nameProcess)->id; //me quedo con el id del proceso
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('POST',$this->url('/API/bpm/case'),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']],
                'json' => ['processDefinitionId' => $proceso]
                ],
              );
    return  json_decode($response->getContent());
  }

  public function getCasesProcess($proceso)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/bpm/case?p=0&c=10&f=processDefinitionId='.$proceso),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]
                ],
              );
    return  json_decode($response->getContent());
  }

  /** Seteo una variable de proceso**/
  public function setVariableCase($case,$variable,$value)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('PUT',$this->url('/API/bpm/caseVariable/'.$case.'/'.$variable),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']],
                'json' => [
                  "type"=>"java.lang.String",
                  'value' =>$value],
                  ]);
    return  json_decode($response->getContent());
  }

  /**actividad actual**/
   public function getActivityCurrent($case)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/bpm/activity?p=0&c=10&f=caseId%3d'.$case),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']],
                  ]);
    return  json_decode($response->getContent());
  }
  /** finalizar actividad**/
   public function executeActivity($actividad)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    // $actividad = ()$this->getActivityCurrent($case);
    $response = $this->client->request('POST',$this->url('/API/bpm/userTask/'.$actividad.'/execution?assign=true'),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]
                  ]);
    return  json_decode($response->getContent());
  }

  /** finalizar actividad**/
   public function finishActivity($actividad)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    // $actividad = ()$this->getActivityCurrent($case);
    $response = $this->client->request('PUT',$this->url('/API/bpm/activity/'.$actividad),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']],
                'json' => ['state' =>'completed'],
                  ]);
    return  json_decode($response->getContent());
  }

  /** actor **/
   public function actorByName($name)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $actividad = $this->getActivityCurrent($case);
    $response = $this->client->request('GET',$this->url('/API/bpm/actor?f=name='.$name),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]]);
    return  json_decode($response->getContent());
  }

  /** Crea un caso para el proceso encontrado **/
  public function deleteCase($case)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
      $response = $this->client->request('DELETE',$this->url('/API/bpm/case/'.$case),[
                'headers'=> ['X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]
                ]);
    return  json_decode($response->getContent());
  }

  // /** Crea un caso para el proceso encontrado **/
  // public function taskUser($case)
  // {
  //   $cache = new FilesystemAdapter();
  //   $credenciales = $cache->getItem($this->cacheNamespace)->get();
  //     $response = $this->client->request('DELETE',$this->url('/API/bpm/case/'.$case),[
  //               'headers'=> ['X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
  //                           'Cookie'=>$credenciales['cookie']]
  //               ]);
  //   return  json_decode($response->getContent());
  // }

  private function url($url)
  {
    return $this->baseUrl.$url;
  }
}
