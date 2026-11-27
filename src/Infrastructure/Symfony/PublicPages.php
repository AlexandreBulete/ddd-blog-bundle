<?php

declare(strict_types=1);

namespace AlexandreBulete\DddBlogBundle\Infrastructure\Symfony;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Where the public sees a post or a category, when the application says so
 * (`blog.public_routes`): the back office links to it and shows the address
 * before the slug field.
 */
final readonly class PublicPages
{
    private const string PLACEHOLDER = 'slug-placeholder';

    /**
     * @param array{post?: ?string, category?: ?string} $routes
     */
    public function __construct(
        private UrlGeneratorInterface $urls,
        private array $routes = [],
    ) {}

    public function url(string $kind, string $slug): ?string
    {
        $route = $this->routes[$kind] ?? null;

        return $route === null ? null : $this->urls->generate($route, ['slug' => $slug]);
    }

    /**
     * The address up to the slug: `/blog/`.
     */
    public function prefix(string $kind): ?string
    {
        $url = $this->url($kind, self::PLACEHOLDER);
        if ($url === null || !str_ends_with($url, self::PLACEHOLDER)) {
            return null;
        }

        return substr($url, 0, -\strlen(self::PLACEHOLDER));
    }
}
