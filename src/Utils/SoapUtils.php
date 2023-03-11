<?php
    namespace App\Http\Controllers\Shop\Utils;

    use SoapClient;
    use SoapParam;
    use SoapVar;

    class SoapUtils {
        function createSoapClient($otp_mv_server_url) {
            /**
            * @desc A banki felülethez illeszkedő SOAP kliens létrehozása.
            * A kliensen beállított socket_timeout 660 másodperc azért,
            * hogy a háromszereplős fizetésekhez kapcsolódó kommunikációs szálak
            * se szakadjanak meg.
            * 
            * @param array $properties kapcsolódási paraméterek (javasolt
            * a otp_webshop_client.conf fájl teljes tartalma)
            * 
            * @return SoapClient A banki felülethez illeszkedő SOAP kliens.
            */
            $soapClientProps = array (   
                'location' => $otp_mv_server_url,
                'uri' => 'java:hu.iqsoft.otp.mw.access',
                'trace' => true,
                'exceptions' => 1,
                'connection_timeout' => 10,
                'default_socket_timeout' => 660);
                
            return new SoapClient(NULL, $soapClientProps);      
        }


        function startWorkflowSynch($workflowName, $inputXml, $soapClient) {
        
            $workflowState = NULL;
            $retryCount = 0;
            $resendAllowed = true;
    
            /* A háromszereplős fizetési tranzakció esetén
               a process futási ideje a 10 percet is meghaladhatja
               (10 perc a fizetési timeout, további pár másodperc
               a kommunikációs overhead)   */
            ini_set('max_execution_time','660');
            
            do {
                try {
                    $workflowState = $soapClient->__soapCall(
                        "startWorkflowSynch", 
                        array( 
                            new SoapParam(new SoapVar($workflowName, XSD_STRING), "arg0"), 
                            new SoapParam(new SoapVar($inputXml, XSD_STRING), "arg1")),
                        array('soapaction' => "urn:startWorkflowSynch"));
                    
                    $resendAllowed = false;
                }
                catch (SoapFault $sf) {
                    $resendAllowed = false;
                    if ($retryCount < RESENDCOUNT) {
                        if (stristr($sf->getMessage(), "Maximum workflow number is reached") !== false) {
                            // Pillanatnyi túlterhelés miatti visszautasítás a banki oldalon
                            $resendAllowed = true;
                            sleep(1);
                        } 
                    }
                }
                catch (Exception $e) {
                    $resendAllowed = false;
                }
                
            } while ($resendAllowed && $retryCount++ < 10);
            
            return $workflowState;
        }
    }
?>