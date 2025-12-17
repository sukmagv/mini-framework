<?php

namespace Enums;

enum HttpStatus: int
{
    case OK = 200;
    case CREATED = 201;

    case BAD_REQUEST = 400;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;

    case INTERNAL_SERVER_ERROR = 500;
}