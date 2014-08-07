<?php

/*
 * Specification: https://wiki.stellar.org/Federation
 */
namespace Dominik\Stellar\Federation;

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
        if(!$result) {
            throw new \UnexpectedValueException('User not found');
        }

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
     * @TODO proper exception handling instead of noSuchUser for everything.
     *
     * noSuchUser
     *  The supplied user was not found.
     * noSupported
     *  Look up by tag is not supported.
     * noSuchDomain
     *  The supplied domain is not served here.
     * invalidParams
     *  Missing or conflicting parameters.
     * unavailable
     *  Service is temporarily unavailable.
     */
    function returnException($e) {

        $httpCode = 404;

        $response = [
            'error' => 'noSuchUser',
            'error_message' => 'No user found',
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
            return false;
        }

        if(!isset($this->resolver[$domain][$user])) {
            return false;
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
                throw new \UnexpectedValueException('HTTP method must be GET');
            }

            $getVars = $this->request->getQueryParameters();

            $type = isset($getVars['type']) ? $getVars['type'] : false;
            if (!$type) {
                    throw new \UnexpectedValueException('No type given');
            }

            if (!in_array($type, $this->allowedTypes)) {
                throw new \UnexpectedValueException('Unknown type: ' . $type);
            }

            $user = isset($getVars['user']) ? $getVars['user'] : false;
            if (!$user) {
                    throw new \UnexpectedValueException('No user given');
            }

            $domain = isset($getVars['domain']) ? $getVars['domain'] : false;
            if (!isset($domain)) {
                    throw new \UnexpectedValueException('No domain given');
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
