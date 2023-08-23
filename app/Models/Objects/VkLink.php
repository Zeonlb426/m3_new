<?php

declare(strict_types=1);

namespace App\Models\Objects;

use App\Contracts\Objects\SocialVideoLinkInterface;
use App\Enums\SocialVideoType;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class VkLink
 * @package App\Models\Objects
 */
final class VkLink implements SocialVideoLinkInterface
{
    private readonly Uri $uri;

    /**
     * @var array{oid: numeric-string, id: numeric-string, hash: string}
     */
    private readonly array $queryParts;

    /**
     * VkLink constructor.
     *
     * @param string $source - video uri or iframe for embedding, containing uri
     */
    public function __construct(string $source)
    {
        if (Str::isEmpty($source)) {
            throw new InvalidArgumentException('Argument "$source" can not be empty.');
        }

        \preg_match('/src\s*=\s*"(.+?)"/', $source, $match);

        if (isset($match[1]) && Str::isNotEmpty($match[1])) {
            $source = $match[1];
        }

        $this->uri = new Uri($source);
        $this->queryParts = Query::parse($this->uri->getQuery());

        $this->validate($source);
    }

    private function validate(string $source): void
    {
        if (false === \in_array($this->uri->getScheme(), ['http', 'https'])) {
            throw new MalformedUriException(\sprintf('Invalid scheme for uri "%s".', $source));
        }

        if (false === \in_array($this->uri->getHost(), ['vk.com', 'www.vk.com'])) {
            throw new MalformedUriException(\sprintf('Invalid domain for uri "%s".', $source));
        }

        if ('/video_ext.php' !== $this->uri->getPath()) {
            throw new MalformedUriException(\sprintf('Invalid path for uri "%s".', $source));
        }

        if (false === isset($this->queryParts['oid']) || false === \is_numeric($this->queryParts['oid'])) {
            throw new MalformedUriException(\sprintf('Invalid query part "oid" for uri "%s".', $source));
        }

        if (false === isset($this->queryParts['id']) || false === \is_numeric($this->queryParts['id'])) {
            throw new MalformedUriException(\sprintf('Invalid query part "id" for uri "%s".', $source));
        }

        if (false === isset($this->queryParts['hash']) || 0 === \strlen($this->queryParts['hash'])) {
            throw new MalformedUriException(\sprintf('Invalid query part "hash" for uri "%s".', $source));
        }
    }

    public function getVideoId(): string
    {
        return $this->queryParts['oid'] . '_' . $this->queryParts['id'];
    }

    public function getVideoType(): SocialVideoType
    {
        return SocialVideoType::VK;
    }

    public function __toString(): string
    {
        return $this->uri->__toString();
    }

    public static function tryParseUri(string $source): ?self
    {
        try {
            return new self($source);
        } catch (MalformedUriException) {
        }

        return null;
    }

    public static function maybeIsVkLink(string $source): bool
    {
        return \preg_match('/http(?:s)?:\/\/(?:www\.)?vk\.com/i', $source) > 0;
    }
}
