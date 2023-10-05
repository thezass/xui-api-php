# XUI php write in php

## for [MHSanaei](https://github.com/MHSanaei/3x-ui) and [alireza0](https://github.com/alireza0/x-ui) panels

## Getting start

```php
<?php

$xui  = new xui (PANEL_LINK,"admin",'admin','MHSanaei');

>
```

## create client

```php
$uuid = $xui->generateUUID();
$gb = 300 * 1024 * 1024;

$settings = array(
        "id" => 1,
        "settings" => json_encode(array(
            "clients" => array(
                array(
                    "id" => $uuid,
                    "alterId" => 0,
                    "email" => uniqid(),
                    "totalGB" => $gb,
                    "expiryTime" => -86400000,
                    "enable" => true,
                    "tgId" => "",
                    "subId" => '',
                    "limitIp" => 1,
                    "flow" => ""
                )
            )
        ))
);


$response = $xui->create_client($settings);
```

## Get client info

```php
$user_info = $xui->cilent_info($uuid= '2dde3b6e-cf32-41ba-b1ea-22e9ae36e4a3');
```
