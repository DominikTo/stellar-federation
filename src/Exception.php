<?php

namespace Dominik\Stellar\Federation;


class Exception extends \Exception {

    public function getHTTPCode() {

        return 500;

    }

    public function getError() {

        return $this->getHTTPcode() . ' - '. 'Internal Server Error';

    }

}
