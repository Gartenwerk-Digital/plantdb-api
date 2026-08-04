<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class GbifClient
{
    private const string BASE_URL = 'https://api.gbif.org/v1';

    private const int PLANTAE_KINGDOM_KEY = 6;

    /**
     * @return array<string, mixed>
     */
    public function searchPlants(int $limit, int $offset): array
    {
        $payload = $this->request()
            ->get('/species/search', [
                'higherTaxonKey' => self::PLANTAE_KINGDOM_KEY,
                'rank' => 'SPECIES',
                'status' => 'ACCEPTED',
                'limit' => $limit,
                'offset' => $offset,
            ])
            ->throw()
            ->json();

        if (! is_array($payload)) {
            return [];
        }

        /** @var array<string, mixed> $payload */
        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function vernacularNames(int $taxonKey): array
    {
        $payload = $this->request()
            ->get(sprintf('/species/%d/vernacularNames', $taxonKey))
            ->throw()
            ->json();

        if (! is_array($payload)) {
            return [];
        }

        $results = $payload['results'] ?? [];
        if (! is_array($results)) {
            return [];
        }

        $rows = [];
        foreach ($results as $row) {
            if (! is_array($row)) {
                continue;
            }

            /** @var array<string, mixed> $row */
            $rows[] = $row;
        }

        return $rows;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->timeout(30)
            ->retry(3, 200, throw: false);
    }
}
