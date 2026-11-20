<?php

namespace App\Console\Commands;

use App\Enums\TestimonialStatus;
use App\Models\IpnLog;
use App\Models\Message;
use App\Models\Testimonial;
use App\Support\Media;
use App\Support\Retention;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('privacy:prune')]
#[Description('Delete or anonymise personal data past the retention periods in the privacy policy')]
class PrunePersonalData extends Command
{
    public function handle(): int
    {
        $ips = Message::whereNotNull('ip_address')->where('created_at', '<', now()->subDays(Retention::ENQUIRY_IP_DAYS))->update(['ip_address' => null]);
        $messages = Message::where('created_at', '<', now()->subMonths(Retention::ENQUIRY_MONTHS))->delete();

        $invites = 0;
        Testimonial::where('status', TestimonialStatus::Invited)->where('created_at', '<', now()->subMonths(Retention::UNUSED_INVITE_MONTHS))
            ->each(function (Testimonial $t) use (&$invites) {
                Media::delete($t->avatar);
                $t->delete();
                $invites++;
            });

        $ipn = IpnLog::where('created_at', '<', now()->subYears(Retention::FINANCIAL_YEARS))->delete();

        $this->components->info("Cleared {$ips} enquiry IP(s), deleted {$messages} old enquiry(ies), {$invites} unused testimonial invite(s) and {$ipn} old payment log(s).");

        return self::SUCCESS;
    }
}
