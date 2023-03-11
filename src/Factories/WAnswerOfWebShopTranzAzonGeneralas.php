<?php
namespace taki47\otpszep\Factories;

use taki47\otpszep\Models\WebShopTranzAzon;
use taki47\otpszep\Utils\WebShopXmlUtils;

/**
* A fizetési tranzakció azonosító generálás szolgáltatás 
* válasz XML-jének feldolgozásása és a megfelelő value object előállítása.
* 
* @version 4.0
*/
class WAnswerOfWebShopTranzAzonGeneralas {

    /**
    * A fizetési tranzakció azonosító generálás szolgáltatás 
    * válasz XML-jének feldolgozásása és a megfelelő value object előállítása.
    * 
    * @param DomDocument $answer A tranzakciós válasz xml
    * @return WebShopTranzAzon a válasz tartalma, 
    *         vagy NULL üres/hibás válasz esetén
    */
    function load($answer) {
        $webShopTranzAzon = null;

        $record = WebShopXmlUtils::getNodeByXPath($answer, '//answer/resultset/record');
        if (!is_null($record)) {
            $webShopTranzAzon = new WebShopTranzAzon();
            $webShopTranzAzon->setAzonosito(WebShopXmlUtils::getElementText($record, "id"));
            $webShopTranzAzon->setPosId(WebShopXmlUtils::getElementText($record, "posid"));
            $webShopTranzAzon->setTeljesites(WebShopXmlUtils::getElementText($record, "timestamp"));
        }
        
        return $webShopTranzAzon;
    }

}

?>