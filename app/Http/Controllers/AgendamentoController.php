<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\ErrorHandler\Debug;

class AgendamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $agendamentos = DB::select("SELECT

        ag.public_id AS agendamento_public_id,
        ag.data AS agendamento_data,
        ag.status AS agendamento_status,
        ag.motivo_extravio AS agendamento_motivo_extravio,
        bn.public_id AS bonus_public_id,
        bn.status AS bonus_status,
        bn.chave AS bonus_chave,
        bn.valor AS bonus_valor,
        bn.valor_percentual AS bonus_valor_percentual,
        bn.validade AS bonus_validade,
        bn.status AS bonus_status,
        bn.cliente_id AS bonus_cliente_id,
        bn.promocao_id AS bonus_promocao_id,
        bn.venda_id AS bonus_venda_id,
        prom.public_id AS promo_public_id,
        prom.fator_multiplicacao AS promo_fator_mult,
        prom.valor_minimo_compra AS promo_valor_min_compra,
        itd.public_id AS intervalo_disparo_public_id,
        itd.tempo AS intervalo_disparo_tempo,
        itd.texto AS intervalo_disparo_texto,
        itd.tipo_intervalo AS intervalo_disparo_tipo_intervalo,
        cli.public_id AS cliente_public_id,
        pes.public_id AS pessoa_public_id,
        pes.cpf AS pessoa_cpf,
        pes.telefone_celular AS pessoa_telefone_celular
    FROM mbn_agendamento AS ag
    INNER JOIN mbn_bonus AS bn ON bn.id LIKE ag.bonus_id
    INNER JOIN mbn_intervalo_disparo AS itd ON itd.id LIKE ag.intervalo_disparo_id
    INNER JOIN mbn_promocao AS prom ON prom.id LIKE bn.promocao_id
    INNER JOIN mbn_cliente AS cli ON cli.id LIKE bn.cliente_id
    INNER JOIN mbn_pessoa AS pes ON pes.id LIKE cli.pessoa_id
    WHERE CAST(ag.data AS DATE) LIKE CAST(GETDATE() AS DATE) AND ag.status LIKE 0 AND itd.tipo_intervalo LIKE 2
    ORDER BY ag.data_insert;");
        $all = array();
        foreach ($agendamentos as $key => $value) {
            $all[] = self::toObjectRequest($value);
        }

        return Response($all);
    }
    public function toObjectRequest($data)
    {

        return [
            'public_id' => $data->agendamento_public_id,
            'data' => $data->agendamento_data,
            'bonus' => [
                'public_id' => $data->bonus_public_id,
                'status' => $data->bonus_status,
                'utilizado' => '',
                'chave' => $data->bonus_chave,
                'valor' => $data->bonus_valor,
                'valor_percentual' => $data->bonus_valor_percentual,
                'cliente' => [
                    'public_id' => $data->cliente_public_id,
                    'pessoa' => [
                        'public_id' => $data->pessoa_public_id,
                        'cpf' => $data->pessoa_cpf,
                        'telefone_celular' => $data->pessoa_telefone_celular,

                    ],
                    'bloqueado' => '',
                    'bloqueios' => [],
                ],
            ],
            'intervalo_disparo' => [
                'public_id' => $data->intervalo_disparo_public_id,
                'tempo' => $data->intervalo_disparo_tempo,
                'texto' => $data->intervalo_disparo_texto,
                'tipo_intervalo' => $data->intervalo_disparo_tipo_intervalo,
            ],
            'tentativas' => '',
            'texto_personalizado' => self::geradorTexto('Joca pingo', $data->bonus_valor, $data->promo_fator_mult, $data->bonus_valor, $data->bonus_valor_percentual ?? '', $data->bonus_validade, 'cm rio', $data->bonus_chave, $data->promo_valor_min_compra ?? '', $data->intervalo_disparo_texto),
            'status' => $data->agendamento_status,
            'motivo_extravio' => $data->agendamento_motivo_extravio,
            'SENDER_SMS' => 'DCL'
        ];
    }

    public function toCurrency($number)
    {
        return number_format(floatval($number), 2, ',', '.');
    }
    public function geradorTexto(
        string $nome_cliente,
        string $valor,
        $fato_multi,
        string $valor_fixo,
        string $valor_percentual,
        string $validade,
        string $loja,
        string $chave,
        string $valor_minimo_compra,
        string $texto
    ) {
        $valor_min = 0;
        if ($valor && $fato_multi) {
            $valor_min = floatval($valor) * floatval($fato_multi);
        } else {
            $valor_min = $valor_minimo_compra;
        }

        $final_text = "";
        $final_text = str_replace("{nome_cliente}", $nome_cliente, $texto);
        $final_text = str_replace("{valor}",self::toCurrency( $valor), $final_text);
        $final_text = str_replace("{valor_fixo}",self::toCurrency( $valor_fixo), $final_text);
        $final_text = str_replace("{valor_percentual}", self::toCurrency( $valor_percentual), $final_text);
        $final_text = str_replace("{validade}", date_format(new DateTime($validade),'d/m'), $final_text);
        $final_text = str_replace("{loja}", $loja, $final_text);
        $final_text = str_replace("{chave}", $chave, $final_text);
        $final_text = str_replace("{valor_minimo}", self::toCurrency( $valor_min), $final_text);

        return $final_text;
        // class PalavrasChaves(models.TextChoices):
        // NOME_CLIENTE = "nome_cliente", "cliente.pessoa.nome"
        // VALOR = "valor", "valor || valor_percentual"
        // VALOR_FIXO = "valor_fixo", "valor"
        // VALOR_PERCENTUAL = "valor_percentual", "valor_percentual"
        // VALIDADE = "validade", "validade"
        // LOJA = "loja", "venda.loja.nome_fantasia"
        // CHAVE = "chave", "chave"
        // VALOR_MINIMO = "valor_minimo", "valor_minimo"
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
