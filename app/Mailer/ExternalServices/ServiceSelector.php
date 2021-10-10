<?php


namespace App\Mailer\ExternalServices;


class ServiceSelector
{
    public static function selector($service)
    {
        switch ($service) {
            case $service = "google":
                return new GoogleService();
            default:
                return false;
        }
    }
}
