<?php
    namespace App\Http\Controllers\Shop\Utils;

    use DomDocument;

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
        * DomDocument szöveges reprezentációja
        * 
        * @param DomDocument $dom 
        * 
        * @return string $dom->saveXML()
        */
        function xmlToString($dom) {
            return $dom->saveXML();
        }
    }
?>