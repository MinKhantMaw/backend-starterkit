<?php

use App\Support\ApiResponse;

test('success responses use the standard envelope', function () {
    $response = ApiResponse::success('Done', ['id' => 1]);
    expect($response->getStatusCode())->toBe(200)
        ->and($response->getData(true))->toMatchArray([
            'success' => true,
            'message' => 'Done',
            'data' => ['id' => 1],
        ]);
});
