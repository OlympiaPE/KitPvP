<?php

declare(strict_types=1);

namespace Olympia\Kitpvp\exceptions;

use Exception;

final class CosmeticsException extends Exception
{
    public const ERR_CREATE_COSMETICS_DIR = 0;
    public const ERR_TOTAL_LUCK_RATE = 1;
}