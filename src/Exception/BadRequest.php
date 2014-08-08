<?php

namespace Dominik\Stellar\Federation\Exception;


class BadRequest extends \Dominik\Stellar\Federation\Exception {

    public function getHTTPCode() {

        return 400;

    }

    public function getError() {

        return $this->getHTTPcode() . ' - '. 'Bad Request';

    }

}
