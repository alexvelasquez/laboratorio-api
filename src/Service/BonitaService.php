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
  public function loginService()
  {
    $cache = new FilesystemAdapter();
    /** elimino la cache **/
    $cache->delete($this->cacheNamespace);
    /** agrego las nuevas credenciales en la cache**/
    $cookieBonita = $cache->get($this->cacheNamespace, function (ItemInterface $item) {
      $item->expiresAfter(43200);
      $response = $this->client->request('POST',$this->url('/loginservice'),[
                  'body' => 'username=walter.bates&password=bpm&redirect=false&redirectURL=']);
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
  public function findProcessByName(){
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('GET',$this->url('/API/bpm/process?f=name=Pool'),[
                'headers'=> [ 'Content-Type'=>'application/json',
                              'Cookie'=>$credenciales['cookie']]
                ]);
    return json_decode($response->getContent())[0];
  }

  public function startProcess($process)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
    $response = $this->client->request('POST',$this->url('/API/bpm/process/'.$process.'/instantiation'),[
                'headers'=> ['Content-Type'=>'application/json',
                            'X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']],
                'json' => ['ticket_account' => 'Jefe de proyecto',
                           "ticket_description"=>"issue description",
                           "ticket_subject"=>"Issue 1"
                         ]
                ],
              );
    return  json_decode($response->getContent());
  }

  /** Crea un caso para el proceso encontrado **/
  public function createCase($proceso)
  {
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

  /** Crea un caso para el proceso encontrado **/
  public function deleteCase($case)
  {
    $cache = new FilesystemAdapter();
    $credenciales = $cache->getItem($this->cacheNamespace)->get();
      $response = $this->client->request('DELETE',$this->url('/API/bpm/case/'.$case),[
                'headers'=> ['X-Bonita-API-Token'=>$credenciales['X-Bonita-API-Token'],
                            'Cookie'=>$credenciales['cookie']]
                ],
              );
    return  json_decode($response->getContent());
  }

  private function url($url)
  {
    return $this->baseUrl.$url;
  }
}
