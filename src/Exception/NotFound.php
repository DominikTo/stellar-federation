<?php

namespace Dominik\Stellar\Federation\Exception;


class NotFound extends \Dominik\Stellar\Federation\Exception {

    public function getHTTPCode() {

        return 404;

    }

    public function getError() {

        return $this->getHTTPcode() . ' - '. 'Not Found';

    }

}
