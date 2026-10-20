<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Validation\ValidationException;

class ProdutoService
{

    public function create(array $dados)
    {
        $this->verificarMargem($dados['preco'], $dados['preco_custo']);

        return Produto::create($dados);
    }

    private function verificarMargem(float $preco, float $custo)
    {
        $valor = $custo + (0.1 * $custo);
        $preco_minimo = round($valor, 2);

        if($preco < $preco_minimo){
            throw ValidationException::withMessages([
                'preco' => sprintf('O preço deve ser mais do que 10 por cento do valor de custo: %.2f', $preco_minimo),
            ]);
        }
    }
}