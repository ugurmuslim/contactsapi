<?php


namespace App\Mailer\Interfaces;


interface ExternalServiceInterface
{
    public function generateServiceUrl();
    public function completeAuthentication(string $code);
    public function getContacts(string $accessToken);
    public function downloadAsCSV(array $contactList);
}
