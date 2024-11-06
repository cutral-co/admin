<?php

namespace App\Http\Controllers\Eventos;

use App\Http\Controllers\Controller;
use App\Models\Eventos\E202411Registros;
use Illuminate\Http\Request;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

class RegistroController extends Controller
{
    /**
     * Para obtener todas las instancias de un modelo.
     */
    public function index()
    {
        try {
        } catch (\Exception $e) {
            $log = saveLog($e->getMessage(), get_class() . '::' . __FUNCTION__, $e->getTrace());
            return log_send_response($log);
        }
    }

    /**
     * Para guardar una nueva instancia.
     */
    public function store(Request $request)
    {
        try {
            $registro = E202411Registros::create(
                $request->only([
                    'lastname',
                    'name',
                    'dni',
                    'email',
                    'phone',
                ])
            );
            $ev_id = "01";

            $opid = "$ev_id-$registro->dni-$registro->id";
            $registro->opid = $opid;
            $registro->nro_comprobante = $opid;

            $uniqid = uniqid();
            $registro->hash = "$ev_id-$uniqid-$registro->id";

            $registro = $this->create_mp_preference($registro);

            return sendResponse($registro);
        } catch (\Exception $e) {
            return sendResponse(null, $e->getMessage());
        }
    }

    private function create_mp_preference(E202411Registros $registro)
    {
        $preferencia = [
            "external_reference" => $registro->opid,
            "items" => [
                [
                    "title" =>  'Evento 2024-11',
                    "quantity" => 1,
                    "unit_price" => 4815.54
                ]
            ],

            /* https://www.tuweb.com/success?collection_id=123456789&collection_status=approved&external_reference=null&payment_type=credit_card&merchant_order_id=987654321z */
            "redirect_urls" => array(
                "success" => "https://bb.cutralco.gob.ar/admin/public/api/evento_202411/mp/success",
                "failure" => "http://test.com/failure",
                "pending" => "http://test.com/pending"
            ),
        ];

        MercadoPagoConfig::setAccessToken(env('MP_PRIVATE_TOKEN'));

        $client = new PreferenceClient();
        $preference = $client->create($preferencia);

        $registro->mp_preference_id = $preference->id;
        $registro->mp_preference = json_encode($preference);
        $registro->save();

        return $preference->init_point;
    }

    public function success(Request $request)
    {
        activity()->withProperties($request->all())->log('asdasd');
    }
}
