<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Exception;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Success Response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $title =null, string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'title' => $title,
            'message' => $message
        ], $code);
    }

    protected function verifiedResponse(string $title =null, string $message = null, int $id=null, bool $verified, int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'verified' => $verified,
            'title' => $title,
            'message' => $message,
            'id' => $id,
        ], $code);
    }

    /**
     * Error Response
     *
     * @param string|null $message
     * @param int $code
     * @param mixed $data
     * @return JsonResponse
     */
    protected function errorResponse(string $message = null, int $code = Response::HTTP_INTERNAL_SERVER_ERROR, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    /**
     * Handle Exceptions
     *
     * @param Exception $e
     * @return JsonResponse
     */
    protected function handleException(Exception $e): JsonResponse
    {
        // Error de registro no encontrado
        if ($e instanceof ModelNotFoundException) {
            return $this->errorResponse(
                'El registro solicitado no existe.',
                Response::HTTP_NOT_FOUND
            );
        }

        // Errores de base de datos
        if ($e instanceof QueryException) {
            $mysqlErrorCode = $e->errorInfo[1] ?? null;
            // Error de foreign Key
            if ($mysqlErrorCode === 1451) {
                return $this->errorResponse(
                    'El registro no puede ser eliminado porque está siendo utilizado.',
                    Response::HTTP_CONFLICT
                );
            }

            // Error de llave única
            if ($e->getCode() === '23000') {
                return $this->errorResponse(
                    'Ya existe un registro con estos datos.',
                    Response::HTTP_CONFLICT
                );
            }

            return $this->errorResponse(
                'Error en la base de datos.',
                //$e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Para otros errores en producción
        if (app()->environment('production')) {
            return $this->errorResponse(
                'Ha ocurrido un error inesperado.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Para desarrollo, mostramos más detalles
        return $this->errorResponse(
            $e->getMessage(),
            Response::HTTP_INTERNAL_SERVER_ERROR,
            [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]
        );
    }

    /**
     * Created Response
     *
     * @param mixed $data
     * @param string|null $message
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $title = null, string $message = null): JsonResponse
    {
        return $this->successResponse($data, $title, $message, Response::HTTP_CREATED);
    }

    /**
     * No Content Response
     *
     * @return JsonResponse
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
