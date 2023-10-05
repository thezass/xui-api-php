<?php

class xui
{

    private $link;
    private $user;
    private $pass;
    private $session;
    private $panel_mode;

    /**
     * @param string $link link path of your panel without / end of it
     * @param string $user username of panel
     * @param string $pass password of panel
     * @param string $panel_mode choose your panel between alireza0 and MHSanaei default is MHSanaei
     */

    public function __construct($link, $user, $pass, string $panel_mode = 'MHSanaei')
    {
        $this->link = $link;
        $this->user = $user;
        $this->pass = $pass;
        $this->session = $this->panel_login($link, $user, $pass);
        if ($panel_mode !== 'MHSanaei'  && $panel_mode !== 'alireza0') {
            throw new Exception('wrong panel mode name');
        }
        $this->panel_mode = $panel_mode;
    }

    /**
     * @return string login cookie of panel
     */
    public function panel_login()
    {
        $url = $this->link . '/login';

        $post = [
            'username' => $this->user,
            'password' => $this->pass,
        ];


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_HEADER, true);


        $response = curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpcode !== 200) {
            throw new Exception($response . curl_error($ch));
        }
        $cookies = [];

        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);

        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);

        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        curl_close($ch);

        $cookie = $cookies['session'];
        return $cookie;
    }



    /**
     * @param array $settings settings of client see example at https://github.com/thezass/xui-api-php 
     */
    public function create_client(array $settings)
    {
        if ($this->panel_mode == 'MHSanaei') {

            $url  = $this->link . '/panel/api/inbounds' . "/addClient";
        } else {
            $url  = $this->link . '/xui/API/inbounds' . '/addClient/';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_COOKIE, 'session=' . $this->session);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $settings);


        $res = json_decode(curl_exec($ch), true);

        if (curl_errno($ch)) {
            throw new Exception($res . curl_error($ch));
        }
        curl_close($ch);
        return $res;
    }

    /**
     * @param string $email email of client if you have email dont need to use uuid parameter
     * @param string $uuid uuid of client default is null if email passed 
     * @return array user_info array of info about client 
     */
    public function client_info(string $email = null, string $uuid = null)
    {

        if ($email == null) {
            if ($uuid == null) {
                throw new Exception('you need to fill out at least one parameter');
            }
            if ($this->panel_mode == 'MHSanaei') {

                $url  = $this->link . '/panel/api/inbounds' . '/list';
            } else {
                $url  = $this->link . '/xui/API/inbounds/';
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

            curl_setopt($ch, CURLOPT_COOKIE, 'session=' . $this->session);
            $res = json_decode(curl_exec($ch), true);
            curl_close($ch);
            if (curl_errno($ch)) {
                throw new Exception('cannot connect to server');
            }

            foreach ($res['obj'] as $set) {
                foreach (json_decode($set['settings'], true)['clients'] as $key => $value) {
                    if ($uuid ==  $value['id']) {
                        $email = $value['email'];
                        break;
                    }
                }
            }

            if ($email == null) {
                throw new Exception('User not Found');
            }
        }
        if ($this->panel_mode == 'MHSanaei') {
            $url  = $this->link . '/panel/api/inbounds' . '/getClientTraffics/' . $email;
        } else {
            $url  = $this->link . '/xui/API/inbounds' . '/getClientTraffics/' . $email;
        }


        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_COOKIE, 'session=' . $this->session);

        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('cannot connect to server');
        }
        curl_close($ch);
        return $res;
    }

    /**
     * @return string uuid for users in panel
     */

    public function generateUUID()
    {

        $data = random_bytes(16);


        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);


        $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

        return $uuid;
    }
}
