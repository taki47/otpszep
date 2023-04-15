<?php
    namespace taki47\otpszep;

    use Exception;
    use taki47\otpszep\Utils\WebShopXmlUtils;
    use taki47\otpszep\Utils\SignatureUtils;
    use taki47\otpszep\Utils\SoapUtils;
    use taki47\otpszep\Factories\WResponse;

    class Service {
        protected $customer_page_url = "https://www.otpbankdirekt.hu/webshop/do/webShopVasarlasInditas?posId={0}&azonosito={1}&nyelvkod={2}&version=5";
        protected $otp_mw_server_url = "https://www.otpbankdirekt.hu/mwaccesspublic/mwaccess";
        protected $back_url;
        protected $pos_id;
        protected $currency;
        protected $lang_code;
        protected $priv_key_fileName;
        protected $soap_client;

        function __construct($backURL)
        {
            $this->pos_id            = env("POS_ID");
            $this->currency          = env("CURRENCY");
            $this->lang_code         = env("LANG_CODE");
            $this->priv_key_fileName = env("PRIV_KEY_FILENAME");
            $this->back_url          = env("BACK_URL")."?tranzakcioAzonosito=";
            if ( $backURL!="" )
                $this->back_url = $backURL."?tranzakcioAzonosito=";
            
            $this->soap_client       = SoapUtils::createSoapClient($this->otp_mw_server_url);
        }

        function startWorkflow($tranzAzon, $amount)
        {
            $dom = WebShopXmlUtils::getRequestSkeleton("WEBSHOPFIZETESINDITAS", $variables);

            /* aláírás kiszámítása */
            $signatureFields = array(0 => $this->pos_id, $tranzAzon, $amount, $this->currency,"");
            $signatureText = SignatureUtils::getSignatureText($signatureFields);
            $pkcs8PrivateKey = SignatureUtils::loadPrivateKey($this->getPrivateKeyFile($this->priv_key_fileName));
            $signature = SignatureUtils::generateSignature($signatureText, $pkcs8PrivateKey);

            $attrName = 'algorithm';
            $attrValue = 'SHA512';


            /* paraméterek beillesztése */
            WebShopXmlUtils::addParameter($dom, $variables, "isClientCode", "WEBSHOP");
            WebShopXmlUtils::addParameter($dom, $variables, "isPOSID", $this->pos_id);
            WebShopXmlUtils::addParameter($dom, $variables, "isTransactionID", $tranzAzon);
            WebShopXmlUtils::addParameter($dom, $variables, "isAmount", $amount);
            WebShopXmlUtils::addParameter($dom, $variables, "isExchange", $this->currency);
            WebShopXmlUtils::addParameter($dom, $variables, "isLanguageCode", $this->lang_code);
            WebShopXmlUtils::addParameter($dom, $variables, "isCardPocketId", "07");
            WebShopXmlUtils::addParameter($dom, $variables, "isBackURL", $this->back_url.$tranzAzon);

            WebShopXmlUtils::addParameter($dom, $variables, "isNameNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isCountryNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isCountyNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isSettlementNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isZipcodeNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isStreetNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isMailAddressNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isNarrationNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isConsumerReceiptNeeded", "false");
            
            WebShopXmlUtils::addParameter($dom, $variables, "isShopComment", "");

            WebShopXmlUtils::addParameter($dom, $variables, "isConsumerRegistrationNeeded", "false");
            WebShopXmlUtils::addParameter($dom, $variables, "isTwoStaged", "false");

            WebShopXmlUtils::addParameter($dom, $variables, "isClientSignature", $signature, $attrName, $attrValue);
            
            $inputXml = WebShopXmlUtils::xmlToString($dom);
            
            // tranzakció indítása
            $workflowState = SoapUtils::startWorkflowSynch("WEBSHOPFIZETESINDITAS", $inputXml, $this->soap_client);

            $response = null;
            if ( !is_null($workflowState) ) {
                $response = new WResponse("WEBSHOPFIZETESINDITAS", $workflowState);
            }

            if ( $response->isFinished() ) {
                $responseDom = $response->getResponseDOM();
                $outputXML = WebShopXmlUtils::xmlToString($responseDom);
            }

            $url = "https://www.otpbankdirekt.hu/webshop/do/webShopVasarlasInditas?posId=".$this->pos_id."&azonosito=".$tranzAzon."&nyelvkod=".$this->lang_code."&version=5";
            $url = str_replace('#','%23',$url);

            $return = [
                "message" => $response->getMessages()[0],
                "url"     => $url
            ];

            return $return;
        }

        function getPrivateKeyFile($keyFile)
        {
            $keyFile = base_path()."/".$keyFile;
            if ( file_exists($keyFile) )
                return $keyFile;

            return false;
        }

        function tranzakcioStatusLekerdezes($tranzAzon) {
            /* aláírás kiszámítása */
            $signatureFields = array(0 => $this->pos_id, $tranzAzon, null, null, null);
            $signatureText = SignatureUtils::getSignatureText($signatureFields);
            $pkcs8PrivateKey = SignatureUtils::loadPrivateKey($this->getPrivateKeyFile($this->priv_key_fileName));
            $signature = SignatureUtils::generateSignature($signatureText, $pkcs8PrivateKey);

            $attrName = 'algorithm';
            $attrValue = 'SHA512';
            
            
            $dom = WebShopXmlUtils::getRequestSkeleton("WEBSHOPTRANZAKCIOLEKERDEZES", $variables);
            
            WebShopXmlUtils::addParameter($dom, $variables, "isClientCode", "WEBSHOP");
            WebShopXmlUtils::addParameter($dom, $variables, "isPOSID", $this->pos_id);
            WebShopXmlUtils::addParameter($dom, $variables, "isTransactionID", $tranzAzon);
            WebShopXmlUtils::addParameter($dom, $variables, "isClientSignature", $signature, $attrName, $attrValue);

            $inputXml = WebShopXmlUtils::xmlToString($dom);
            
            // tranzakció indítása
            $workflowState = SoapUtils::startWorkflowSynch("WEBSHOPTRANZAKCIOLEKERDEZES", $inputXml, $this->soap_client);

            $response = null;
            if ( !is_null($workflowState) )
                $response = new WResponse("WEBSHOPTRANZAKCIOLEKERDEZES", $workflowState);

            if ( $response->isFinished() ) {
                $responseDom = $response->getResponseDOM();
                $outputXML = WebShopXmlUtils::xmlToString($responseDom);
            }

            return $response->getAnswer()->webShopFizetesAdatok[0];
        }
    }
?>