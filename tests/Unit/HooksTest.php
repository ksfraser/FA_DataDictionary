<?php

declare(strict_types=1);

namespace Ksfraser\FaDataDictionaryLegacy\Tests;

use PHPUnit\Framework\TestCase;

final class HooksTest extends TestCase
{
    public function testInstallAccessReturnsAreasAndSections(): void
    {
        require_once __DIR__ . '/../../hooks.php';

        $h = new \hooks_ksf_data_dictionary();
        [$areas, $sections] = $h->install_access();

        $this->assertIsArray($areas);
        $this->assertIsArray($sections);
        $this->assertArrayHasKey('SA_ksf_data_dictionary', $areas);
        $this->assertNotEmpty($sections);
    }

    public function testInstallOptionsAddsMenuForStockApp(): void
    {
        require_once __DIR__ . '/../../hooks.php';

        $added = [];
        $app = new class($added) {
            public string $id = 'stock';
            private array $added;
            public function __construct(array &$added) { $this->added = &$added; }
            public function add_rapp_function(int $index, string $label, string $path, string $perm): void
            {
                $this->added[] = [$index, $label, $path, $perm];
            }
        };

        $GLOBALS['path_to_root'] = '/root';

        $h = new \hooks_ksf_data_dictionary();
        $h->install_options($app);

        $this->assertCount(1, $added);
        $this->assertSame(2, $added[0][0]);
        $this->assertSame('ksf_data_dictionary', $added[0][1]);
        $this->assertStringContainsString('/modules/ksf_data_dictionary/ksf_data_dictionary.php', $added[0][2]);
        $this->assertSame('SA_ksf_data_dictionary', $added[0][3]);
    }
}
