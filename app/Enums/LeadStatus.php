<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Surveying = 'surveying';
    case Committed = 'committed';
    case Lost = 'lost';
}
