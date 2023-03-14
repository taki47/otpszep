# OTP SZÉP card integration for Laravel based website

This module helps you to easily embed an OTP SZÉP card payment solution into your Laravel based website.

## IMPORTANT!!!
This module is not an official OTP module. Use at your own risk!

## INSTALLATION
Edit project composer.json
```php
"require": {
    ...
    "taki47/otpszep": "^1.0.0"
    ...
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/taki47/otpszep"
    }
],
```

Run composer update
```sh
composer update
```

## USAGE
Add these settings to your .env file
```php
POS_ID="#02299991" #your pos_id
PRIV_KEY_FILENAME="#02299991.privKey.pem" #private key file with absolute url from your project root
CURRENCY="HUF"
LANG_CODE="hu"
BACK_URL="${APP_URL}/payResult"
```

## That's it! Happy code :-)

## Example code - Start payment
```php
use taki47\otpszep\Service;

class PublicController extends Controller
{
    public function PayStart()
    {
        // generate random transaction number here
        $transactionNr = "asdfgh123";
        // this variable contains payable amount (only number)
        $amount = "123";

        // do something with the transactionNr

        // start transaction
        $service = new Service();
        $response = $service->startWorkFlow($transactionNr, $amount);

        if ( $response["message"] == "SIKERESWEBSHOPFIZETESINDITAS" ) {
            // if success redirect to OTP payment URL
            return redirect($response["url"]);
        } else {
            // transaction start failed, return server error
            abort(500);
        }
    }
}
```


## Example code - Payment result (back)
```php
use taki47\otpszep\Service;

class PublicController extends Controller
{
    public function back(Request $request)
    {
        $azonosito = $request->tranzakcioAzonosito;

        $service = new Service();
        $response = $service->tranzakcioStatusLekerdezes($azonosito);
        
        if ( $response->posValaszkod=="000" ) {
            // payment result is success
        }

        /**
         * DO SOMETHING WITH $response ARRAY
         */
    }
}
```