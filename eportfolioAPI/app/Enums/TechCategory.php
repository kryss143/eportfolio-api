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
    case Database = 'database';
    case DevOps = 'devops';
    case Tooling = 'tooling';
    case Other = 'other';
}

?>