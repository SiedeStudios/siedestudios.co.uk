<?php

namespace App\Enums;

enum TestimonialStatus: string
{
    case Invited = 'invited';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
