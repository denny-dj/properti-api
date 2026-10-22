<?php

namespace App\Enums;

enum TransactionType: string
{
    case PropertyBookingFee = 'property_booking_fee'; // tanda jadi / booking fee properti
    case ArchitectBooking = 'architect_booking';     // pembayaran jasa konsultasi arsitek
}
