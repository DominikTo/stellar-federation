<?php

namespace Dominik\Stellar\Federation\Exception;


class NoSuchDomain extends NotFound {

    public function getHTTPCode() {

        return 404;

    }

    public function getError() {

        return 'noSuchDomain';

    }

}
