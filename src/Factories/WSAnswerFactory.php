<?php

namespace App\Http\Controllers\Shop\Factories;

use WAnswerOfWebShopTranzAzonGeneralas;
use WAnswerOfWebShopTrazakcioLekerdezes;
use WAnswerOfWebShopFizetes;
use WAnswerOfWebShopFizetesKetszereplos;
use WAnswerOfWebShopJovairas;
use WAnswerOfWebShopKulcsLekerdezes;

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
    function getAnswerFactory($workflowName) {
        switch ($workflowName) {
           case 'WEBSHOPTRANZAZONGENERALAS':
                return new WAnswerOfWebShopTranzAzonGeneralas();
           case 'WEBSHOPTRANZAKCIOLEKERDEZES':
                return new WAnswerOfWebShopTrazakcioLekerdezes();
           case 'WEBSHOPFIZETES':
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