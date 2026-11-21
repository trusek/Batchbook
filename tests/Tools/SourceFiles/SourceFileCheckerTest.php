<?php

namespace App\Tests\Tools\SourceFiles;

use App\Tools\SourceFiles\SourceFileChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class SourceFileCheckerTest extends TestCase
{
    private const BOM = "\xEF\xBB\xBF";

    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir().'/source-file-checker-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->root);
    }

    public function testReportsByteOrderMark(): void
    {
        $this->givenFile('src/WithBom.php', self::BOM."<?php\n");
        $this->givenFile('templates/with_bom.html.twig', self::BOM."<p></p>\n");
        $this->givenFile('config/with_bom.yaml', self::BOM."a: 1\n");
        $this->givenFile('src/Clean.php', "<?php\n");
        $this->givenFile('vendor/lib/WithBom.php', self::BOM."<?php\n");

        self::assertSame([
            'config/with_bom.yaml: BOM na początku pliku.',
            'src/WithBom.php: BOM na początku pliku.',
            'templates/with_bom.html.twig: BOM na początku pliku.',
        ], (new SourceFileChecker())->check($this->root));
    }

    public function testReportsTwigDateFormatWithSingleBackslash(): void
    {
        $this->givenFile('templates/entry.html.twig', <<<'TWIG'
            <time datetime="{{ entry.occurredAt|date('Y-m-d\\TH:i') }}">
                {{ entry.occurredAt|date('Y-m-d\TH:i') }}
                {{ entry.occurredAt|date("Y-m-d\TH:i", 'Europe/Warsaw') }}
                {{ date(entry.occurredAt)|date('d.m.Y H:i') }}
            </time>
            TWIG);

        self::assertSame([
            'templates/entry.html.twig:2: w formacie date() pojedynczy backslash znika; pisz \\\\T zamiast \\T.',
            'templates/entry.html.twig:3: w formacie date() pojedynczy backslash znika; pisz \\\\T zamiast \\T.',
        ], (new SourceFileChecker())->check($this->root));
    }

    private function givenFile(string $path, string $contents): void
    {
        (new Filesystem())->dumpFile($this->root.'/'.$path, $contents);
    }
}
