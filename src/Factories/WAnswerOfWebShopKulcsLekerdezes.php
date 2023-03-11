<?php
namespace App\Http\Controllers\Shop\Factories;

use ..\Models\WebShopKulcsAdatokLista;
use ..\Models\WebShopKulcsAdatok;
use ..\Utils\WebShopXmlUtils;

/**
* Kulcs lekérdezés válasz XML-jének feldolgozásása és
* a megfelelő value object előállítása.
* 
* @author Lászlók Zsolt
* @version 4.0
*/
class WAnswerOfWebShopKulcsLekerdezes {

    /**
    * @desc A banki felület által visszaadott szöveges logikai
    * értékből boolean típusú érték előállítása.
    * 
    * A képzés módja:
    * "TRUE" szöveges érték => true logikai érték
    * minden más érték => false logikai érték
    */
    function getBooleanValue($value) {
      $result = false;
      
      if (!is_null($value) && strcasecmp("TRUE", $value) == 0) {
        $result = true;
      }
      
      return $result;
    }

    /**
    * Kulcs lekérdezés válasz XML-jének feldolgozásása és
    * a megfelelő value object előállítása.
    * 
    * @param DomDocument $answer A tranzakciós válasz xml
    * @return WebShopKulcsAdatokLista a válasz tartalma, 
    *         vagy NULL üres/hibás válasz esetén
    */
    function load($answer) {
    	    	
    	$webShopKulcsAdatokLista = new WebShopKulcsAdatokLista();
        $resultSet = WebShopXmlUtils::getNodeByXPath($answer, '//answer/resultset');
                
        if(!empty($resultSet)) {
        	$webShopKulcsAdatokLista->setPrivateKey(WebShopXmlUtils::getElementText($resultSet, 'privateKey'));
        }
        
        $recordList = WebShopXmlUtils::getNodeArrayByXPath($answer, '//answer/resultset/record');
        $lista = array();
        
        foreach ($recordList as $record) {
        	
            $webShopKulcsAdatok = new WebShopKulcsAdatok();
            
            $webShopKulcsAdatok->setLejarat(WebShopXmlUtils::getElementText($record, 'lejarat'));
            
            $lista[] = $webShopKulcsAdatok;
        }
        
        $webShopKulcsAdatokLista->setWebShopKulcsAdatok($lista);
        
        return $webShopKulcsAdatokLista;
    }

}

?>