<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SoapClient;
use SoapFault;

class TributariaController extends Controller
{
    public function index(Request $request)
    {
        try {
            $options = [
                'location' => env('URL_TRIBUTARIA'),
                'uri' => 'urn:uSWConsultaTributariaIntf-ISWConsultaTributaria',
                'trace' => 1,
                'exceptions' => true,
            ];

            try {
                $client = new SoapClient(null, $options);

                $response =  $client->__doRequest($this->getXMLRequest($request), $options['location'], 'urn:uSWConsultaTributariaIntf-ISWConsultaTributaria#SW_ConsultarDeuda', 1);
                preg_match('/<return xsi:type="xsd:string">(.*?)<\/return>/s', $response, $matches);

                if (isset($matches[1])) {
                    // El contenido extraído está escapado, desescaparlo
                    $xmlContent = htmlspecialchars_decode($matches[1]);

                    // Cargar el XML limpio
                    $xmlObject = simplexml_load_string($xmlContent);

                    if ($xmlObject === false) {
                        return sendResponse(null, 'Failed loading XML', 301);
                    }

                    return sendResponse($xmlObject);
                }
            } catch (SoapFault $e) {
                return sendResponse(null, $e->getMessage(), 301);
            }
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    private function getXMLRequest(Request $request)
    {
        $attributes = $request->params;
        $attributes['Key'] = env('KEY_TRIBUTARIA');

        $xml = new \SimpleXMLElement("
        <soapenv:Envelope
            xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
            xmlns:xsd='http://www.w3.org/2001/XMLSchema'
            xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'
            xmlns:urn='urn:uSWConsultaTributariaIntf-ISWConsultaTributaria'
        >
            <soapenv:Header/>
            <soapenv:Body>
                <urn:$request->type soapenv:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'/>
            </soapenv:Body>
        </soapenv:Envelope>");

        // Access the Body element
        $body = $xml->xpath("//soapenv:Body/urn:$request->type")[0];

        // Add attributes dynamically
        foreach ($attributes as $key => $value) {
            $body->addChild($key, $value, 'urn');
        }

        // Output the XML to verify
        Header('Content-type: text/xml');
        return $xml->asXML();

        /* $requestBody = <<<XML
            <soapenv:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:uSWConsultaTributariaIntf-ISWConsultaTributaria">
                <soapenv:Header/>
                <soapenv:Body>
                    <urn:SW_ConsultarDeuda soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
                        <pTipoImponible xsi:type="xsd:string">E</pTipoImponible>
                        <pNroImponible xsi:type="xsd:string">527387</pNroImponible>
                        <pFechaAct xsi:type="xsd:string">15/04/2024</pFechaAct>
                        <pParaInforme xsi:type="xsd:string">N</pParaInforme>
                        <Key xsi:type="xsd:string">11111111110000000000</Key>
                    </urn:SW_ConsultarDeuda>
                </soapenv:Body>
            </soapenv:Envelope>
            XML; */
    }
}
