<?php

namespace App\Services;

class MarkdownLiteService
{
    public function render(string $input): string
    {
        // Intentionally minimal for the stored-XSS training surface documented in TECH_SPEC §8.
        $output = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $input);
        $output = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $output ?? $input);

        return preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" class="text-indigo-600 underline">$1</a>', $output ?? $input) ?? $input;
    }
}
