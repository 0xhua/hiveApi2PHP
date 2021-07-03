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


    private function login(bool $remember = false)
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
                        'twofa_code'=> $this->tfa,
                        'remember' => $remember
                    )
                )), function ($result) {
            if ($result->response[1] == CURLE_OK) {
                if ($result->info['http_code'] == 200) {
                    /**
                     * STATUS 200
                     {
                       "access_token": "string",
                       "token_type": "bearer",
                       "expires_in": 0
                      }*/
                    @$this->result=json_decode($result->body);
                }elseif ($result->info['http_code'] == 403){
                    /**
                     * STATUS 403
                    	Not allowed for authenticated users
                    */
                    @$this->result=json_decode($result->body, true);
                }elseif ($result->info['http_code'] == 422){
                    /**
                     * STATUS 422
                    	{
                          "message": "The given data was invalid.",
                          "errors": {
                            "field1": [
                              "The field1 is required"
                            ],
                            "field2": [
                              "The field2 must be a string",
                              "The field2 must be 6 characters length"
                            ]
                          }
                        }
                    */
                    @$this->result=json_decode($result->body, true);
                } else trigger_error('Server responded with code ' . $result->info['http_code'], E_USER_ERROR);
            } else trigger_error('cURL responded with: ' . $result->response[0], E_USER_ERROR);
        }
        );
        return $this->result;
    }

    private function logout($token){
        $curl = new Zebra_cURL();
        $curl->ssl(false);
        $curl->option(CURLOPT_HTTPHEADER,[
            "Authorization: Bearer $token"
        ]);
        $curl->post($this->baseURL.'auth/logout',function ($result) {
            var_dump($result);
            if ($result->response[1] == CURLE_OK) {
                if ($result->info['http_code'] == 401) {
                    /**
                     * STATUS 401
                    {
                    "message": "Unauthenticated."
                    }*/
                    @$this->result=json_decode($result->body, true);
                }elseif ($result->info['http_code'] == 204){
                    /**
                     * STATUS 204
                    Successful logout
                     */
                    @$this->result=json_decode($result->body, true);
                } else trigger_error('Server responded with code ' . $result->info['http_code'], E_USER_ERROR);
            } else trigger_error('cURL responded with: ' . $result->response[0], E_USER_ERROR);
        });
        return $this->result;
    }
}
