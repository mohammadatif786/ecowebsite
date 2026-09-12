<?php

namespace App\Domain\LiveStreams\Enums;

enum LiveStreamVisibility: string
{
    case Public = 'public';
    case Followers = 'followers';
    case Private = 'private';
}
