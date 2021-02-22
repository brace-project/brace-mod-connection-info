<?php


namespace Brace\Connection;


use Brace\Core\BraceApp;
use Brace\Core\BraceModule;
use Brace\Core\Helper\IPSet;
use Phore\Di\Container\Producer\DiService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class ConnectionInfoModule implements BraceModule
{

    public function register(BraceApp $app)
    {
        $app->define("connectionInfo", new DiService(function (ServerRequestInterface $request) {
            $_remote_addr = null;
            $_request_scheme = "http";

            // Detect Remote Addr
            $ipSet = new IPSet(IPSet::PRIVATE_NETS);
            if ( ! $ipSet->match($request->getServerParams()["REMOTE_ADDR"])) {
                $_remote_addr = $request->getServerParams()["REMOTE_ADDR"];
            } else if (isset ($request->getHeaders()["X_FORWARDED_FOR"])) {
                $forwardsRev = array_reverse($forwards = explode(",", $request->getHeaders()["HTTP_X_FORWARDED_FOR"][0]));
                for ($i=0; $i<count ($forwardsRev); $i++) {
                    if ( ! $ipSet->match(trim ($forwardsRev[$i]))) {
                        return $forwardsRev[$i]; // Return first non-private IP from last to first
                    }
                }
                $_remote_addr = $forwards[0];
            } else {
                $_remote_addr = $request->getServerParams()["REMOTE_ADDR"];
            }

            // Determine if connection was originally ssl secured
            if (isset($request->getHeaders()["X_FORWARDED_FOR"]) && strtolower($request->getHeaders()["X_FORWARDED_FOR"][0]) === "https") {
                $_request_scheme = "https";
            }
            if (!empty($request->getServerParams()['HTTPS']) && $request->getServerParams()['HTTPS'] !== 'off') {
                $data["requestScheme"] = "https";
            }
            return new ConnectionInfo(
                $_remote_addr,
                $request->getServerParams()["REMOTE_ADDR"],
                $_request_scheme
            );
        }));
    }
}