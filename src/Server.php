<?php

/*
 * Specification: https://wiki.stellar.org/Federation
 */
namespace Dominik\Stellar\Federation;

use Dominik\Stellar\Federation\Exception\MethodNotAllowed;
use Dominik\Stellar\Federation\Exception\InvalidParams;
use Dominik\Stellar\Federation\Exception\NoSuchDomain;
use Dominik\Stellar\Federation\Exception\NoSuchUser;
use Sabre\HTTP;


class Server {

    private $request;
    private $response;

    private $allowedTypes = [
        'federation',
    ];

    private $resolver;

    public function __construct($resolver) {

        $this->resolver = $resolver;

    }

    private function federationHandler($user, $domain) {

        $result = $this->getUser($user, $domain);

        return [
            'result' => 'success',
            'federation_json' => [
                'type' => 'federation_record',
                'domain' => $result['domain'],
                'user' => $result['user'],
                'destination_address' => $result['address'],
            ]
        ];

    }

    /*
     * @TODO Add support for noSupported and unavailable exception.
     *
     * noSupported
     *  Look up by tag is not supported.
     * unavailable
     *  Service is temporarily unavailable.
     */
    function returnException($e) {

        $httpCode = $e->getHTTPcode();

        $response = [
            'error' => $e->getError(),
            'error_message' => $e->getMessage(),
            'result' => 'error',
            'request' => $this->request->getQueryParameters(),
        ];

        $this->response->setStatus($httpCode);
        $this->response->setHeader('Access-Control-Allow-Origin', '*');
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setBody(json_encode($response, JSON_PRETTY_PRINT));
        HTTP\Sapi::sendResponse($this->response);

    }

    /*
     * @TODO Make this properly plug- and extendable.
     */
    private function getUser($user, $domain) {

        if(!isset($this->resolver[$domain])) {
            throw new NoSuchDomain('The supplied domain is not served here.');
        }

        if(!isset($this->resolver[$domain][$user])) {
            throw new NoSuchUser('The supplied user was not found.');
        }

        return [
            'user' => $user,
            'domain' => $domain,
            'address' => $this->resolver[$domain][$user],
        ];

    }

    public function exec() {

        if (!$this->request) {
            $this->request = HTTP\Sapi::getRequest();
        }

        if (!$this->response) {
            $this->response = new HTTP\Response();
        }

        try {

            if ($this->request->getMethod() !== 'GET') {
                throw new MethodNotAllowed('HTTP method must be GET');
            }

            $getVars = $this->request->getQueryParameters();

            $type = isset($getVars['type']) ? $getVars['type'] : false;
            if (!$type) {
                    throw new InvalidParams('No type given');
            }

            if (!in_array($type, $this->allowedTypes)) {
                throw new InvalidParams('Unknown type: ' . $type);
            }

            $user = isset($getVars['user']) ? $getVars['user'] : false;
            if (!$user) {
                    throw new InvalidParams('No user given');
            }

            $domain = isset($getVars['domain']) ? $getVars['domain'] : false;
            if (!$domain) {
                    throw new InvalidParams('No domain given');
            }

            $result = call_user_func([$this, $type.'Handler'], $user, $domain);

            $this->response->setStatus(200);
            $this->response->setHeader('Access-Control-Allow-Origin', '*');
            $this->response->setHeader('Content-Type', 'application/json');
            $this->response->setBody(json_encode($result, JSON_PRETTY_PRINT));
            HTTP\Sapi::sendResponse($this->response);

        } catch(\Exception $e) {

            $this->returnException($e);

        }

    }

}
