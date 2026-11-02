<?php

namespace Plugins\Stations\Enums;

enum StationTechnology: string
{
    case TwoG = '2G';
    case ThreeG = '3G';
    case FourG = '4G';
    case FiveG = '5G';
    case FourGFiveG = '4G/5G';
}
