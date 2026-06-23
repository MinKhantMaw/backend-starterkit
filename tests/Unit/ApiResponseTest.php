<?php

namespace Tests\Unit;

use App\Helpers\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_responses_use_the_standard_envelope(): void
    {
        $response = ApiResponse::success('Done', ['id' => 1]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([
            'success' => true,
            'message' => 'Done',
            'data' => ['id' => 1],
            'errors' => null,
        ], $response->getData(true));
    }

    public function test_error_responses_use_the_standard_envelope(): void
    {
        $response = ApiResponse::error('Failed', ['email' => ['Required']], 422);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame([
            'success' => false,
            'message' => 'Failed',
            'data' => null,
            'errors' => ['email' => ['Required']],
        ], $response->getData(true));
    }
}
