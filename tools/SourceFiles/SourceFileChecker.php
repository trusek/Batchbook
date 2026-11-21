<?php

namespace App\Tools\SourceFiles;

/**
 * Sprawdza pliki źródłowe projektu pod kątem błędów, których nie łapią lintery.
 *
 * - BOM na początku plików *.php, *.twig, *.yaml/*.yml (PHP z BOM nie działa).
 * - Twig: litera poprzedzona pojedynczym backslashem w formacie date(), np. date('Y-m-d\TH:i').
 */
final class SourceFileChecker
{
    private const BOM = "\xEF\xBB\xBF";

    private const EXTENSIONS = ['php', 'twig', 'yaml', 'yml'];

    /** Katalogi z kodem spoza projektu albo generowanym. */
    private const SKIPPED_DIRECTORIES = ['.git', 'vendor', 'var', 'node_modules'];

    /**
     * @return list<string> naruszenia „ścieżka: opis”, posortowane po ścieżce
     */
    public function check(string $root): array
    {
        $files = iterator_to_array($this->files($root));
        asort($files);
        $violations = [];

        foreach ($files as $path => $relativePath) {
            $contents = (string) file_get_contents($path);

            if (str_starts_with($contents, self::BOM)) {
                $violations[] = sprintf('%s: BOM na początku pliku.', $relativePath);
            }

            if (str_ends_with($relativePath, '.twig')) {
                foreach ($this->twigDateEscapes($contents) as [$line, $letter]) {
                    $violations[] = sprintf('%s:%d: w formacie date() pojedynczy backslash znika; pisz \\\\%s zamiast \\%3$s.', $relativePath, $line, $letter);
                }
            }
        }

        return $violations;
    }

    /**
     * Twig zjada pojedynczy backslash w literale, więc date('Y-m-d\TH:i')
     * wypisuje strefę zamiast litery T. Escape w formacie wymaga \\.
     *
     * @return list<array{int, string}> [numer linii, litera po backslashu]
     */
    private function twigDateEscapes(string $contents): array
    {
        preg_match_all(
            '/\bdate\(\s*([\'"])((?:(?!\1)[^\\\\]|\\\\.)*)\1/s',
            $contents,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
        );

        $escapes = [];
        foreach ($matches as $match) {
            // Pary „\x” od lewej: „\\” to escape backslasha, „\T” to escape zjedzony przez Twiga.
            preg_match_all('/\\\\(.)/s', $match[2][0], $pairs);
            foreach ($pairs[1] as $escaped) {
                if (ctype_alpha($escaped)) {
                    $escapes[] = [substr_count($contents, "\n", 0, $match[0][1]) + 1, $escaped];
                }
            }
        }

        return $escapes;
    }

    /**
     * @return iterable<string, string> pełna ścieżka => ścieżka względem $root
     */
    private function files(string $root): iterable
    {
        $filtered = new \RecursiveCallbackFilterIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            static fn (\SplFileInfo $file): bool => !$file->isDir() || !in_array($file->getFilename(), self::SKIPPED_DIRECTORIES, true),
        );

        /** @var \SplFileInfo $file */
        foreach (new \RecursiveIteratorIterator($filtered) as $file) {
            if (in_array($file->getExtension(), self::EXTENSIONS, true)) {
                $path = $file->getPathname();
                yield $path => str_replace('\\', '/', substr($path, strlen($root) + 1));
            }
        }
    }
}
