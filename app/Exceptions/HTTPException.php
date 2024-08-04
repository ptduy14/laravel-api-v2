<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class HTTPException extends Exception
{
    public static function BAD_REQUEST($message = 'Bad Request')
    {
        return new self($message, Response::HTTP_BAD_REQUEST);
    }

    public static function NOT_FOUND($message = 'Not Found')
    {
        return new self($message, Response::HTTP_NOT_FOUND);
    }

    // Thêm các method khác nếu cần
    public static function UNAUTHORIZED($message = 'Unauthorized')
    {
        return new self($message, Response::HTTP_UNAUTHORIZED);
    }

    public static function FORBIDDEN($message = 'Forbidden')
    {
        return new self($message, Response::HTTP_FORBIDDEN);
    }

    public static function INTERNAL_SERVER_ERROR($message = 'Internal Server Error')
    {
        return new self($message, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
