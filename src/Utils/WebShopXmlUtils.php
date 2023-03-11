<?php
    namespace taki47\otpszep\Utils;

    use DomDocument;
    use DOMXPath;

    class WebShopXmlUtils {
        /**
        * @desc Banki tranzakcióhoz tartozó üres input xml váz létrehozása:
        * <StartWorkflow>
        *   <TemplateName>$templateName</TemplateName>
        *   <Variables/>
        * </StartWorkflow>
        * 
        * @param string $templateName az indíandó tranzakció neve (szöveges kódja)
        * @param DomNode $variables referencia az input xml-ben létrehozott
        * Varaibles tag-re.
        * 
        * @return DomDocument a létrehozott objektum
        */
        function getRequestSkeleton($templateName, &$variables) {
            $dom = new DomDocument('1.0', "UTF-8");
            $root = $dom->createElement('StartWorkflow');
            $dom->appendChild($root);
    
            $root->appendChild($dom->createElement('TemplateName', $templateName));
    
            $variables = $dom->createElement('Variables');
            $root->appendChild($variables);
    
            return $dom;
        }

        /**
        * @desc Banki tranzakcióhoz tartozó input xml kiegészítése egy 
        * input változó értékkel.
        * 
        * @param DomDocument $dom maga az input xml
        * @param DomNode $variables az xml Variables tag-je
        * @param string name a beillesztendő változó neve
        * @param string value a beillesztendő változó értéke.
        * @param string attributeName a változóhoz esetlegesen hozzáadandó attribútum neve
        * @param string attributeValue a változóhoz esetlegesen hozzáadandó attribútum értéke
        */
        function addParameter($dom, $variables, $name, $value, $attributeName = null, $attributeValue = null) {
            $node = null;
            if (is_bool($value)) {
                $value = $value ? TRUE : FALSE;
            }
            
            if (!is_null($value)) {
                $value = iconv("ISO-8859-2", $dom->actualEncoding, $value);            
            }
            
            $attribute = null;
            
            if ($dom->documentElement->namespaceURI) {
                $node = $dom->createElementNS($dom->documentElement->namespaceURI, $name);
                $node->prefix = $dom->documentElement->prefix;
                
                if (!empty($attributeName) && !empty($attributeValue)) {
                    $attribute = $dom->createAttributeNS($attributeName);
                    $attribute->prefix = $dom->documentElement->prefix;
                }
                
            }
            else {
                $node = $dom->createElement($name);
                
                if (!empty($attributeName) && !empty($attributeValue)) {
                    $attribute = $dom->createAttribute($attributeName);
                }
            }
            
            if (!is_null($attribute)) {
                $attribute->value = $attributeValue;
                $node->appendChild($attribute);
            }
            
            $node->appendChild($dom->createTextNode($value));
            
            $variables->appendChild($node);
        }

        /**
        * @desc A banki tranzakció output xml-jének értelmezése, 
        * adott WResponse objektum feltöltése.
        * 
        * @param string $responseStr output xml szövege
        * @param WResponse feltöltendő response objektum
        */
        function parseOutputXml ($responseStr, $wresponse) {
            $responseStrDecoded = NULL;
            
            $responseStrDecoded = base64_decode($responseStr, true);
            
            $wresponse->response = $responseStrDecoded !== FALSE ? $responseStrDecoded : $responseStr;
            $wresponse->responseDOM = new DomDocument();
            
            $wresponse->responseDOM->loadXML($wresponse->response);
            
            $path = new DOMXPath($wresponse->responseDOM);
            
            // Válaszkódok listájának előállítása
            $wresponse->hasSuccessfulAnswer = false;
            $messageElements = $path->query('//answer/messagelist/message');
            for ($i = 0; $i < $messageElements->length; $i++) {
                $messageElement = $messageElements->item($i);
                $message = $messageElement->nodeValue;
                $wresponse->messages[] = $message;
                if ($message != "SIKERESWEBSHOPFIZETESINDITAS ") {
                    $wresponse->errors[] = $message;
                }
                else {
                    $wresponse->hasSuccessfulAnswer = true;
                }
            }

            // Tájékoztató kódok listájának előállítása
            $infoElements = $path->query('//answer/infolist/info');
            for ($i = 0; $i < $infoElements->length; $i++) {
                $infoElement = $infoElements->item($i);
                $info = $infoElement->nodeValue;
                $wresponse->infos[] = $info;
            }
        }

        /**
        * @desc XPath kifejezés kiértékelése, mely egy
        * adott elemtől indul és egy elemre vonatkozik. 
        * Lista esetén az első elem kerül a válaszba.
        * 
        * @param DOMDocument / DOMNode $node a kiértékelés helye
        * @param string $xpath xpath kifejezés
        */
        function getNodeByXPath($node, $xpath) {
            $doc = NULL;
            if (is_a($node, 'DOMDocument')) {
                $doc = $node;
                $node = $node->documentElement;   
            }
            else {
                $doc = $node->ownerDocument;
            }
            
            $path = new DOMXPath($doc);
            $record = $path->query($xpath, $node);

            if (is_a($record, 'DOMNodeList') && ($record->length > 0)) 
                $record = $record->item(0);

            return $record;
        }

        /**
        * DomDocument szöveges reprezentációja
        * 
        * @param DomDocument $dom 
        * 
        * @return string $dom->saveXML()
        */
        function xmlToString($dom) {
            return $dom->saveXML();
        }

        /**
        * @desc Adott xml node adott nevű child node-ja szöveges 
        * tartalmának lekérdezése. Az eredmény összefűzve tartalmazza 
        * az XML_TEXT_NODE típusú child node-k értékét.
        * 
        * @param DomNode $record a szülő node
        * @param string $childNode a child node neve
        * 
        * @return string a child node szöveges tartalma
        */
        function getElementText($record, $childName) {
            $result = NULL;
            $childNode = self::getChildElement($record, $childName);
            if (!is_null($childNode)) $result = $childNode->textContent;
            return iconv($record->ownerDocument->actualEncoding, "ISO-8859-2", $result);
        }

        /**
        * @desc Adott xml node első adott nevű child node-jának lekérdezése.
        * 
        * @param DomNode $record
        * @param string $childName
        * 
        * @return DomNode az adott nevű Node / Element vagy NULL
        */
        function getChildElement($record, $childName) {
            $result = NULL;
            $childNodes = $record->childNodes;
            for($i = 0; !is_null($childNodes) && $i<= $childNodes->length && is_null($result); $i++){
                $item = $childNodes->item($i);
                if ( $item && $item->nodeName == $childName ) 
                    $result = $childNodes->item($i);
            }
            return $result;
        }
    }
?>