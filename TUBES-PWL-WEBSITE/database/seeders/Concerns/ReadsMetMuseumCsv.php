<?php

namespace Database\Seeders\Concerns;

use App\Models\ArtWork;

/**
 * Shared CSV-reading utilities for all MetMuseum CSV seeders.
 *
 * Key behaviours guaranteed by this trait:
 * - BOM (UTF-8 EF BB BF) is stripped from every header column name.
 * - fgetcsv() is used throughout — never file() / explode("\n") — so
 *   multiline quoted fields are always handled correctly by the C parser.
 * - provenance_final.csv stores its real payload inside the FIRST semicolon-
 *   delimited field as a comma-delimited sub-record.  mapCsvRow() detects
 *   this and re-parses transparently.
 */
trait ReadsMetMuseumCsv
{
    // --------------------------------------------------------------------------
    // File helpers
    // --------------------------------------------------------------------------

    protected function csvPath(string $filename): string
    {
        return database_path('data/' . $filename);
    }

    /**
     * Open a CSV file, detect / apply the delimiter, read the header row,
     * and return the file handle ready for data rows.
     *
     * @return array{resource|null, array|null, string|null, array|null}
     *              [handle, headers, delimiter, meta]
     *              meta is non-null (with 'file_missing'=>true) on failure.
     */
    protected function readCsvRows(string $filename, ?string $forcedDelimiter = null): array
    {
        $path = $this->csvPath($filename);

        if (!is_file($path)) {
            return [null, null, null, ['file_missing' => true, 'path' => $path]];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [null, null, null, ['file_missing' => true, 'path' => $path]];
        }

        // Peek at the first line only to detect delimiter — then rewind.
        $headerLine = fgets($handle) ?: '';
        $delimiter  = $forcedDelimiter ?: $this->detectDelimiter($headerLine);

        rewind($handle);

        // Read headers via the same fgetcsv() that data rows use.
        $rawHeaders = fgetcsv($handle, 0, $delimiter, '"', '\\') ?: [];
        $headers    = $this->stripBomFromHeaders($rawHeaders);

        return [$handle, $headers, $delimiter, null];
    }

    // --------------------------------------------------------------------------
    // Header normalisation
    // --------------------------------------------------------------------------

    protected function detectDelimiter(string $line): string
    {
        return substr_count($line, ';') > substr_count($line, ',') ? ';' : ',';
    }

    /**
     * Remove UTF-8 BOM (EF BB BF) and surrounding double-quotes from every
     * header string that was read via fgetcsv().
     */
    protected function stripBomFromHeaders(array $headers): array
    {
        return array_map(function ($h) {
            // Remove BOM
            $h = ltrim((string) $h, "\xEF\xBB\xBF");
            // Remove stray leading/trailing quote characters fgetcsv may leave
            $h = trim($h, '"');
            return trim($h);
        }, $headers);
    }

    // --------------------------------------------------------------------------
    // Row mapping
    // --------------------------------------------------------------------------

    /**
     * Map a raw fgetcsv() row array onto an associative array keyed by the
     * header names.
     *
     * Special case: provenance_final.csv uses a semicolon field delimiter but
     * the actual data is stored as a single quoted comma-delimited value in
     * field[0].  When the row has exactly 1 element and the outer delimiter
     * is ';' we re-parse field[0] as comma-CSV.
     */
    protected function mapCsvRow(array $headers, array $row, string $delimiter): array
    {
        // Provenance/SIM hybrid CSVs: entire record collapsed into field[0].
        if (!empty($row[0]) && $delimiter === ';') {
            $fallback = str_getcsv($row[0], ',', '"', '\\');
            if (count($fallback) >= 3 && is_numeric(trim($fallback[0]))) {
                $row = $fallback;
            }
        }

        $mapped = [];
        foreach ($headers as $index => $header) {
            $key = trim((string) $header);
            if ($key === '') {
                continue;
            }
            $mapped[$key] = $row[$index] ?? null;
        }

        return $mapped;
    }

    // --------------------------------------------------------------------------
    // Artwork lookup (with soft-delete awareness)
    // --------------------------------------------------------------------------

    protected function findArtworkByMetObjectId(mixed $metObjectId): ?ArtWork
    {
        if ($metObjectId === null || trim((string) $metObjectId) === '') {
            return null;
        }

        // ArtWork does NOT use SoftDeletes — plain where() is correct.
        return ArtWork::where('met_object_id', (int) $metObjectId)->first();
    }

    // --------------------------------------------------------------------------
    // Text helpers
    // --------------------------------------------------------------------------

    /**
     * Trim surrounding whitespace.  Intentionally does NOT strip internal
     * newlines so multiline provenance / SIM / reference text is preserved.
     */
    protected function normalizeText(mixed $value): string
    {
        return trim((string) $value);
    }

    protected function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value !== '' ? $value : null;
    }

    protected function splitDelimitedValue(mixed $value, string $delimiter = '|'): array
    {
        if ($value === null) {
            return [];
        }
        $parts = array_map('trim', explode($delimiter, (string) $value));
        return array_values(array_filter($parts, static fn ($part) => $part !== ''));
    }

    // --------------------------------------------------------------------------
    // Console output helpers (safe: no-op when $this->command is absent)
    // --------------------------------------------------------------------------

    protected function consoleInfo(string $message): void
    {
        if (isset($this->command)) {
            $this->command->info($message);
        }
    }

    protected function consoleWarn(string $message): void
    {
        if (isset($this->command)) {
            $this->command->warn($message);
        }
    }

    protected function consoleNewline(): void
    {
        if (isset($this->command)) {
            $this->command->line('');
        }
    }
}