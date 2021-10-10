<?php


namespace App\Mailer\ExternalServices;

use App\Mailer\Interfaces\ExternalServiceInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class GoogleService implements ExternalServiceInterface
{

    private $publicKey;
    private $secretKey;
    private $scope = "openid%20profile%20email%20https://www.googleapis.com/auth/contacts.readonly";
    private $redirectUri = "http://localhost:8000/authentication/complete/google";

    public function __construct()
    {
        $this->publicKey = env("GOOGLE_PUBLIC_KEY");
        $this->secretKey = env("GOOGLE_SECRET_KEY");
    }

    public function generateServiceUrl()
    {
        return "https://accounts.google.com/o/oauth2/v2/auth/oauthchooseaccount?" .
            "scope=$this->scope" .
            "&response_type=code" .
            "&redirect_uri=$this->redirectUri" .
            "&client_id=$this->publicKey" .
            "&flowName=GeneralOAuthFlow";
    }

    public function completeAuthentication($code)
    {
        $response = Http::post("https://oauth2.googleapis.com/token", [
            "code"          => $code,
            "client_id"     => $this->publicKey,
            "client_secret" => $this->secretKey,
            "redirect_uri"  => $this->redirectUri,
            "grant_type"    => "authorization_code",
        ]);

        if ($response->status() != 200) {
            return false;
        }
        $jsonResponse = $response->json();
        return $this->getContacts($jsonResponse['access_token']);
        //return $jsonResponse['access_token'];

    }

    public function getContacts(string $accessToken)
    {
        $response = Http::withHeaders([
            "Authorization" => "Bearer " . $accessToken,
        ])
            ->get("https://people.googleapis.com/v1/people/me/connections?personFields=names,emailAddresses");

        if ($response->status() != 200) {
            return false;
        }
        $jsonResponse = $response->json();
        $contactArray = [];
        foreach ($jsonResponse['connections'] as $connection) {
            $contactArray[] = [
                "displayName" => isset($connection['names'][0]['displayName']) ? $connection['names'][0]['displayName'] : null,
                "givenName"   => isset($connection['names'][0]['givenName']) ? $connection['names'][0]['givenName'] : null,
                "familyName"  => isset($connection['names'][0]['familyName']) ? $connection['names'][0]['familyName'] : null,
                "email"       => $connection['emailAddresses'][0]['value'],
            ];
        }

        return $this->downloadAsCSV($contactArray);

    }

    public function downloadAsCSV(array $contacts)
    {
        $headers = [
            'Cache-Control'         => 'must-revalidate, post-check=0, pre-check=0'
            , 'Content-type'        => 'text/csv'
            , 'Content-Disposition' => 'attachment; filename=Google-' . Carbon::now()->format('Y-m-d H:i:s') . ".csv"
            , 'Expires'             => '0'
            , 'Pragma'              => 'public',
        ];


        $callback = function () use ($contacts) {
            $FH = fopen('php://output', 'w');
            fputcsv($FH, [
                "Display Name", "Name", "Surname", "Email",
            ]);
            foreach ($contacts as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return response()->stream($callback, 200, $headers);

    }
}
