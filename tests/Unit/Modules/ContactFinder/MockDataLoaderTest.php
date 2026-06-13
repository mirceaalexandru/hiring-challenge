<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\ContactFinder;

use App\Modules\ContactFinder\Support\MockDataLoader;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class MockDataLoaderTest extends TestCase
{
    public function test_throws_when_file_is_missing(): void
    {
        $this->expectException(RuntimeException::class);
        MockDataLoader::load('/no/such/fixture.json');
    }

    public function test_throws_on_invalid_json(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mock').'.json';
        file_put_contents($path, '{ not valid json');

        try {
            $this->expectException(RuntimeException::class);
            MockDataLoader::load($path);
        } finally {
            @unlink($path);
        }
    }

    public function test_loads_valid_json_into_array(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mock').'.json';
        file_put_contents($path, '{"Acme":{"registry":{"name":"Jane"}}}');

        try {
            $data = MockDataLoader::load($path);
            $this->assertArrayHasKey('Acme', $data);
        } finally {
            @unlink($path);
        }
    }
}
