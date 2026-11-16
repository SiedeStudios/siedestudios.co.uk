<?php

namespace App\Support;

/**
 * How long personal data is kept. The privacy policy reads these values,
 * and `privacy:prune` enforces them — so the policy and reality match.
 */
class Retention
{
    /** Contact-form enquiries that didn't turn into work. */
    public const ENQUIRY_MONTHS = 24;

    /** IP addresses on enquiries (kept briefly for spam/abuse checks). */
    public const ENQUIRY_IP_DAYS = 30;

    /** Testimonial invites that were never filled in. */
    public const UNUSED_INVITE_MONTHS = 12;

    /** Invoices and payment records (HMRC: 6 years after the end of the financial year). */
    public const FINANCIAL_YEARS = 7;

    /** Server error logs (LOG_DAILY_DAYS). */
    public const LOG_DAYS = 14;
}
