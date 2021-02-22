<?php


namespace Brace\Connection;



use Brace\Connection\Helper\IPSet;

class ConnectionInfo
{

    public function __construct(
        private string $remoteAddr,
        private string $connectionRemoteAddr,
        private string $requestScheme
    ) {}

    /**
     * Return the Remote Ip Address. Takes into account
     * proxy information.
     *
     * @return string
     */
    public function getRemoteAddr() : string
    {
        $this->remoteAddr;
    }

    public function getConnectionRemoteAddr() : string
    {
        $this->connectionRemoteAddr;
    }

    public function getRequestScheme() : string
    {
        return $this->requestScheme;
    }

    public function isSsl() : bool
    {
        return $this->requestScheme === "https";
    }

    public function remoteAddrMatchCidr(array $cidr = []) : bool
    {
        $ipSet = new IPSet($cidr);
        return $ipSet->match($this->remoteAddr);
    }

}