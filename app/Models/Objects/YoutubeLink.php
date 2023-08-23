<?php

declare(strict_types=1);

namespace App\Models\Objects;

use App\Contracts\Objects\SocialVideoLinkInterface;
use App\Enums\SocialVideoType;
use GuzzleHttp\Psr7\Exception\MalformedUriException;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class YoutubeLink
 * @package App\Models\Objects
 */
final class YoutubeLink implements SocialVideoLinkInterface
{
    public const REGEX = '/(?:http:|https:)?\/\/(?:www\.)?(?:(?:m\.)?youtube(?:-nocookie)?\.com\/(?:e(?:mbed)?\/|live\/|watch\?.*v=)|youtu\.be\/)([a-z0-9-_]{11}|[a-z0-9-_]{64})/i';

    private readonly Uri $uri;

    private readonly string $videoId;

    /**
     * YoutubeLink constructor.
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

        $result = \preg_match(self::REGEX, $source, $matches);

        if (false === $result || 0 === $result) {
            throw new MalformedUriException(\sprintf('Unable to parse URI: "%s".', $source));
        }

        $this->uri = new Uri($source);
        $this->videoId = \array_pop($matches);
    }

    public function getVideoType(): SocialVideoType
    {
        return SocialVideoType::YOUTUBE;
    }

    public function getVideoId(): string
    {
        return $this->videoId;
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
}
