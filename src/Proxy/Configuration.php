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

class Configuration
{
    protected $backend;
    protected $backendPort = 80;
    protected $aliases = array();

    public function setBackend($backend)
    {
        $this->backend = $backend;
    }

    public function getBackend()
    {
        return $this->backend;
    }

    public function setBackendPort($backendPort)
    {
        $this->backendPort = $backendPort;
    }

    public function getBackendPort()
    {
        return $this->backendPort;
    }

    public function setAliases($aliases)
    {
        $this->aliases = $aliases;
    }

    public function getAliases()
    {
        return $this->aliases;
    }


}