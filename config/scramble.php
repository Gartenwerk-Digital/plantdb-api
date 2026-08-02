<?php

declare(strict_types=1);

use Dedoc\Scramble\Http\Middleware\RestrictedDocsAccess;

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
     */
    'api_path' => 'api',

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain' => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info' => [
        /*
         * API version.
         */
        'version' => env('API_VERSION', '0.1.0'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => <<<'MD'
            PlantDB API — central community-curated plant database for garden management applications.

            ## Response envelope

            **Success (single resource)**
            ```json
            { "data": { ... } }
            ```

            **Success (paginated collection)**
            ```json
            {
              "data": [ ... ],
              "meta": { "pagination": { "current_page": 1, "per_page": 20, "total": 100, "last_page": 5 } },
              "links": { "first": "...", "last": "...", "prev": null, "next": "..." }
            }
            ```

            Success responses may also carry a human-readable `meta.message` (e.g. after login).

            ## Error envelope

            Every non-2xx response uses the unified shape:
            ```json
            { "error": { "code": "validation_failed", "message": "...", "details": { "email": ["..."] } } }
            ```

            `code` is a stable snake_case slug clients can switch on. `details` is only present for `422 validation_failed`.

            | Status | `error.code`         | When                                        |
            | ------ | -------------------- | ------------------------------------------- |
            | 400    | `bad_request`        | Malformed request or business rule refusal. |
            | 401    | `unauthenticated`    | Missing / invalid bearer token.             |
            | 403    | `forbidden`          | Authenticated but not permitted.            |
            | 404    | `not_found`          | Unknown route or unpublished resource.      |
            | 422    | `validation_failed`  | Payload failed validation.                  |
            | 429    | `too_many_requests`  | Rate limit exceeded.                        |
            | 500    | `server_error`       | Unexpected server error.                    |

            Every v1 response also carries the `X-API-Version: v1` header.

            ## Rate limits

            Requests are rate-limited **per API key** (Sanctum token id) on a rolling 24-hour window. The daily quota depends on the user's tier:

            | Tier         | Requests / 24 h |
            | ------------ | --------------- |
            | `free`       | 1 000           |
            | `pro`        | 50 000          |
            | `enterprise` | unlimited       |

            Unauthenticated public reads are limited to **100 requests / 24 h per IP**. Login / register share a separate 5/min brute-force limiter.

            Every rate-limited response carries:

            - `X-RateLimit-Limit` — the tier's ceiling for the current window
            - `X-RateLimit-Remaining` — requests left before the next reset

            When the quota is exhausted the API returns `429 too_many_requests` and adds `Retry-After` (seconds until the counter resets).
            MD,
    ],

    /*
     * Customize Stoplight Elements UI
     */
    'ui' => [
        /*
         * Define the title of the documentation's website. App name is used when this config is `null`.
         */
        'title' => 'PlantDB API',

        /*
         * Define the theme of the documentation. Available options are `light`, `dark`, and `system`.
         */
        'theme' => 'light',

        /*
         * Hide the `Try It` feature. Enabled by default.
         */
        'hide_try_it' => false,

        /*
         * Hide the schemas in the Table of Contents. Enabled by default.
         */
        'hide_schemas' => false,

        /*
         * URL to an image that displays as a small square logo next to the title, above the table of contents.
         */
        'logo' => '',

        /*
         * Use to fetch the credential policy for the Try It feature. Options are: omit, include (default), and same-origin
         */
        'try_it_credentials_policy' => 'include',

        /*
         * There are three layouts for Elements:
         * - sidebar - (Elements default) Three-column design with a sidebar that can be resized.
         * - responsive - Like sidebar, except at small screen sizes it collapses the sidebar into a drawer that can be toggled open.
         * - stacked - Everything in a single column, making integrations with existing websites that have their own sidebar or other columns already.
         */
        'layout' => 'responsive',
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     *
     * Example of non-default config (final URLs are generated using Laravel `url` helper):
     *
     * ```php
     * 'servers' => [
     *     'Live' => 'api',
     *     'Prod' => 'https://scramble.dedoc.co/api',
     * ],
     * ```
     */
    'servers' => null,

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     *    @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    /**
     * Determines how Scramble stores the names of enum cases.
     * Available options:
     * - 'names' – Case names are stored in the `x-enumNames` enum schema extension.
     * - 'varnames' - Case names are stored in the `x-enum-varnames` enum schema extension.
     * - false - Case names are not stored.
     */
    'enum_cases_names_strategy' => false,

    /**
     * When Scramble encounters deep objects in query parameters, it flattens the parameters so the generated
     * OpenAPI document correctly describes the API. Flattening deep query parameters is relevant until
     * OpenAPI 3.2 is released and query string structure can be described properly.
     *
     * For example, this nested validation rule describes the object with `bar` property:
     * `['foo.bar' => ['required', 'int']]`.
     *
     * When `flatten_deep_query_parameters` is `true`, Scramble will document the parameter like so:
     * `{"name":"foo[bar]", "schema":{"type":"int"}, "required":true}`.
     *
     * When `flatten_deep_query_parameters` is `false`, Scramble will document the parameter like so:
     *  `{"name":"foo", "schema": {"type":"object", "properties":{"bar":{"type": "int"}}, "required": ["bar"]}, "required":true}`.
     */
    'flatten_deep_query_parameters' => true,

    'middleware' => [
        'web',
        RestrictedDocsAccess::class,
    ],

    'extensions' => [],
];
