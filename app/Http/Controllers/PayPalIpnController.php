<?php

namespace App\Http\Controllers;

use App\Services\PayPal\IpnListener;
use App\Services\PayPal\IpnProcessor;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayPalIpnController extends Controller
{
    /**
     * PayPal expects an empty HTTP 200 for every IPN it delivers; anything else
     * makes it retry. Outcomes are recorded in ipn_logs for the admin panel.
     */
    public function __invoke(Request $request, IpnProcessor $processor): Response
    {
        $raw = $request->getContent();
        $fields = $request->post();

        if ($raw === '' && $fields !== []) {
            $raw = IpnListener::encode($fields);
        }

        if ($fields === [] && $raw !== '') {
            parse_str($raw, $fields);
        }

        $processor->handle($raw, $fields);

        return response('', 200);
    }
}
