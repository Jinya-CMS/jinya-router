<?php

namespace Jinya\Router\Attributes;

/**
 * Possible HTTP methods supported by the server
 */
enum HttpMethod
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
    case HEAD;
    case PATCH;
    case OPTIONS;
}
