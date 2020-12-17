<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;


class CloudService
{
  private $client;
  private $baseUrl;
  private $user;
  private $password;


  public function __construct(HttpClientInterface $client,string $baseUrl,string $user,$password)
  {
      $this->client = $client;
      $this->baseUrl = $baseUrl;
      $this->user = $user;
      $this->password = $password;
  }

  public function getToken(){
    $response = $this->client->request('POST',$this->url('/login_check'),
    [ 'headers'=> ['Content-Type'=>'application/json'],
      'json' => ['_username'=>$this->user,
                '_password'=>$this->password],
    ]);

    return json_decode($response->getContent())->token; 
  }

  public function altaProtocolo($protocolo){
    $token = $this->getToken();
    $response = $this->client->request('POST',$this->url('/protocolo/alta'),
    [ 'headers'=> ['Content-Type'=>'application/json',
                  'Authorization'=>'Bearer '.$token],
      'json' => ['_nroProtocolo'=>$protocolo->getProtocoloId(),
                 '_nombre'=>$protocolo->getNombre(),
                 '_orden'=>$protocolo->getOrden()],
    ]);
    return json_decode($response->getContent());
  }

  public function estado($protocolo){
    $token = $this->getToken();
    $response = $this->client->request('GET',$this->url('/protocolo/estado/'.$protocolo),
    [ 'headers'=> ['Content-Type'=>'application/json',
                  'Authorization'=>'Bearer '.$token],
    ]);
    return json_decode($response->getContent());
  }

  public function reestablecer($protocolo){
    $token = $this->getToken();
    $response = $this->client->request('GET',$this->url('/protocolo/reestablecer/'.$protocolo),
    [ 'headers'=> ['Content-Type'=>'application/json',
                  'Authorization'=>'Bearer '.$token],
    ]);
    return json_decode($response->getContent());
  }
  
  public function url($link)
  {
      return $this->baseUrl.$link;
  }
}