<?php

namespace Dominik\Stellar\Federation\Exception;


class NoSuchUser extends NotFound {

    public function getHTTPCode() {

        return 404;

    }

    public function getError() {

        return 'noSuchUser';

    }

}
