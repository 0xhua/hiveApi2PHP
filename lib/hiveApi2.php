<?php
require_once './vendor/stefangabos/zebra_curl/Zebra_cURL.php';

class hiveApi2
{
    private $baseURL = 'https://api2.hiveos.farm/api/v2/';
    private $login;
    private $password;
    private $tfa;
    private $result;

    /**
     * hiveApi2 constructor.
     * @param $login - HiveOs.farm Email/Username
     * @param $password - HiveOs.farm Password
     * @param $tfa - Two Factor Authentication
     */
    public function __construct(string $login, string $password, string $tfa)
    {
        $this->login = $login;
        $this->password = $password;
        $this->tfa = $tfa;
    }

    private function login()
    {
        $curl = new Zebra_cURL();
        $curl->ssl(false);
        $curl->option(CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        $curl->post(
            array(
                $this->baseURL.'auth/login' => json_encode(array(
                        'login' => $this->login,
                        'password' => $this->password,
                        'twofa_code'=> $this->tfa
                    )
                )), function ($result) {
                var_dump($result);
            if ($result->response[1] == CURLE_OK) {
                if ($result->info['http_code'] == 200) {
                        var_dump(json_decode($result->body, true));
                }elseif ($result->info['http_code'] == 403){

                } else trigger_error('Server responded with code ' . $result->info['http_code'], E_USER_ERROR);
            } else trigger_error('cURL responded with: ' . $result->response[0], E_USER_ERROR);
        }
        );
    }

    public function start(){
        @$this->login();
    }
}
