<?php

declare(strict_types=1);

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\Header;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\Parameter;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;
use Dedoc\Scramble\Support\Generator\Types\Type;
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
            $this->attachLocaleParameters($openApi);
            $this->attachContentLanguageHeader($openApi);
        });
    }

    private function attachLocaleParameters(OpenApi $openApi): void
    {
        /** @var list<string> $supported */
        $supported = config('i18n.supported');
        /** @var string $default */
        $default = config('i18n.default');

        foreach ($openApi->paths as $path) {
            foreach ($path->operations as $operation) {
                $existing = $this->collectParameterKeys($operation->parameters);

                if (! in_array('query:locale', $existing, true)) {
                    $localeSchema = $this->schemaFromType(
                        (new StringType())->enum($supported)
                    );

                    $localeParam = Parameter::make('locale', 'query')
                        ->setSchema($localeSchema)
                        ->description(sprintf(
                            'Overrides the `Accept-Language` header. Supported values: `%s`. An unsupported locale returns `400 unsupported_locale`.',
                            implode('`, `', $supported),
                        ));

                    $operation->addParameters([$localeParam]);
                }

                if (! in_array('header:Accept-Language', $existing, true)) {
                    $headerSchema = $this->schemaFromType(new StringType());

                    $headerParam = Parameter::make('Accept-Language', 'header')
                        ->setSchema($headerSchema);
                    $headerParam->example = $default;
                    $headerParam->description(sprintf(
                        'Standard content negotiation. Ignored when `?locale=` is set. Defaults to `%s`.',
                        $default,
                    ));

                    $operation->addParameters([$headerParam]);
                }
            }
        }
    }

    private function attachContentLanguageHeader(OpenApi $openApi): void
    {
        foreach ($openApi->paths as $path) {
            foreach ($path->operations as $operation) {
                foreach ($operation->responses ?? [] as $response) {
                    if (! $response instanceof Response) {
                        continue;
                    }

                    $status = (int) $response->code;
                    if ($status < 200) {
                        continue;
                    }

                    if ($status >= 300) {
                        continue;
                    }

                    if (array_key_exists('Content-Language', $response->headers)) {
                        continue;
                    }

                    $header = new Header(
                        description: 'Effective locale of the response, negotiated from `?locale=` or `Accept-Language`.',
                        schema: $this->schemaFromType(new StringType()),
                    );

                    $response->addHeader('Content-Language', $header);
                }
            }
        }
    }

    private function schemaFromType(Type $type): Schema
    {
        $schema = new Schema();
        $schema->type = $type;

        return $schema;
    }

    /**
     * @param  array<array-key, Parameter|Reference>  $parameters
     * @return array<int, string>
     */
    private function collectParameterKeys(array $parameters): array
    {
        $keys = [];

        foreach ($parameters as $parameter) {
            if (! $parameter instanceof Parameter) {
                continue;
            }

            $keys[] = $parameter->in.':'.$parameter->name;
        }

        return $keys;
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
