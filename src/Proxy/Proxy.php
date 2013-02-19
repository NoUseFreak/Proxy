<?php
/**
 * This file is part of the Clastic package.
 *
 * (c) Dries De Peuter <dries@nousefreak.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Proxy;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class Proxy
{
    /**
     * @var Configuration
     */
    protected $configuration;

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function proxy(\Symfony\Component\HttpFoundation\Request $request)
    {
        $client = $this->getClient($request);

        $method = strtolower($request->getMethod());

        $response = $client->$method($request->getRequestUri())->send();

        return $this->createResponse($response, $request);
    }

    protected function getClient(\Symfony\Component\HttpFoundation\Request $request)
    {
        return new \Guzzle\Http\Client('{scheme}://{host}', array(
             'scheme' => $request->getScheme(),
             'host' =>  $this->configuration->getBackend()
        ));
    }

    protected function createResponse(\Guzzle\Http\Message\Response $gResponse, Request $request)
    {
        $response = new Response($gResponse->getBody(), $gResponse->getStatusCode());

        foreach ($gResponse->getHeaderLines() as $header) {
            list($name, $value) = explode(':', $header, 2);
            $response->headers->set($name, $value);
        }

        return $this->prepareResponse($response, $request);
    }

    protected function prepareResponse(Response $response, Request $request)
    {
        $response->setContent(str_ireplace(array(
            $request->getScheme() . '://' . $this->configuration->getBackend(),
            $request->getScheme() . '://www.' . $this->configuration->getBackend(),
        ),
        array(
            $request->getSchemeAndHttpHost(),
            $request->getSchemeAndHttpHost(),
        ), $response->getContent()));

        return $response;
    }
}