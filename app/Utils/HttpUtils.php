<?php

declare(strict_types=1);

namespace App\Utils;

use JsonException;
use Psr\Http\Message\MessageInterface;

/**
 * Class HttpUtils
 * @package App\Utils
 */
final class HttpUtils
{
    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param bool $associative
     *
     * @return mixed
     *
     * @throws \JsonException
     */
    public static function decodeJsonHttpMessage(MessageInterface $message, bool $associative = false)
    {
        \GuzzleHttp\Psr7\Message::rewindBody($message);

        $content = $message->getBody()->getContents();

        \GuzzleHttp\Psr7\Message::rewindBody($message);

        return \json_decode($content, $associative, 512, \JSON_THROW_ON_ERROR);
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param bool $associative
     *
     * @return mixed|null
     */
    public static function decodeJsonHttpMessageSafe(MessageInterface $message, bool $associative = false)
    {
        try {
            return self::decodeJsonHttpMessage($message, $associative);
        } catch (JsonException) {
            return null;
        }
    }
}
