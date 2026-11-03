<?php

namespace App\Enums;

enum TransactionType: string
{
    case PropertyBookingFee = 'property_booking_fee'; // tanda jadi / booking fee properti
    case ArchitectBlueprint = 'architect_blueprint';    // Jalur A: paket desain siap bangun
    case ArchitectCustomBooking = 'architect_custom';   // Jalur B: tanda jadi custom/survei
    case ArchitectBooking = 'architect_booking';     // pembayaran jasa konsultasi arsitek
}
