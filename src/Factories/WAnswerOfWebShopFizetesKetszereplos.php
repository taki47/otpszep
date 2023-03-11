<?php
namespace App\Http\Controllers\Shop\Factories;

use ..\Models\WebShopFizetesValasz;
use ..\Utils\WebShopXmlUtils;

/**
* Kétszereplős fizetés illetve kétlépcsős fizetés lezárás 
* válasz XML-jének feldolgozásása és a megfelelő value object előállítása.
* 
* @version 4.0
*/
class WAnswerOfWebShopFizetesKetszereplos {

    /**
    * Kétszereplős fizetés illetve kétlépcsős fizetés lezárás 
    * válasz XML-jének feldolgozásása és a megfelelő value object előállítása.
    * 
    * @param DomDocument $answer A tranzakciós válasz xml
    * @return WebShopFizetesValasz a válasz tartalma, 
    *         vagy NULL üres/hibás válasz esetén
    */
    function load($answer) {
        $webShopFizetesValasz = new WebShopFizetesValasz();
       
        $record = WebShopXmlUtils::getNodeByXPath($answer, '//answer/resultset/record');
        if (!is_null($record)) {
            $webShopFizetesValasz->setPosId(WebShopXmlUtils::getElementText($record, "posid"));
            $webShopFizetesValasz->setAzonosito(WebShopXmlUtils::getElementText($record, "transactionid"));
            $webShopFizetesValasz->setTeljesites(WebShopXmlUtils::getElementText($record, "timestamp"));
            $webShopFizetesValasz->setValaszKod(WebShopXmlUtils::getElementText($record, "posresponsecode"));
            $webShopFizetesValasz->setAuthorizaciosKod(WebShopXmlUtils::getElementText($record, "authorizationcode"));
        }
        
        return $webShopFizetesValasz;
    }

}

?>