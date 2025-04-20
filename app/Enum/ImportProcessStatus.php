<?php

namespace App\Enum;

enum ImportProcessStatus: int
{
    case PENDING = 1;
    case PROCESSING = 2;
    case DONE = 3;
    case FAILED = 4;
}
