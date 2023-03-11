<?php
namespace taki47\otpszep\Factories;

use taki47\otpszep\Models\WebShopFizetesAdatok;
use taki47\otpszep\Utils\WebShopXmlUtils;

/**
* Háromszereplős fizetés válasz XML-jének feldolgozásása és
* a megfelelő value object előállítása.
* 
* @version 4.0
*/
class WAnswerOfWebShopFizetes {

    /**
    * Háromszereplős fizetés válasz XML-jének feldolgozásása és
    * a megfelelő value object előállítása.
    * 
    * @param DomDocument $answer A tranzakciós válasz xml
    * @return WebShopFizetesAdatok a válasz tartalma, 
    *         vagy NULL üres/hibás válasz esetén
    */
    function load($answer) {

        $webShopFizetesAdatok = NULL;
        
        $record = WebShopXmlUtils::getNodeByXPath($answer, '//answer/resultset/record');
        if (!is_null($record)) {
            $webShopFizetesAdatok = new WebShopFizetesAdatok();

            $webShopFizetesAdatok->setPosId(WebShopXmlUtils::getElementText($record, "posid"));
            $webShopFizetesAdatok->setAzonosito(WebShopXmlUtils::getElementText($record, "transactionid"));
            $webShopFizetesAdatok->setAuthorizaciosKod(WebShopXmlUtils::getElementText($record, "authorizationcode"));
            $webShopFizetesAdatok->setTeljesites(WebShopXmlUtils::getElementText($record, "timestamp"));

            $webShopFizetesAdatok->setNev(WebShopXmlUtils::getElementText($record, "name"));
            $webShopFizetesAdatok->setOrszag(WebShopXmlUtils::getElementText($record, "country"));
            $webShopFizetesAdatok->setMegye(WebShopXmlUtils::getElementText($record, "county"));
            $webShopFizetesAdatok->setVaros(WebShopXmlUtils::getElementText($record, "settlement"));
            $webShopFizetesAdatok->setIranyitoszam(WebShopXmlUtils::getElementText($record, "zipcode"));
            $webShopFizetesAdatok->setUtcaHazszam(WebShopXmlUtils::getElementText($record, "street"));
            $webShopFizetesAdatok->setMailCim(WebShopXmlUtils::getElementText($record, "mailaddress"));
            $webShopFizetesAdatok->setKozlemeny(WebShopXmlUtils::getElementText($record, "narration"));
        }
        
        return $webShopFizetesAdatok;
    }

}

?>