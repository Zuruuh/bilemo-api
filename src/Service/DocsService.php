<?php

namespace App\Service;

use Exception;
use Michelf\Markdown;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

class DocsService
{
    const DOCS_DIR = 'docs';
    const FILE_EXTENSION = 'md';
    const DOC_DOES_NOT_EXIST = 'There are not documents with the name "%s"';

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getHtmlContent(string $filename): Response
    {
        $path = $this->getRealPath($filename);
        $this->fileIsValid($path, $filename);

        $content = file_get_contents($path);
        $markdown = Markdown::defaultTransform($content);

        return new Response($this->replaceLinks($markdown), 200);
    }

    private function getRealPath(string $filename): string
    {
        return realpath(sprintf('%s/../%s/%s.%s', getcwd(), self::DOCS_DIR, $filename, self::FILE_EXTENSION));
    }

    private function fileIsValid(string $path, string $filename): void
    {
        if (!file_exists($path)) {
            throw new NotFoundHttpException(sprintf(self::DOC_DOES_NOT_EXIST, $filename));
        }
        if (is_dir($path)) {
            throw new Exception('Trying to access file at ' . $path . ', but a directory was found', 500);
        }
    }

    private function replaceLinks(string $content): string
    {
        $base_route = $this->router->generate('app_api_app_doc');

        $formatted = str_replace('.md', '', $content);
        $formatted = preg_replace('/<a href="\.(\/\S+)"/', '<a href="' . $base_route . '$1"', $formatted);
        return $formatted;
    }
}
