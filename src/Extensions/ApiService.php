<?php

namespace App\Extensions;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trait que brinda los métodos básicos de HTTP con CURL
 */
abstract class ApiService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * URL base de la API a consumir
     *
     * @var string
     */
    private $baseUrl;

    public function __construct(
        LoggerInterface $logger,
        string $baseUrl)
    {
        $this->logger = $logger;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Ejecuta un HTTP GET a la $url indicada; hace un json_decode del resultado
     *
     * @param string $url
     * @param boolean $jsonAssoc si es True, devuelve el JSON como un arreglo asociativo; si es Falso, como un objeto
     *
     * @return object|array
     */
    public function get(string $url, bool $jsonAssoc = false)
    {
        $curl = $this->curl($url);
        return $this->execCurl($curl, $jsonAssoc);
    }

    /**
     * Ejecuta un HTTP POST a la $url indicada; hace un json_decode del resultado
     *
     * @param string $url
     * @param string $data que va al body del request
     * @param boolean $jsonAssoc si es True, devuelve el JSON como un arreglo asociativo; si es Falso, como un objeto
     *
     * @return object|array
     */
    public function post(string $url, string $data, bool $jsonAssoc = false, $contentJson = true)
    {

        $curl = $this->curl($url, 'POST', $contentJson);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        return $this->execCurl($curl, $jsonAssoc);
    }

    /**
     * Ejecuta un HTTP PUT a la $url indicada; hace un json_decode del resultado
     *
     * @param string $url
     * @param string $data en formato JSON
     * @param boolean $jsonAssoc si es True, devuelve el JSON como un arreglo asociativo; si es Falso, como un objeto
     *
     * @return object|array
     */
    public function put(string $url, string $data, bool $jsonAssoc = false)
    {
        $curl = $this->curl($url, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        return $this->execCurl($curl, $jsonAssoc);
    }

    /**
     * Ejecuta un HTTP PUT a la $url indicada; hace un json_decode del resultado
     *
     * @param string $url
     * @param string $data en formato JSON
     * @param boolean $jsonAssoc si es True, devuelve el JSON como un arreglo asociativo; si es Falso, como un objeto
     *
     * @return object|array
     */
    public function patch(string $url, string $data, bool $jsonAssoc = false)
    {
        $curl = $this->curl($url, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        return $this->execCurl($curl, $jsonAssoc);
    }

    /**
     * Devuelve un objeto cURL con la URL base + $url; tambien setea los HEADERS para el TOKEN y el content-type application/json
     *
     * @param string $url URL que se concatena a la URL base de la API
     * @param string $method el metodo HTTP a ejecutar (puede ser 'GET', 'POST', 'PUT', 'PATCH', 'DELETE', ...)
     *
     * @return curl
     */
    protected function curl(string $url, string $method = 'GET', $contentJson = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->baseUrl . $url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5000000);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        if($contentJson){
            $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        return $curl;
    }

    /**
     * Ejecuta el cURL que recibe por parametro, controlando excepciones, logeando, decodificando a JSON, etc
     *
     * @param $curl
     * @param boolean $jsonAssoc si es True, devuelve el JSON como un arreglo asociativo; si es Falso, como un objeto
     *
     * @return object|array
     */
    protected function execCurl($curl, bool $jsonAssoc = false)
    {
        try {
            dd(curl_getinfo($curl, CURLINFO_HTTP_CODE ));
            $data = json_decode(curl_exec($curl), $jsonAssoc);

            if ($jsonAssoc) {
                $result = [];
                $result['data'] = $data;
                $result['statusCode'] = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            } else {
                $result = new \StdClass;
                $result->data = $data;
                $result->statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            }

            curl_close($curl);
            return $result;
        } catch (\Exception $e) {
            $err = curl_error($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $message = get_class($this);
            $message .= " curl_error: $err || curl_code: $code";

            $this->logger->error("$message || Exception: $e");

            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }
}
