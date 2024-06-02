<?php

namespace Olympia\Kitpvp\handlers;

abstract class Handler
{
    abstract public function onLoad(): void;
}