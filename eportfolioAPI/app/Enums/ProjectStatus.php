<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Built = 'built';
    case InProgress = 'in-progress';
}
