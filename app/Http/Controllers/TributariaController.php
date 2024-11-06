<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserPagoOnline;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class TributariaController extends Controller
{
    public function index(Request $request, $redirect = null)
    {
        try {
            $options = [
                'location' => env('URL_TRIBUTARIA'),
                'uri' => 'urn:uSWConsultaTributariaIntf-ISWConsultaTributaria',
                'trace' => 1,
                'exceptions' => true,
            ];

            try {
                $client = new \SoapClient(null, $options);

                if ($request->type === 'SW_EmitirCompPagoDeudaVenc' || $request->type === 'SW_EmitirCompPagoNoVenc') {
                    /*  return $this->getXMLRequestEmitirCompPagoDeudaVenc($request); */
                    $response =  $client->__doRequest($this->getXMLRequestEmitirComp($request), $options['location'], 'urn:uSWConsultaTributariaIntf-ISWConsultaTributaria#SW_ConsultarDeuda', 1);
                } else {
                    $response =  $client->__doRequest($this->getXMLRequest($request), $options['location'], 'urn:uSWConsultaTributariaIntf-ISWConsultaTributaria#SW_ConsultarDeuda', 1);
                }
                preg_match('/<return xsi:type="xsd:string">(.*?)<\/return>/s', $response, $matches);

                if (isset($matches[1])) {
                    // El contenido extraído está escapado, desescaparlo
                    $xmlContent = htmlspecialchars_decode($matches[1]);

                    if ($request->type === 'SW_EmitirCompPagoDeudaVenc' && !$request->method) {
                        return sendResponse($xmlContent);
                    }
                    // Cargar el XML limpio
                    $xmlObject = (object)simplexml_load_string($xmlContent);

                    if ($xmlObject === false) {
                        return sendResponse(null, 'Failed loading XML', 301);
                    }

                    if ($request->method) {
                        return $this->pagar_online($xmlObject);
                    }

                    if ($redirect) {
                        return redirect()->away($redirect);
                    }
                    return sendResponse($xmlObject);
                }
            } catch (\SoapFault $e) {
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

    private function getXMLRequestEmitirComp(Request $request)
    {
        $attributes = $request->params;
        $key = env('KEY_TRIBUTARIA');

        $pagoOnline = $request->method ? $request->method : '';

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
                    <Key xsi:type='xsd:string'>$key</Key>
                    <pPagoElectronico xsi:type='xsd:string'>$pagoOnline</pPagoElectronico>
                    <pIpCliente xsi:type='xsd:string'>127.0.0.1</pIpCliente>
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

    private function pagar_online($operacion)
    {
        try {
            $operacion = json_decode(json_encode($operacion), true);

            $opid = $operacion['opid'];
            $nro_comprobante = str_replace('/', '-', $operacion["comprobantes"]["comprobante"]["nro_comprobante"]);

            UserPagoOnline::create([
                'opid' => $opid,
                'nro_comprobante' => $nro_comprobante,
                'comprobante' => json_encode($operacion["comprobantes"]["comprobante"]),
                'importe' => $this->format_importe($operacion["comprobantes"]["comprobante"]['importe'])
            ]);
            return sendResponse($operacion);
        } catch (\Exception $e) {
            return sendResponse(null, $e->getMessage(), 301);
        }
    }

    public function pagar_online_mp(Request $request)
    {
        $operacion = $request->operacion;
        $userPagoOnline = UserPagoOnline::where('opid', $operacion['opid'])->first();

        $preferencia = [
            "external_reference" => $operacion['opid'],
            "items" => [
                [
                    "title" =>  $userPagoOnline->nro_comprobante,
                    "quantity" => 1,
                    "unit_price" => $userPagoOnline->importe
                ]
            ],
            "back_urls" => [
                "success" => "https://bb.cutralco.gob.ar/admin/public/api/mp/success",
                "failure" => "http://test.com/failure",
                "pending" => "http://test.com/pending"
            ],
            "auto_return" => "approved"
        ];

        MercadoPagoConfig::setAccessToken(env('MP_PRIVATE_TOKEN'));

        $client = new PreferenceClient();
        $preference = $client->create($preferencia);

        $userPagoOnline->mp_preference_id = $preference->id;
        $userPagoOnline->mp_preference = json_encode($preference);
        $userPagoOnline->save();

        return sendResponse($preference->init_point);
    }

    public function success(Request $request)
    {
        $userPagoOnline = UserPagoOnline::where('mp_preference_id', $request->preference_id)->first();
        $userPagoOnline->approved = json_encode($request->all());
        $userPagoOnline->save();

        $infoPago = "collection_id={$request->payment_id};";
        $infoPago .= "collection_status={$request->collection_status};";
        $infoPago .= "payment_id={$request->payment_id};";
        $infoPago .= "status={$request->status};";
        $infoPago .= "external_reference={$request->external_reference};";
        $infoPago .= "payment_type={$request->payment_type};";
        $infoPago .= "merchant_order_id={$request->merchant_order_id};";
        $infoPago .= "preference_id={$request->preference_id};";
        $infoPago .= "site_id={$request->site_id};";
        $infoPago .= "processing_mode={$request->processing_mode};";
        $infoPago .= "merchant_account_id={$request->merchant_account_id};";

        $newRequest = new Request([
            'type' => 'SW_RegistrarPago',
            'params' => [
                'pOpID' => "$request->opid",
                'pPagoElectronico' => 'mercadopago',
                'pEstado' => 'PAGADO',
                'pMensaje' => 'Prueba',
                'pMedioPago' => 'Mercado Pago',
                'pInfoPago' => $infoPago,
            ],
        ]);

        return $this->index($newRequest, 'https://www.cutralco.gob.ar/');
    }

    public function get_preferenicia(Request $request)
    {
        MercadoPagoConfig::setAccessToken(env('MP_PRIVATE_TOKEN'));

        $payment = new PaymentClient();
        $preference = $payment->get($request->id);

        return sendResponse($preference);
    }

    protected function format_importe($importe)
    {
        $numero = (int)$importe;

        return $numero / 100;
    }
}
