<?php

namespace Database\Seeders\Concerns;

use App\Models\ArtWork;

/**
 * Shared JSON-reading utilities for all MetMuseum JSON seeders.
 */
trait ReadsMetMuseumJson
{
    protected function jsonPath(string $filename): string
    {
        return database_path('data/' . $filename);
    }

    /**
     * Read and decode a JSON file.
     */
    protected function readJsonRows(string $filename): ?array
    {
        $path = $this->jsonPath($filename);

        if (!is_file($path)) {
            $this->consoleWarn("[JSON Reader] File not found: {$path}");
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            $this->consoleWarn("[JSON Reader] Failed to read file: {$path}");
            return null;
        }

        // Clean any stray NaN / invalid values if necessary (consistent with project patterns)
        $content = str_replace(': NaN', ': null', $content);

        $data = json_decode($content, true);
        if (!is_array($data)) {
            $this->consoleWarn("[JSON Reader] Failed to parse JSON: {$path}");
            return null;
        }

        return $data;
    }

    /**
     * Find artwork model by met_object_id.
     */
    protected function findArtworkByMetObjectId(mixed $metObjectId): ?ArtWork
    {
        if ($metObjectId === null || trim((string) $metObjectId) === '') {
            return null;
        }

        return ArtWork::where('met_object_id', (int) $metObjectId)->first();
    }

    /**
     * Trim surrounding whitespace.
     */
    protected function normalizeText(mixed $value): string
    {
        return trim((string) $value);
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
