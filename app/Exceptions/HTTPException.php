<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HTTPException extends Exception
{
    public static function NOT_FOUND($message = 'Resource not Found')
    {
        return new self($message, Response::HTTP_NOT_FOUND);
    }

    public static function BAD_REQUEST($message = 'Bad Request')
    {
        return new self($message, Response::HTTP_BAD_REQUEST);
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

    public static function CONSTRAINT_VIOLATION($message = 'Constraint Violation')
    {
        return new self($message, Response::HTTP_BAD_REQUEST);
    }

    public function render($request): JsonResponse {
        return response()->json([
            'status' => $this->getCode(),
            'message' => $this->getMessage()
        ], $this->getCode());
    }
}
