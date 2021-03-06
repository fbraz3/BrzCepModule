<?php

namespace BrzCepModule\Adapter;
use Zend\Http\Client;
use BrzCepModule\Response\EnderecoResponse;
use BrzCepModule\Exception\CepNotFoundException;

class CorreioControlAdapter implements CepAdapterInterface {
    /** @var  Client */
    protected $httpClient;

    /** @var  EnderecoResponse */
    protected $enderecoResponse;

    public function __construct(Client $httpClient,EnderecoResponse $enderecoResponse)
    {
        $this->httpClient = $httpClient;
        $this->enderecoResponse = $enderecoResponse;
    }

    public function getEnderecoByCep($cep)
    {
        $this->httpClient->setUri("http://cep.correiocontrol.com.br/{$cep}.json");

        $response = $this->httpClient->send();

        return $this->parseResponse($response->getBody());
    }

    protected function parseResponse($response)
    {
        if(!$this->isJson($response))
        {
            throw new CepNotFoundException();
        }

        $response = json_decode($response,true);

        $enderecoResponse = $this->enderecoResponse;

        if(isset($response['logradouro']) && !empty($response['logradouro']))
        {
            $enderecoResponse->setLogradouro($response['logradouro']);
        }

        if(isset($response['bairro']) && !empty($response['bairro']))
        {
            $enderecoResponse->setBairro($response['bairro']);
        }

        if(isset($response['localidade']) && !empty($response['localidade']))
        {
            $enderecoResponse->setLocalidade($response['localidade']);
        }

        if(isset($response['uf']) && !empty($response['uf']))
        {
            $enderecoResponse->setUf($response['uf']);
        }

        return $enderecoResponse;
    }

    protected function isJson ($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * @return EnderecoResponse
     */
    public function getEnderecoResponse()
    {
        return $this->enderecoResponse;
    }
}