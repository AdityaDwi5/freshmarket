<?php

if (!function_exists('successResponse')){
    function successResponse($data, $message = 'Success', $code = 200) {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data

        ], $code);
    }
}

if (!function_exists('errorResponse')){
    function errorResponse($data, $message = 'Error', $code = 500) {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data

        ], $code);
    }
}

if (!function_exists('notFoundResponse')){
    function notFoundResponse($message = 'Resoure not found') {
        return errorResponse($message, 404);
    }
}

if (!function_exists('badRequestResponse')){
    function badRequestResponse($message = 'Bad Request') {
        return errorResponse($message, 400);
    }
}