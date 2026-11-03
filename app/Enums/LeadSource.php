<?php

namespace App\Enums;

enum LeadSource: string
{
    case PropertyDetail = 'property_detail';
    case ArchitectPortfolio = 'architect_portfolio';
    case KprCalculator = 'kpr_calculator';
}
