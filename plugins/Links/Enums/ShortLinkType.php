<?php

namespace Plugins\Links\Enums;

enum ShortLinkType: string
{
    case Upload = 'upload';
    case Download = 'download';
    case Redirect = 'redirect';
}
