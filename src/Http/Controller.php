<?php

namespace Jinya\Router\Http;

use Jinya\Router\Templates\Engine;
use JsonException;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * A class that can be used as a base controller for classes marked with the Controller attribute.
 */
abstract class Controller
{
    public const HTTP_CONTINUE = 100;
    public const HTTP_SWITCHING_PROTOCOLS = 101;
    public const HTTP_PROCESSING = 102;
    public const HTTP_EARLY_HINTS = 103;
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_ACCEPTED = 202;
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const HTTP_NO_CONTENT = 204;
    public const HTTP_RESET_CONTENT = 205;
    public const HTTP_PARTIAL_CONTENT = 206;
    public const HTTP_MULTI_STATUS = 207;
    public const HTTP_ALREADY_REPORTED = 208;
    public const HTTP_IM_USED = 226;
    public const HTTP_MULTIPLE_CHOICES = 300;
    public const HTTP_MOVED_PERMANENTLY = 301;
    public const HTTP_FOUND = 302;
    public const HTTP_SEE_OTHER = 303;
    public const HTTP_NOT_MODIFIED = 304;
    public const HTTP_USE_PROXY = 305;
    public const HTTP_RESERVED = 306;
    public const HTTP_TEMPORARY_REDIRECT = 307;
    public const HTTP_PERMANENTLY_REDIRECT = 308;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_PAYMENT_REQUIRED = 402;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_METHOD_NOT_ALLOWED = 405;
    public const HTTP_NOT_ACCEPTABLE = 406;
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const HTTP_REQUEST_TIMEOUT = 408;
    public const HTTP_CONFLICT = 409;
    public const HTTP_GONE = 410;
    public const HTTP_LENGTH_REQUIRED = 411;
    public const HTTP_PRECONDITION_FAILED = 412;
    public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    public const HTTP_REQUEST_URI_TOO_LONG = 414;
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const HTTP_EXPECTATION_FAILED = 417;
    public const HTTP_I_AM_A_TEAPOT = 418;
    public const HTTP_MISDIRECTED_REQUEST = 421;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_LOCKED = 423;
    public const HTTP_FAILED_DEPENDENCY = 424;
    public const HTTP_TOO_EARLY = 425;
    public const HTTP_UPGRADE_REQUIRED = 426;
    public const HTTP_PRECONDITION_REQUIRED = 428;
    public const HTTP_TOO_MANY_REQUESTS = 429;
    public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;
    public const HTTP_NOT_IMPLEMENTED = 501;
    public const HTTP_BAD_GATEWAY = 502;
    public const HTTP_SERVICE_UNAVAILABLE = 503;
    public const HTTP_GATEWAY_TIMEOUT = 504;
    public const HTTP_VERSION_NOT_SUPPORTED = 505;
    public const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;
    public const HTTP_INSUFFICIENT_STORAGE = 507;
    public const HTTP_LOOP_DETECTED = 508;
    public const HTTP_NOT_EXTENDED = 510;
    public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

    /** @var ServerRequestInterface $request The current request */
    public ServerRequestInterface $request;
    /** @var array<string, mixed>|object|null $body The parsed body of the current request */
    public array|object|null $body;
    /** @var Engine|null $templateEngine The template engine of the current request */
    public Engine|null $templateEngine = null;

    /**
     * Gets the header value for the given header name
     *
     * @param string $name The name of the header
     * @return string
     */
    public function getHeader(string $name): string
    {
        return $this->request->getHeaderLine($name);
    }

    /**
     * Gets the query parameter value for the given query parameter name
     *
     * @param string $name The name of the query parameter
     * @param string $default The default value if the query parameter is not found in the request
     * @return string
     */
    public function getQueryParameter(string $name, string $default = ''): string
    {
        return $this->request->getQueryParams()[$name] ?? $default;
    }

    /**
     * Renders the given template with the given data and returns the response. This method can only be used if the router was configured with a template language
     *
     * @param string $template The template to render, this has to be a path usable by the template engine
     * @param mixed|null $data The data to pass to the temnplate engine
     * @param int $status The response status, defaults to 200
     * @return ResponseInterface
     */
    public function render(string $template, mixed $data = null, int $status = self::HTTP_OK): ResponseInterface
    {
        if ($this->templateEngine === null) {
            throw new RuntimeException('No template engine provided');
        }

        return new Response($status, headers: ['Content-Type' => 'text/html'], body: $this->templateEngine->render(
            $template,
            $data
        ));
    }

    /**
     * Sends the given file to as response
     *
     * @param string $filename The file to send
     * @param int $status The response status defaults to 200. If the file doesn't exist or can't be opened, 404 is returned
     * @return ResponseInterface
     * @throws JsonException
     */
    public function file(string $filename, int $status = self::HTTP_OK): ResponseInterface
    {
        if (!file_exists($filename)) {
            return $this->notFound();
        }

        $file = fopen($filename, "rb+");
        if (!$file) {
            return $this->notFound();
        }

        return new Response($status, headers: ['Content-Type' => mime_content_type($filename)], body: Stream::create(
            $file
        ));
    }

    /**
     * Generates a 404 not found response
     *
     * @param mixed|null $data The data to include in the response body
     * @return ResponseInterface
     * @throws JsonException
     */
    public function notFound(mixed $data = null): ResponseInterface
    {
        if ($data) {
            return $this->json($data, status: self::HTTP_NOT_FOUND);
        }

        return new Response(self::HTTP_NOT_FOUND);
    }

    /**
     * Generates a response with the given status code and encodes the data as JSON in the response body
     *
     * @param mixed $data The data to include in the response body
     * @param int $status The status to send, defaults to 200
     * @return ResponseInterface
     * @throws JsonException
     */
    public function json(mixed $data, int $status = self::HTTP_OK): ResponseInterface
    {
        return new Response($status, headers: ['Content-Type' => 'application/json'], body: json_encode(
            $data,
            JSON_THROW_ON_ERROR
        ));
    }

    /**
     * Generates a 204 no content response
     *
     * @return ResponseInterface
     */
    public function noContent(): ResponseInterface
    {
        return new Response(self::HTTP_NO_CONTENT);
    }
}
