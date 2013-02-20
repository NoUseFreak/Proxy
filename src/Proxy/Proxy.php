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

        $gRequest = $client->$method($request->getRequestUri());

        if ($method == 'post') {
            $gRequest->addPostFields($_POST);
        }

        foreach ($request->cookies->all() as $name => $value) {
            $gRequest->addCookie($name, str_replace($request->getHost(), $this->configuration->getBackend(), $value));
        }

        $response = $gRequest->send();

        return $this->createResponse($response, $request);
    }

    protected function getClient(\Symfony\Component\HttpFoundation\Request $request)
    {
        return new \Guzzle\Http\Client('{scheme}://{host}' . ':' . $this->configuration->getBackendPort(), array(
             'scheme' => $request->getScheme(),
             'host' =>  $this->configuration->getBackend(),
             'redirect.disable' => true,
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
        $domains = array_merge(array($this->configuration->getBackend()), $this->configuration->getAliases());
        $response->setContent(str_ireplace(array_map(function($value) use ($request) {
            return $request->getScheme() . '://' . $value;
        }, $domains),
        $request->getSchemeAndHttpHost(), $response->getContent()));

        if ($response->headers->has('set-cookie')) {
            $response->headers->set('set-cookie', implode(';', array_map(function($item) use ($domains, $request) {
                if (strpos($item, 'domain') !== false) {
                    return str_replace($domains, $request->getHost(), $item);
                }
                return $item;
            }, explode(';', $response->headers->get('set-cookie')))));

        }

        return $response;
    }
}