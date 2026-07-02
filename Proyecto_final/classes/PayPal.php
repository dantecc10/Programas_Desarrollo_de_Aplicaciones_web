<?php
require_once __DIR__ . '/../config/config.php';

class PayPal
{
    private $baseUrl;
    private $clientId;
    private $secret;

    public function __construct()
    {
        $this->clientId = PAYPAL_CLIENT_ID;
        $this->secret = PAYPAL_CLIENT_SECRET;
        $this->baseUrl = PAYPAL_MODE === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    private function getAccessToken()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("PayPal getAccessToken: HTTP $httpCode - $response");
            return null;
        }

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    public function createOrder($monto, $moneda = 'MXN', $referencia = '')
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $moneda,
                    'value' => number_format($monto, 2, '.', '')
                ],
                'description' => 'Reservación de cancha - ' . SITE_NAME,
                'invoice_id' => $referencia
            ]]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            error_log("PayPal createOrder: HTTP $httpCode - $response");
            return null;
        }

        return json_decode($response, true);
    }

    public function captureOrder($orderId)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            error_log("PayPal captureOrder: HTTP $httpCode - $response");
            return null;
        }

        return json_decode($response, true);
    }
}
