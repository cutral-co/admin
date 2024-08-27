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

                if ($request->type === 'SW_EmitirCompPagoDeudaVenc' || $request->type === 'SW_EmitirCompPagoNoVenc') {
                    /*  return $this->getXMLRequestEmitirCompPagoDeudaVenc($request); */
                    $response =  $client->__doRequest($this->getXMLRequestEmitirCompPagoDeudaVenc($request), $options['location'], 'urn:uSWConsultaTributariaIntf-ISWConsultaTributaria#SW_ConsultarDeuda', 1);
                } else {
                    $response =  $client->__doRequest($this->getXMLRequest($request), $options['location'], 'urn:uSWConsultaTributariaIntf-ISWConsultaTributaria#SW_ConsultarDeuda', 1);
                }
                preg_match('/<return xsi:type="xsd:string">(.*?)<\/return>/s', $response, $matches);

                if (isset($matches[1])) {
                    // El contenido extraído está escapado, desescaparlo
                    $xmlContent = htmlspecialchars_decode($matches[1]);

                    if ($request->type === 'SW_EmitirCompPagoDeudaVenc') {
                        return sendResponse($xmlContent);
                    }
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
            $a = activity('error')->withProperties($e->getTrace())->log($e->getMessage());
            return sendResponse(null, $e->getMessage(), 301);
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
    }

    private function getXMLRequestEmitirCompPagoDeudaVenc(Request $request)
    {
        $attributes = $request->params;
        $key = env('KEY_TRIBUTARIA');

        $type = $request->type;
        $xml = new \SimpleXMLElement("
        <soapenv:Envelope
            xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
            xmlns:xsd='http://www.w3.org/2001/XMLSchema'
            xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'
            xmlns:urn='urn:uSWConsultaTributariaIntf-ISWConsultaTributaria'
        >
            <soapenv:Header/>
            <soapenv:Body>
                <urn:$type soapenv:encodingStyle='http://schemas.xmlsoap.org/soap/encoding/'>
                    <XMLGrupoNroComp xsi:type='xsd:string'>
                        <VFPDATA>
                        </VFPDATA>
                    </XMLGrupoNroComp>
                    <Key xsi:type='xsd:string'>{$key}</Key>
                </urn:$type>
            </soapenv:Body>
        </soapenv:Envelope>");

        $vfpData = $xml->xpath("//soapenv:Body/urn:$type/XMLGrupoNroComp/VFPDATA")[0];

        if ($request->type === 'SW_EmitirCompPagoDeudaVenc') {
            foreach ($attributes['DETALLEPERIODOSSELECCIONADOS'] as $detalle) {
                $detalleElement = $vfpData->addChild('DETALLEPERIODOSSELECCIONADOS');

                foreach ($detalle as $key => $value) {
                    $detalleElement->addChild($key, $value);
                }
            }
        } else if ($request->type === 'SW_EmitirCompPagoNoVenc') {
            foreach ($attributes['DETALLECOMPROBSELECCIONADOS'] as $detalle) {
                $detalleElement = $vfpData->addChild('DETALLECOMPROBSELECCIONADOS');

                foreach ($detalle as $key => $value) {
                    $detalleElement->addChild($key, $value);
                }
            }
        }

        return $xml->asXML();
    }
}
