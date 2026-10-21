<?php

namespace App\Enums;

enum ServiceType: string
{
    case Consultation = 'consultation'; // Konsultasi
    case Design = 'design';             // Jasa desain
    case Supervision = 'supervision';   // Pengawasan pembangunan
}
