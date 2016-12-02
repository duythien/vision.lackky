<?php
namespace Lackky\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

/**
 * Class ImagesController
 *
 * @package Lackky\Controllers
 */
class ImagesController extends ControllerBase
{

    /**
     * @return JsonResponse
     */
    public function describeAction()
    {
        if (!$this->request->isPost()) {
            return $this->respondWithError('Need provided a request post', 404);
        }
        $data   = $this->parserDataRequest();

        $img = $data['img'];
        if (!isset($img)) {
            return $this->respondWithError('Need provided a resouce image url', 405);
        }

        $body = [
            'url' => $img
        ];

        $parameters = $this->getParameter();

        if (!isset($parameters['maxCandidates'])) {
            $parameters['maxCandidates'] = 1;
        }

        //Created client request
        $client = $this->makeClient();

        try {
            $response = $client->request('POST', 'describe', [
                'query' => $parameters,
                'body' => json_encode($body)
            ]);
            echo $response->getBody()->getContents();

        } catch (ClientException $e) {
            if (!$e->hasResponse()) {
                return $this->respondWithError('We can not detected a error', 400);
            }
            echo \GuzzleHttp\Psr7\str($e->getResponse());
        }
    }

    /**
     * @return JsonResponse
     */
    public function analyzeAction()
    {
        $data   = $this->parserDataRequest();

        $img = $data['img'];
        if (!isset($img)) {
            return $this->respondWithError('Need provided a resouce image url', 405);
        }

        $body = ['url' => $img];
        $parameters = $this->getParameter();

        if (!isset($parameters['visualFeatures'])) {
            $parameters['visualFeatures'] = 'Adult';
        }

        //Created client request
        $client = $this->makeClient();
        try {
            $response = $client->request('POST', 'analyze', [
                'query' => $parameters,
                'body' => json_encode($body)
            ]);
            echo $response->getBody()->getContents();

        } catch (ClientException $e) {
            if (!$e->hasResponse()) {
                return $this->respondWithError('We can not detected this error', 400);
            }
            echo \GuzzleHttp\Psr7\str($e->getResponse());
        }
    }

    /**
     * @return Client
     */
    protected function makeClient()
    {
        $vision = $this->config->vision;

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Ocp-Apim-Subscription-Key' => $vision->secretKey,
        ];
        $client = new Client([
            'base_uri' => $vision->baseUrl,
            'timeout'  => 222.0,
            'headers' => $headers
        ]);

        return $client;
    }
}
