<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;


class BonitaService
{
  private $cache;
  private $client;
  private $baseUrl;
  private $cacheNamespace;


  public function __construct(HttpClientInterface $client,string $baseUrl,string $cacheNamespace)
  {
      $this->client = $client;
      $this->baseUrl = $baseUrl;
      $this->cacheNamespace = $cacheNamespace;
      $this->cache= new FilesystemAdapter();
  }
  public function loginService($username){
    $response = $this->client->request('POST',$this->url('/loginservice'),[
                'body' => 'username='.$username.'&password=bpm&redirect=false&redirectURL=']);
    $cookie='';
    foreach ($response->getHeaders()['set-cookie'] as $value) {
      $value = explode(";", $value)[0];
      $value = explode("=",$value);
      $data[$value[0]]=$value[1]; //me guardo las cabceras
      $cookie .= $value[0].'='.$value[1].';';//me armo la cookie
    }
    $data['cookie'] = substr($cookie, 0, -1);

    $credenciales = $this->cache->getItem($this->cacheNamespace);
    $credenciales->set($data);
    $this->cache->save($credenciales);
    return $this->cache->getItem($this->cacheNamespace)->get();
  }

 /** busca un proceso por nombre **/
  public function findProcessByName($nameProcess){
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
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
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
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
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/bpm/case?p=0&c=10&f=processDefinitionId='.$proceso),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]
                ],
              );
    return  json_decode($response->getContent());
  }

  /** Seteo una variable de proceso**/
  public function setVariableCase($case,$variable,$value,$type)
  {
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('PUT',$this->url('/API/bpm/caseVariable/'.$case.'/'.$variable),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']],
                'json' => [
                  "type"=>$type,
                  'value' =>$value],
                  ]);
    return  json_decode($response->getContent());
  }

  /**actividad actual**/
   public function getActivityCurrent($case)
  {

    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    // dd($credenciales);
    $response = $this->client->request('GET',$this->url('/API/bpm/activity?p=0&c=10&f=caseId%3d'.$case),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']
                            ],
                  ]);
    $response = !empty(json_decode($response->getContent())) ? json_decode($response->getContent())[0] : NULL;
    // dd($response);
    return $response;
  }

  /** todas las variables de procesos **/
  public function getCaseVariables($case)
  {
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/bpm/caseVariable?p=0&c=10&f=case_id%3d'.$case),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]
                ],
              );
    return  json_decode($response->getContent());
  }

  public function getCaseVariable($case,$nameVariable)
  {
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/bpm/caseVariable/'.$case.'/'.$nameVariable),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]
                ],
              );
    return  json_decode($response->getContent());
  }

  /** finalizar actividad**/
   public function executeActivity($actividad)
  {
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
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
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
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
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
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
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
      $response = $this->client->request('DELETE',$this->url('/API/bpm/case/'.$case),[
        'headers'=> ['Content-Type'=>'application/json',
                    'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                    'Cookie'=>$credenciales['cookie']]]);
    return  json_decode($response->getContent());
  }


  public function userNameBonita($name)
  {

    // $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    // $response = $this->client->request('GET',$this->url('/API/bpm/actor?p=0&c=10&f=process_id='.$case.'&n=users&n=group&n=roles&n=memberships'),[
    //             'headers'=> ['Content-Type'=>'application/json',
    //                         'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
    //                         'Cookie'=>$credenciales['cookies']]]);
    // return  json_decode($response->getContent());
    $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/identity/user?p=0&c=10&o=lastname%20ASC&s=zarate&f=enabled%3dtrue'),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]]);
    return  json_decode($response->getContent());
  }

  public function asignedTask($task)
  {
        $credenciales = $this->cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('PUT',$this->url('/API/bpm/humanTask/120119'),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']],
                'json' => ['state' =>'skipped',
                          'assigned_id'=>'304']]);
  }

  private function url($url)
  {
    return $this->baseUrl.$url;
  }
}
