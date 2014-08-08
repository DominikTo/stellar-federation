<?php

namespace Dominik\Stellar\Federation\Exception;


class InvalidParams extends BadRequest {

    public function getHTTPCode() {

        return 400;

    }

    public function getError() {

        return 'invalidParams';

    }

}
