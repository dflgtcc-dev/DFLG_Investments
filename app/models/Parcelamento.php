<?php

namespace app\models;

class Parcelamento
{
    private int $id;
    private int $usuarioId;
    private ?int $categoriaId;
    private string $descricao;
    private float $valorParcela;
    private int $quantidadeParcelas;
    private int $parcelasPagas;
    private string $dataPrimeiraParcela;
    private ?string $nomeCategoria;
    private ?string $tag;
    private float $valorPago;

    public function __construct(
        int $id,
        int $usuarioId,
        ?int $categoriaId,
        string $descricao,
        float $valorParcela,
        int $quantidadeParcelas,
        int $parcelasPagas = 0,
        string $dataPrimeiraParcela = '',
        ?string $nomeCategoria = null,
        ?string $tag = null,
        float $valorPago = 0.0
    ) {
        $this->id = $id;
        $this->usuarioId = $usuarioId;
        $this->categoriaId = $categoriaId;
        $this->descricao = $descricao;
        $this->valorParcela = $valorParcela;
        $this->quantidadeParcelas = $quantidadeParcelas;
        $this->parcelasPagas = $parcelasPagas;
        $this->dataPrimeiraParcela = $dataPrimeiraParcela;
        $this->nomeCategoria = $nomeCategoria;
        $this->tag = $tag;
        $this->valorPago = $valorPago;
    }

    public function getId(): int { return $this->id; }
    public function getUsuarioId(): int { return $this->usuarioId; }
    public function getCategoriaId(): ?int { return $this->categoriaId; }
    public function getDescricao(): string { return $this->descricao; }
    public function getValorParcela(): float { return $this->valorParcela; }
    public function getQuantidadeParcelas(): int { return $this->quantidadeParcelas; }
    public function getDataPrimeiraParcela(): string { return $this->dataPrimeiraParcela; }
    public function getNomeCategoria(): ?string { return $this->nomeCategoria; }
    public function getTag(): ?string { return $this->tag; }

    // parcelas pagas = parcelas lançadas pelo botão "Lançar parcela"
    public function getParcelasPagas(): int { return $this->parcelasPagas; }

    // soma do que já foi lançado (as parcelas lançadas guardam o valor da época)
    public function getValorPago(): float { return round($this->valorPago, 2); }

    public function getParcelasRestantes(): int
    {
        return max($this->quantidadeParcelas - $this->parcelasPagas, 0);
    }

    // o que já foi pago mais o que falta no valor atual da parcela
    public function getValorTotal(): float
    {
        return round($this->valorPago + $this->valorParcela * $this->getParcelasRestantes(), 2);
    }

    public function getValorRestante(): float
    {
        return round($this->valorParcela * $this->getParcelasRestantes(), 2);
    }

    public function getProgresso(): float
    {
        if ($this->quantidadeParcelas <= 0) {
            return 0.0;
        }

        return min(100.0, round(($this->parcelasPagas / $this->quantidadeParcelas) * 100, 2));
    }

    public function eQuitado(): bool
    {
        return $this->parcelasPagas >= $this->quantidadeParcelas;
    }

    // número da próxima parcela a lançar (null se quitado)
    public function getNumeroProximaParcela(): ?int
    {
        return $this->eQuitado() ? null : $this->parcelasPagas + 1;
    }

    // vencimento da parcela N (1 = primeira). Não passa do fim do mês:
    // uma compra no dia 31 vence em 28/02 (ou 29/02), 30/04 etc., e volta ao dia 31 quando o mês tem 31 dias
    public function getDataVencimento(int $numero): string
    {
        $primeira = new \DateTimeImmutable($this->dataPrimeiraParcela);
        $dia = (int) $primeira->format('j');
        $inicioMes = $primeira->modify('first day of this month')->modify('+' . ($numero - 1) . ' months');
        $diasNoMes = (int) $inicioMes->format('t');

        return $inicioMes->setDate(
            (int) $inicioMes->format('Y'),
            (int) $inicioMes->format('n'),
            min($dia, $diasNoMes)
        )->format('Y-m-d');
    }

    public function getDataProximaParcela(): ?string
    {
        $numero = $this->getNumeroProximaParcela();

        return $numero === null ? null : $this->getDataVencimento($numero);
    }

    // quantas parcelas já venceram e ainda não foram lançadas
    public function getParcelasAtrasadas(?string $hoje = null): int
    {
        $hoje = $hoje ?? date('Y-m-d');
        $atrasadas = 0;

        for ($numero = $this->parcelasPagas + 1; $numero <= $this->quantidadeParcelas; $numero++) {
            if ($this->getDataVencimento($numero) >= $hoje) {
                break;
            }
            $atrasadas++;
        }

        return $atrasadas;
    }

    // descrição gravada em cada parcela lançada, ex.: "Notebook (3/10)"
    public function getDescricaoParcela(int $numero): string
    {
        return $this->descricao . " ({$numero}/{$this->quantidadeParcelas})";
    }

    public static function arrayParaObjeto(array $parcelamento): self
    {
        $valorParcela = $parcelamento['valor_parcela'] ?? null;
        if ($valorParcela === null) {
            $valorParcela = (float) $parcelamento['valor_total'] / max((int) $parcelamento['quantidade_parcelas'], 1);
        }

        return new self(
            (int) $parcelamento['id'],
            (int) $parcelamento['usuario_id'],
            $parcelamento['categoria_id'] !== null ? (int) $parcelamento['categoria_id'] : null,
            $parcelamento['descricao'],
            (float) $valorParcela,
            (int) $parcelamento['quantidade_parcelas'],
            (int) ($parcelamento['parcelas_pagas'] ?? 0),
            $parcelamento['data_primeira_parcela'],
            $parcelamento['nome_categoria'] ?? null,
            $parcelamento['tag'] ?? null,
            (float) ($parcelamento['valor_pago'] ?? 0)
        );
    }
}
