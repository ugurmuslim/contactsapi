<?php

namespace App\Http\Controllers;

use App\Mailer\ExternalServices\ServiceSelector;
use App\Mailer\Interfaces\ExternalServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class AuthenticationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function authentication($serviceName)
    {
        /**
         * @var ExternalServiceInterface $service
         */
        $service = ServiceSelector::selector($serviceName);
        $url = $service->generateServiceUrl();
        return redirect($url);
    }

    public function completeAuthentication($serviceName)
    {
        $code = Request::input("code");

        if(!$code) {
            dd(2);
        }
        /**
         * @var ExternalServiceInterface $service
         */
        $service = ServiceSelector::selector($serviceName);
        $response = $service->completeAuthentication($code);
        return $response;
    }

    public function getContacts($serviceName) {

        $accessToken = Request::input("accessToken");
        /**
         * @var ExternalServiceInterface $service
         */
        $service = ServiceSelector::selector($serviceName);
        $response = $service->getContacts($accessToken);
        return $response;

    }
}
