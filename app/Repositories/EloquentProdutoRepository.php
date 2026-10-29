<?php

namespace App\Repositories;

use App\Models\Produto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProdutoRepository implements ProdutoRepositoryInterface
{
    public function create(array $dados): Produto
    {
        return Produto::create($dados);
    }

    public function update(Produto $produto, array $dados): Produto
    {
        $produto->update($dados);

        return $produto->refresh();
    }

    public function delete(Produto $produto): void
    {
        $produto->delete();
    }

    public function search(array $filtros = [], int $porPagina = 10): LengthAwarePaginator
    {
        return Produto::query()
            ->when(
                $filtros['search'] ?? null,
                fn ($query, $pedaco) => $query->where('nome', 'LIKE', "%{$pedaco}%")
            )
            ->when(
                isset($filtros['preco_max']),
                fn ($query) => $query->where('preco', '<=', $filtros['preco_max'])
            )
            ->when(
                isset($filtros['em_estoque']),
                fn ($query) => $filtros['em_estoque']
                    ? $query->where('quantidade', '>', 0)
                    : $query->where('quantidade', '<=', 0)
            )
            ->paginate($porPagina);
    }
}