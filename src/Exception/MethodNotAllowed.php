<?php

namespace Dominik\Stellar\Federation\Exception;


class MethodNotAllowed extends \Dominik\Stellar\Federation\Exception {

    public function getHTTPCode() {

        return 405;

    }

    public function getError() {

        return $this->getHTTPcode() . ' - '. 'Method Not Allowed';

    }

    /*
     * @TODO The response MUST include an Allow header containing a list of valid methods for the requested resource.
     */

}
