Proxy
=====

A simple php proxy.

## Usage
```php
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$configuration = new \Proxy\Configuration();
$configuration->setBackend('nousefreak.be');

$proxy = new \Proxy\Proxy();
$proxy->setConfiguration($configuration);

$response = $proxy->proxy($request);
$response->send();
```