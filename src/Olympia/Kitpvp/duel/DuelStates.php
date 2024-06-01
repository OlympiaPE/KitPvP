<?php

namespace Olympia\Kitpvp\duel;

interface DuelStates
{
    public const PENDING = 0;
    public const STARTING = 1;
    public const IN_PROGRESS = 2;
    public const FINISHED = 3;
}