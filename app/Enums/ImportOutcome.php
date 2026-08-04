<?php

declare(strict_types=1);

namespace App\Enums;

enum ImportOutcome: string
{
    case Imported = 'imported';
    case SkippedDuplicate = 'skipped_duplicate';
    case SkippedIncomplete = 'skipped_incomplete';
    case Failed = 'failed';
}
