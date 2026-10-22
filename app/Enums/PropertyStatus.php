<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review'; // menunggu verifikasi admin
    case Published = 'published';
    case Reserved = 'reserved';            // booking fee sudah dibayar
    case Rejected = 'rejected';
    case Sold = 'sold';
}
