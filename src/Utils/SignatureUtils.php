<?php

namespace taki47\otpszep\Utils;

/**
* A WebShop PHP kliens által használt utility osztály
* digitális aláírás generálására PHP5 környezetben.
* 
* @version 4.0
*/

class SignatureUtils {

    /**
    * Privát kulcs fájlrendszerből történő betöltése és értelmezése.
    * A kulcs állománynak PEM formában kell lennie!
    * 
    * @param string $privKeyFileName a privát kulcs állomány elérési címe
    * @return resource privát kulcs
    */
    function loadPrivateKey($privKeyFileName) {
        $priv_key = file_get_contents($privKeyFileName);
        $pkeyid = openssl_get_privatekey($priv_key);
        return $pkeyid;
    }

    /**
    * Aláírandó szöveg előállítása az aláírandó szöveg értékek listájából:
    * [s1, s2, s3, s4]  ->  's1|s2|s3|s4'
    * 
    * @param array aláírandó mezők 
    * @return string aláírandó szöveg
    */
    function getSignatureText($signatureFields) {
        $signatureText = '';
        foreach ($signatureFields as $data) {
            $signatureText = $signatureText.$data.'|';
        }

        if (strlen($signatureText) > 0) {
            $signatureText = substr($signatureText, 0, strlen($signatureText) - 1);
        }

        return $signatureText;
    }

    /**
    * Digitális aláírás generálása a Bank által elvárt formában.
    * Az aláírás során az MD5 hash algoritmust használjuk 5.4.8-nál kisebb verziójú PHP
    * esetén, egyébként SHA-512 algoritmust.
    * 
    * @param string $data az aláírandó szöveg
    * @param resource $pkcs8PrivateKey privát kulcs
    * 
    * @return string digitális aláírás, hexadecimális formában (ahogy a banki felület elvárja). 
    */
    function generateSignature($data, $pkcs8PrivateKey) {
    	global $signature;

        openssl_sign($data, $signature, $pkcs8PrivateKey, OPENSSL_ALGO_SHA512);

        return bin2hex($signature);
    }

}

?>