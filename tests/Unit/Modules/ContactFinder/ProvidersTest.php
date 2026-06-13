<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\ContactFinder;

use App\Modules\ContactFinder\Providers\EnrichmentProvider;
use App\Modules\ContactFinder\Providers\ListingProvider;
use App\Modules\ContactFinder\Providers\RegistryProvider;
use PHPUnit\Framework\TestCase;

/**
 * Direct coverage of each provider's "found nothing" contract — the absent /
 * null-field cases the resolver depends on but never exercises explicitly.
 */
final class ProvidersTest extends TestCase
{
    public function test_registry_returns_null_when_company_absent(): void
    {
        $provider = new RegistryProvider([]);

        $this->assertNull($provider->lookup('Nobody Inc'));
        $this->assertSame('registry', $provider->key());
    }

    public function test_registry_returns_null_when_name_and_role_empty(): void
    {
        $provider = new RegistryProvider([
            'Acme' => ['registry' => ['name' => null, 'role' => '', 'source_url' => 'mock://x']],
        ]);

        $this->assertNull($provider->lookup('Acme'));
    }

    public function test_registry_maps_name_and_role(): void
    {
        $provider = new RegistryProvider([
            'Acme' => ['registry' => ['name' => 'Jane Doe', 'role' => 'Owner', 'source_url' => 'mock://r']],
        ]);

        $result = $provider->lookup('Acme');
        $this->assertNotNull($result);
        $this->assertSame('Jane Doe', $result->name);
        $this->assertSame('Owner', $result->role);
        $this->assertSame('mock://r', $result->sourceUrl);
        $this->assertNull($result->email);
    }

    public function test_listing_returns_phone_only_when_name_missing(): void
    {
        $provider = new ListingProvider([
            'Acme' => ['listing' => ['name' => null, 'phone' => '+1-555-0100', 'source_url' => 'mock://l']],
        ]);

        $result = $provider->lookup('Acme');
        $this->assertNotNull($result);
        $this->assertFalse($result->hasName());
        $this->assertSame('+1-555-0100', $result->phone);
    }

    public function test_listing_returns_null_when_name_and_phone_empty(): void
    {
        $provider = new ListingProvider([
            'Acme' => ['listing' => ['name' => null, 'phone' => null]],
        ]);

        $this->assertNull($provider->lookup('Acme'));
    }

    public function test_enrichment_parses_confidence_as_int(): void
    {
        $provider = new EnrichmentProvider([
            'Acme' => ['enrichment' => ['email' => 'a@b.com', 'phone' => null, 'provider_confidence' => 73, 'source_url' => 'mock://e']],
        ]);

        $result = $provider->lookup('Acme');
        $this->assertNotNull($result);
        $this->assertSame(73, $result->providerConfidence);
        $this->assertSame('a@b.com', $result->email);
    }

    public function test_enrichment_returns_null_when_no_contact(): void
    {
        $provider = new EnrichmentProvider([
            'Acme' => ['enrichment' => ['email' => null, 'phone' => null, 'provider_confidence' => 90]],
        ]);

        $this->assertNull($provider->lookup('Acme'));
    }
}
