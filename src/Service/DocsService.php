<?php

namespace App\Service;

use Exception;
use Michelf\Markdown;
use Symfony\Component\HttpFoundation\Response;

class DocsService
{
    const DOCS_DIR = 'docs';
    const FILE_EXTENSION = 'md';

    public function __construct()
    {
    }

    public function getHtmlContent(string $filename): Response
    {
        $path = $this->getRealPath($filename);
        $this->fileIsValid($path);

        $content = file_get_contents($path);
        $markdown = Markdown::defaultTransform($content);

        return new Response($this->replaceLinks($markdown), 200);
    }

    private function getRealPath(string $filename): string
    {
        return realpath(sprintf('%s/../%s/%s.%s', getcwd(), self::DOCS_DIR, $filename, self::FILE_EXTENSION));
    }

    private function fileIsValid(string $path): void
    {
        if (!file_exists($path)) {
            throw new Exception('Accessing an unexisting file in ' . $path, 500);
        }
        if (is_dir($path)) {
            throw new Exception('Trying to access file at ' . $path . ', but a directory was found', 500);
        }
    }

    private function replaceLinks(string $content): string
    {
        return str_replace('.md', '', $content);
    }
}
