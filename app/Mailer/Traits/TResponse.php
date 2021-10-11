<?php


namespace App\Mailer\Traits;


trait TResponse
{
    public function failureResult($message): \Illuminate\Http\JsonResponse
    {
        $data = [
            'status' => 'failure',
            'data'   => $message,
        ];
        return \response()->json($data, 400);
    }

    public function successResult($message = ""): \Illuminate\Http\JsonResponse
    {
        $data = [
            'status' => 'success',
            'data'   => $message,
        ];
        return \response()->json($data);

    }
}
