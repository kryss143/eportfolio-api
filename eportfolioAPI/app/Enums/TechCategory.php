<?php

namespace App\Enums;

/**
 * Adjust these cases to match whatever values your TechCategory
 * TypeScript type actually allows.
 */
enum TechCategory: string
{
    case Frontend = 'frontend';
    case Backend = 'backend';
    case Fullstack = 'fullstack';
    case CICD = 'cicd';
    case AI = 'ai';
    case Database = 'database';
    case Deploy = 'deploy';
    case Fundamental = 'fundamental';
}
