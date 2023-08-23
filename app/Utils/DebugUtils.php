<?php

declare(strict_types=1);

namespace App\Utils;

use Illuminate\Http\Request;

/**
 * Class DebugUtils
 * @package App\Utils
 */
final class DebugUtils
{
    public static function isDebugIp(Request $request): bool
    {
        return \in_array($request->getClientIp(), \config('app.debug_ips'), true);
    }

    public static function telescopeEnabledByAdditionalEnvironments(Request $request): bool
    {
        return \count(\config('telescope.additional_environments')) > 0
            && \app()->environment(...\config('telescope.additional_environments'))
//            && (
//                \app()->runningInConsole() || self::isDebugIp($request)
//            )
        ;
    }
}
