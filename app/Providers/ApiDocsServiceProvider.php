<?php

declare(strict_types=1);

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Illuminate\Support\ServiceProvider;

final class ApiDocsServiceProvider extends ServiceProvider
{
    private const string ERROR_SCHEMA_NAME = 'ApiError';

    /**
     * Global error responses added to every documented operation.
     *
     * @var array<int, array{code: string, description: string}>
     */
    private const array ERROR_RESPONSES = [
        400 => ['code' => 'bad_request', 'description' => 'Malformed request.'],
        401 => ['code' => 'unauthenticated', 'description' => 'Missing or invalid credentials.'],
        403 => ['code' => 'forbidden', 'description' => 'Authenticated but not allowed to perform this action.'],
        404 => ['code' => 'not_found', 'description' => 'Resource not found.'],
        422 => ['code' => 'validation_failed', 'description' => 'Request payload failed validation.'],
        429 => ['code' => 'too_many_requests', 'description' => 'Rate limit exceeded.'],
        500 => ['code' => 'server_error', 'description' => 'Unexpected server error.'],
    ];

    public function boot(): void
    {
        if (! class_exists(Scramble::class)) {
            return;
        }

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi): void {
            $errorRef = $this->registerErrorSchema($openApi);
            $this->attachErrorResponses($openApi, $errorRef);
        });
    }

    private function registerErrorSchema(OpenApi $openApi): Reference
    {
        $codeProp = new StringType();
        $codeProp->setDescription('Stable machine-readable slug (e.g. "not_found", "validation_failed").');

        $messageProp = new StringType();
        $messageProp->setDescription('Human-readable error message.');

        $detailsProp = new ObjectType();
        $detailsProp->additionalProperties(new ArrayType());
        $detailsProp->setDescription('Optional per-field error details (populated on validation errors).');

        $errorObject = new ObjectType();
        $errorObject->addProperty('code', $codeProp);
        $errorObject->addProperty('message', $messageProp);
        $errorObject->addProperty('details', $detailsProp);
        $errorObject->setRequired(['code', 'message']);

        $envelope = new ObjectType();
        $envelope->addProperty('error', $errorObject);
        $envelope->setRequired(['error']);

        $schema = new Schema();
        $schema->type = $envelope;

        return $openApi->components->addSchema(self::ERROR_SCHEMA_NAME, $schema);
    }

    private function attachErrorResponses(OpenApi $openApi, Reference $errorRef): void
    {
        foreach ($openApi->paths as $path) {
            foreach ($path->operations as $operation) {
                $existingCodes = $this->collectExistingStatuses($operation->responses ?? []);

                foreach (self::ERROR_RESPONSES as $status => $meta) {
                    if (in_array($status, $existingCodes, true)) {
                        continue;
                    }

                    $response = new Response($status);
                    $response->setDescription(sprintf('%s (error.code: `%s`)', $meta['description'], $meta['code']));
                    $response->setContent('application/json', $errorRef);

                    $operation->addResponse($response);
                }
            }
        }
    }

    /**
     * @param  array<array-key, Response|Reference>  $responses
     * @return array<int, int>
     */
    private function collectExistingStatuses(array $responses): array
    {
        $codes = [];

        foreach ($responses as $response) {
            $code = $response instanceof Response ? $response->code : null;

            if ($code === null) {
                continue;
            }

            $codes[] = (int) $code;
        }

        return $codes;
    }
}
