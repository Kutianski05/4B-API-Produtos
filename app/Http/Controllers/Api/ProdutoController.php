<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use App\Services\ProdutoService;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function __construct(private ProdutoService $service) {}

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string',
            'preco_max' => 'nullable|numeric|min:0',
            'em_estoque' => 'nullable|boolean',
        ]);

        $filtros = [
            'search' => $request->query('search'),
            'preco_max' => $request->query('preco_max'),
            'em_estoque' => $request->has('em_estoque') ? $request->boolean('em_estoque') : null,
        ];

        return ProdutoResource::collection($this->service->listar($filtros));
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => 'nullable|string',
            'preco_custo' => ['required', 'numeric', 'min:0'],
            'preco' => ['required', 'numeric', 'min:0'],
            'quantidade' => 'sometimes|integer|min:0',
        ]);

        $produto = $this->service->create($dados);

        return new ProdutoResource($produto);
    }

    public function show(Produto $produto)
    {
        return new ProdutoResource($produto);
    }

    public function update(Request $request, Produto $produto)
    {
        $dados = $request->validate([
            'nome' => ['sometimes', 'string', 'max:255'],
            'descricao' => 'nullable|string',
            'preco_custo' => ['sometimes', 'numeric', 'min:0'],
            'preco' => ['sometimes', 'numeric', 'min:0'],
            'quantidade' => 'sometimes|integer|min:0',
        ]);

        $produto = $this->service->atualizar($produto, $dados);

        return new ProdutoResource($produto);
    }

    public function destroy(Produto $produto)
    {
        $this->service->remover($produto);

        return response()->noContent();
    }
}