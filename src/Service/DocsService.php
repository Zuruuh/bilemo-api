<?php

namespace App\Service;

use Exception;
use Michelf\Markdown;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class DocsService
{
    public const DOCS_DIR = 'docs';
    public const FILE_EXTENSION = 'md';
    public const DOC_DOES_NOT_EXIST = 'There are not documents with the name "%s"';

    private RouterInterface $router;
    private CacheInterface  $cache;

    public function __construct(
        RouterInterface $router,
        CacheInterface $cache
    ) {
        $this->cache = $cache;
        $this->router = $router;
    }

    /**
     * Returns the content of a Markdown file as an html document.
     * 
     * @param string $filename The documentation file to get
     * 
     * @return Response The http response returned to the user
     */
    public function getHtmlContent(string $filename): Response
    {
        $doc = $this->cache->get('docs-' . $filename, function (ItemInterface $item) use ($filename) {
            $item->expiresAfter(60 * 60);

            $path = $this->getRealPath($filename);
            $this->fileIsValid($path, $filename);

            $content = file_get_contents($path);
            $markdown = Markdown::defaultTransform($content);
            return $this->replaceLinks($markdown);
        });

        return new Response($doc, 200);
    }

    /**
     * Returns the path towards a file.
     * 
     * @param string $filename The name of the searched file
     * 
     * @return string The real path to a markdown file
     */
    private function getRealPath(string $filename): string
    {
        return realpath(sprintf('%s/../%s/%s.%s', getcwd(), self::DOCS_DIR, $filename, self::FILE_EXTENSION));
    }

    /**
     * Checks if a file exists & is valid.
     * 
     * @param string $path     The full path to a file
     * @param string $filename The name of the file to check
     * 
     * @return void
     */
    private function fileIsValid(string $path, string $filename): void
    {
        if (!file_exists($path)) {
            throw new NotFoundHttpException(sprintf(self::DOC_DOES_NOT_EXIST, $filename));
        }
        if (is_dir($path)) {
            throw new Exception('Trying to access file at ' . $path . ', but a directory was found', 500);
        }
    }

    /**
     * Replaces all the relatives links in an html document with absolute routes
     * 
     * @param string $content The html document
     * 
     * @return string The formatted html document
     */
    private function replaceLinks(string $content): string
    {
        $base_route = $this->router->generate('app_api_app_doc');

        $formatted = str_replace('.md', '', $content);
        $formatted = preg_replace('/<a href="\.(\/\S+)"/', '<a href="' . $base_route . '$1"', $formatted);

        return $formatted;
    }
}
