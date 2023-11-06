<?php
class MpesaRegister 
{

    #  LNMO access token credentials
    const API_KEY="FVLxAEkNqp4LwFz7bN97OtlrCUd42AQM";
    const CONSUMER_SECRET="z8l5LvO6ccFZLnVN";
    const ACCESS_TOKEN_URL  = "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
    const BUSINESS_SHORTCODE = "600991";
    const CONFIRMATION_URL = "http://161.35.6.91/finebreeze/register.php";
    const VALIDATION_URL = "http://161.35.6.91/finebreeze/register.php";
    const REGISTER_URL = "https://sandbox.safaricom.co.ke/mpesa/c2b/v1/registerurl";


    /**
     * Generates token for for Lipa Na mpesa online
     * @param none
     * @return (token)
     */
    public function generateAccessToken() {
        $consumerKey = self::API_KEY;
        $consumerSecret = self::CONSUMER_SECRET; 
   
        $headers = ['Content-Type:application/json; charset=utf8'];
        $access_token_url = self::ACCESS_TOKEN_URL;
    
        $curl = curl_init($access_token_url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_USERPWD, $consumerKey.':'.$consumerSecret);

        $result = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $result = json_decode($result);

        $access_token = $result->access_token;

        return $access_token;
    }

    public function validationURL() {
        
        $data = file_get_contents('php://input');

        if (!$data) {
            return "Invalid Request";
        }

        $data = json_decode($data);
       
        file_put_contents('MpesaValidationResp.txt', var_export($data, true));
    }


    public function confirmationURL() {
        
        $data = file_get_contents('php://input');

        if (!$data) {
            return "Invalid Request";
        }

        $data = json_decode($data);
        
        file_put_contents('MpesaConfirmationResp.txt', var_export($data, true));
    }


    public function registerURL() {
        $consumerKey = self::API_KEY;
        $consumerSecret = self::CONSUMER_SECRET;
        $businessShortCode = self::BUSINESS_SHORTCODE;
        $validationURL = self::VALIDATION_URL;
        $confirmationURL = self::CONFIRMATION_URL;

        $url = self::REGISTER_URL;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->generateAccessToken(),
            'Content-Type: application/json',
        ]);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode([
            'ShortCode' => $businessShortCode,
            'ResponseType' => 'Completed',
            'ValidationURL' => $validationURL,
            'ConfirmationURL' => $confirmationURL,
        ]));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        curl_close($curl);

        $json = json_decode($response);

        if ($json->ResponseCode == 0) {
            echo 'URLs registered and confirmed successfully.';
        } else {
            echo 'Error registering and confirming URLs: ' . $json->ResponseDescription;
        }
    }

}

$mpesa = new MpesaRegister();

echo $mpesa->registerURL();
echo $mpesa->confirmationURL();
echo $mpesa->validationURL();