<?php
    namespace taki47\otpszep;

    use Exception;
    use .\Utils/WebShopXmlUtils;
    use .\Utils\SignatureUtils;
    use .\Utils\SoapUtils;
    use .\Factories\WResponse;

    class Service {
        protected $customer_page_url = "https://www.otpbankdirekt.hu/webshop/do/webShopVasarlasInditas?posId={0}&azonosito={1}&nyelvkod={2}&version=5";
        protected $otp_mw_server_url = "https://www.otpbankdirekt.hu/mwaccesspublic12/mwaccess";
        protected $pos_id;
        protected $priv_key_fileName;
        protected $soap_client;

        function __construct()
        {
            $this->pos_id = env("POS_ID");
            $this->priv_key_fileName = env("PRIV_KEY_FILENAME");
            $this->soap_client = SoapUtils::createSoapClient($this->otp_mw_server_url);
        }

        function startWorkflow() {

        }

        function tranzakcioAzonositoGeneralas()
        {
            $dom = WebShopXmlUtils::getRequestSkeleton("WEBSHOPTRANZAZONGENERALAS", $variables);
            WebShopXmlUtils::addParameter($dom, $variables, "isClientCode", "WEBSHOP");
            WebShopXmlUtils::addParameter($dom, $variables, "isPOSID", $this->pos_id);

            $signatureFields = array(0 => $this->pos_id);
            $signatureText = SignatureUtils::getSignatureText($signatureFields);

            $pkcs8PrivateKey = SignatureUtils::loadPrivateKey($this->getPrivateKeyFile($this->priv_key_fileName));
            $signature = SignatureUtils::generateSignature($signatureText, $pkcs8PrivateKey);
            WebShopXmlUtils::addParameter($dom, $variables, "isClientSignature", $signature, "algorithm", "SHA512");

            $inputXml = WebShopXmlUtils::xmlToString($dom);

            $workflowState = SoapUtils::startWorkflowSynch("WEBSHOPTRANZAZONGENERALAS", $inputXml, $this->soap_client);
            $response = new WResponse("WEBSHOPTRANZAZONGENERALAS", $workflowState);
            
            if ($response->isFinished()) {
                $responseDom = $response->getResponseDOM(); 
                // ??? $this->lastOutputXml = WebShopWebShopXmlUtils::xmlToString($responseDom);
            }

            return $response;
        }

        function getPrivateKeyFile($keyFile)
        {
            $keyFile = base_path()."/".$keyFile;
            if ( file_exists($keyFile) )
                return $keyFile;

            return false;
        }
    }
?>