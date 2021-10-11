<?php

namespace App\Http\Controllers;

use App\Mailer\ExternalServices\ServiceSelector;
use App\Mailer\Interfaces\ExternalServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Request;

class AuthenticationController extends BaseAPIController
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

        if(!$service) {
            return $this->failureResult("Service could not be found");
        }

        $url = $service->generateServiceUrl();
        return redirect($url);
    }

    public function completeAuthentication($serviceName)
    {
        $code = Request::input("code");

        if(!$code) {
            return $this->failureResult("Wrong code");
        }
        /**
         * @var ExternalServiceInterface $service
         */
        $service = ServiceSelector::selector($serviceName);

        if(!$service) {
            return $this->failureResult("Service could not be found");
        }

        $response = $service->completeAuthentication($code);

        if(!$response) {
            return $this->failureResult("An error occurred");
        }

        return $response;
    }

    public function getContacts($serviceName) {

        $accessToken = Request::input("accessToken");
        /**
         * @var ExternalServiceInterface $service
         */
        $service = ServiceSelector::selector($serviceName);

        if(!$service) {
            return $this->failureResult("Service could not be found");
        }

        $response = $service->getContacts($accessToken);

        if(!$response) {
            return $this->failureResult("An error occurred");
        }

        return $response;

    }
}
