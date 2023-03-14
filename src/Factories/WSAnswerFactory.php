<?php

namespace taki47\otpszep\Factories;

use taki47\otpszep\Factories\WAnswerOfWebShopTranzAzonGeneralas;
use taki47\otpszep\Factories\WAnswerOfWebShopTrazakcioLekerdezes;
use taki47\otpszep\Factories\WAnswerOfWebShopFizetes;
use taki47\otpszep\Factories\WAnswerOfWebShopFizetesKetszereplos;
use taki47\otpszep\Factories\WAnswerOfWebShopJovairas;
use taki47\otpszep\Factories\WAnswerOfWebShopKulcsLekerdezes;

/**
* A tranzakciós válasz XML-eket reprezentáló value object 
* és azt előállító WAnswerOf... osztályok összerendelése.
* 
* @access private
* 
* @version 4.0
*/
class WSAnswerFactory  {

    /**
    * Adott tranzakciós válasz XML-t reprezentáló value object-et 
    * előállító WAnswerOf... objektum előállítása.
    *  
    * @param string a tranzakció kódja
    * @return mixed a megfelelő WAnswerOf... objektum
    */
    static function getAnswerFactory($workflowName) {
        switch ($workflowName) {
           case 'WEBSHOPTRANZAZONGENERALAS':
                return new WAnswerOfWebShopTranzAzonGeneralas();
           case 'WEBSHOPTRANZAKCIOLEKERDEZES':
                return new WAnswerOfWebShopTrazakcioLekerdezes();
           case 'WEBSHOPFIZETESINDITAS':
                return new WAnswerOfWebShopFizetes();
           case 'WEBSHOPFIZETESKETSZEREPLOS':
                return new WAnswerOfWebShopFizetesKetszereplos();
           case 'WEBSHOPFIZETESLEZARAS':
                return new WAnswerOfWebShopFizetesKetszereplos();    
           case 'WEBSHOPFIZETESJOVAIRAS':
                return new WAnswerOfWebShopJovairas();
           case 'WEBSHOPKULCSLEKERDEZES':
                return new WAnswerOfWebShopKulcsLekerdezes();
        }        
        return NULL;
    }

}

?>