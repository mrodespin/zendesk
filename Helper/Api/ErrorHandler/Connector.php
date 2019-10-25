<?php

namespace Wagento\Zendesk\Helper\Api\ErrorHandler;

class Connector
{
    const XML_PATH_ERROR = 'zendesk/config/connection_error';

    const NO_ERROR = null;
    const EMPTY_FIELDS_ERROR = 1;
    const WORNG_FIELDS_VALUES_ERROR = 2;

    const ERRO_MSG = [
        self::EMPTY_FIELDS_ERROR => "One or more credential's fields is empty.",
        self::WORNG_FIELDS_VALUES_ERROR => "One or more credential's fields is incorrect.",
    ];
}