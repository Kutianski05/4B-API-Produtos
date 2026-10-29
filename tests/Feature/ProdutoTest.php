<?php

namespace Tests\Feature;

use App\Models\Produto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProdutoTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_produto_com_sucesso(): void
    {
        $this->postJson('/api/produtos', [
            'nome' => 'Lajota',
            'preco_custo' => 100,
            'preco' => 110,
        ])->assertCreated();

        $this->assertDatabaseHas('produtos', [
            'nome' => 'Lajota']);
    }

    private function criarProduto(array $dados = []): Produto
    {
        return Produto::create(array_merge([
            'nome' => 'Produto Teste',
            'preco_custo' => 100,
            'preco' => 150,
            'quantidade' => 10,
        ], $dados));
    }

    public function test_filtra_produtos_por_preco_maximo(): void
    {
        $this->criarProduto(['nome' => 'Barato', 'preco' => 120]);
        $this->criarProduto(['nome' => 'Caro', 'preco' => 500]);

        $this->getJson('/api/produtos?preco_max=200')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Barato');
    }

    public function test_filtra_produtos_em_estoque(): void
    {
        $this->criarProduto(['nome' => 'Com estoque', 'quantidade' => 5]);
        $this->criarProduto(['nome' => 'Sem estoque', 'quantidade' => 0]);

        $this->getJson('/api/produtos?em_estoque=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Com estoque');

        $this->getJson('/api/produtos?em_estoque=0')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nome', 'Sem estoque');
    }

    public function test_atualiza_produto_com_sucesso(): void
    {
        $produto = $this->criarProduto();

        $this->putJson("/api/produtos/{$produto->id}", [
            'nome' => 'Nome Atualizado',
            'preco' => 200,
        ])->assertOk()
            ->assertJsonPath('data.nome', 'Nome Atualizado');

        $this->assertDatabaseHas('produtos', [
            'id' => $produto->id,
            'nome' => 'Nome Atualizado',
        ]);
    }

    public function test_nao_atualiza_produto_com_margem_menor_que_10_por_cento(): void
    {
        $produto = $this->criarProduto(['preco_custo' => 100, 'preco' => 150]);

        $this->putJson("/api/produtos/{$produto->id}", ['preco' => 105])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('preco');
    }

    public function test_remove_produto_com_sucesso(): void
    {
        $produto = $this->criarProduto();

        $this->deleteJson("/api/produtos/{$produto->id}")->assertNoContent();

        $this->assertDatabaseMissing('produtos', ['id' => $produto->id]);
    }
}