<?php

namespace BwtTeam\LaravelAPI\Exceptions;

use BwtTeam\LaravelAPI\Response\ApiResponse;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    const TYPE_WEB = 1;
    const TYPE_API = 2;

    /**
     * @var int Response type
     */
    protected $type = self::TYPE_WEB;

    /**
     * Get the type of response
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of response
     *
     * @param int $type Response type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Render an exception into a response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, \Throwable $e)
    {
        if ($this->type == self::TYPE_API) {
            $e = $this->prepareException($e);
            $statusCode = $this->recognizeStatusCode($e);
            return new ApiResponse($e, $statusCode);
        }
        return parent::render($request, $e);
    }

    /**
     * Recognition response code from Exception
     *
     * @param Exception $e
     *
     * @return int
     */
    protected function recognizeStatusCode(\Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        } else if ($e instanceof AuthenticationException) {
            return 401;
        } else if ($e instanceof ValidationException) {
            return 422;
        }

        $e = FlattenException::createFromThrowable($e);

        return $e->getStatusCode();
    }
}
