<?php 

if (!function_exists('customApiResponse')) {
    function customApiResponse($status, $data = [], $message = null,$code=200)
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $message
        ],$code);
    }
}