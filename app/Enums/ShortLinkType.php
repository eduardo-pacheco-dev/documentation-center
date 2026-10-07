<?php

namespace App\Enums;

enum ShortLinkType: string
{
    case Upload = 'upload';
    case Download = 'download';
}
