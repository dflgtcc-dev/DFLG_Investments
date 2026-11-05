<?php

namespace app\services;

class CalculadoraService
{
    private const LIMITE_MESES = 1200; // 100 anos

    private array $erros = [];

    public function getCalculadoras(): array
    {
        return [
            'juros-compostos' => [
                'nome' => 'Juros compostos com aportes',
                'descricao' => 'Veja quanto seu dinheiro rende com um valor inicial e aportes mensais.',
                'icone' => 'bi-graph-up-arrow',
            ],
            'aporte-meta' => [
                'nome' => 'Aporte para atingir uma meta',
                'descricao' => 'Descubra quanto guardar por mês para chegar ao valor que você quer.',
                'icone' => 'bi-bullseye',
            ],
            'tempo-meta' => [
                'nome' => 'Tempo para atingir uma meta',
                'descricao' => 'Descubra em quanto tempo você chega ao valor que quer com seus aportes.',
                'icone' => 'bi-hourglass-split',
            ],
            'independencia-financeira' => [
                'nome' => 'Independência financeira',
                'descricao' => 'Descubra quanto patrimônio você precisa para viver de renda e em quanto tempo chega lá.',
                'icone' => 'bi-piggy-bank',
            ],
            'aposentadoria' => [
                'nome' => 'Planejamento de aposentadoria',
                'descricao' => 'Veja o patrimônio e a renda mensal que você pode ter ao se aposentar.',
                'icone' => 'bi-sunset',
            ],
            'reserva-emergencia' => [
                'nome' => 'Reserva de emergência',
                'descricao' => 'Calcule o valor da sua reserva e em quanto tempo você consegue montá-la.',
                'icone' => 'bi-shield-check',
            ],
            'valor-real' => [
                'nome' => 'Valor real (inflação)',
                'descricao' => 'Veja quanto a inflação reduz o poder de compra do seu dinheiro ao longo do tempo.',
                'icone' => 'bi-graph-down-arrow',
            ],
            'comparador-cenarios' => [
                'nome' => 'Comparador de cenários',
                'descricao' => 'Compare dois investimentos lado a lado e veja qual termina com mais dinheiro.',
                'icone' => 'bi-bar-chart-line',
            ],
            'rendimento-investimento' => [
                'nome' => 'Quanto meu investimento rendeu',
                'descricao' => 'Descubra a rentabilidade e a taxa média de um investimento que você já fez.',
                'icone' => 'bi-percent',
            ],
            'juros-simples' => [
                'nome' => 'Juros simples',
                'descricao' => 'Calcule os juros simples e compare com o resultado em juros compostos.',
                'icone' => 'bi-cash-stack',
            ],
        ];
    }

    public function calcularJurosCompostos(array $dados): array|false
    {
        $this->erros = [];

        $valorInicial = $this->valor($dados, 'valorInicial', 'Informe um valor inicial válido.');
        $aporteMensal = $this->valor($dados, 'aporteMensal', 'Informe um aporte mensal válido.');
        $taxaMensal = $this->taxaMensal($dados);
        $prazoMeses = $this->prazoMeses($dados);

        if (!empty($this->erros)) {
            return false;
        }

        $montanteFinal = $this->montante($valorInicial, $aporteMensal, $taxaMensal, $prazoMeses);

        if (!is_finite($montanteFinal)) {
            $this->erros['geral'] = 'Os valores informados geram um resultado grande demais.';

            return false;
        }

        $totalInvestido = $valorInicial + ($aporteMensal * $prazoMeses);

        return array_merge($this->resumoTaxa($taxaMensal), [
            'totalInvestido' => $totalInvestido,
            'rendimento' => $montanteFinal - $totalInvestido,
            'montanteFinal' => $montanteFinal,
        ]);
    }

    public function calcularAporteMeta(array $dados): array|false
    {
        $this->erros = [];

        $valorAlvo = $this->valor($dados, 'valorAlvo', 'Informe um valor alvo maior que zero.', true);
        $valorInicial = $this->valor($dados, 'valorInicial', 'Informe um valor inicial válido.');
        $taxaMensal = $this->taxaMensal($dados);
        $prazoMeses = $this->prazoMeses($dados);

        if (!empty($this->erros)) {
            return false;
        }

        $fator = pow(1 + $taxaMensal, $prazoMeses);
        $falta = $valorAlvo - ($valorInicial * $fator);

        if ($falta <= 0) {
            // o valor inicial sozinho já chega na meta
            $aporteMensal = 0.0;
        } else {
            $aporteMensal = $taxaMensal > 0
                ? $falta * $taxaMensal / ($fator - 1)
                : $falta / $prazoMeses;
        }

        $montanteFinal = $this->montante($valorInicial, $aporteMensal, $taxaMensal, $prazoMeses);

        if (!is_finite($montanteFinal)) {
            $this->erros['geral'] = 'Os valores informados geram um resultado grande demais.';

            return false;
        }

        $totalInvestido = $valorInicial + ($aporteMensal * $prazoMeses);

        return array_merge($this->resumoTaxa($taxaMensal), [
            'aporteMensal' => $aporteMensal,
            'metaJaAtingida' => $falta <= 0,
            'totalInvestido' => $totalInvestido,
            'rendimento' => $montanteFinal - $totalInvestido,
            'montanteFinal' => $montanteFinal,
        ]);
    }

    public function calcularTempoMeta(array $dados): array|false
    {
        $this->erros = [];

        $valorAlvo = $this->valor($dados, 'valorAlvo', 'Informe um valor alvo maior que zero.', true);
        $valorInicial = $this->valor($dados, 'valorInicial', 'Informe um valor inicial válido.');
        $aporteMensal = $this->valor($dados, 'aporteMensal', 'Informe um aporte mensal válido.');
        $taxaMensal = $this->taxaMensal($dados);

        if (!empty($this->erros)) {
            return false;
        }

        $meses = $this->mesesParaMeta($valorAlvo, $valorInicial, $aporteMensal, $taxaMensal);

        if ($meses === null) {
            return false;
        }

        $montanteFinal = $this->montante($valorInicial, $aporteMensal, $taxaMensal, $meses);
        $totalInvestido = $valorInicial + ($aporteMensal * $meses);

        return array_merge($this->resumoTaxa($taxaMensal), [
            'meses' => $meses,
            'anos' => intdiv($meses, 12),
            'mesesRestantes' => $meses % 12,
            'metaJaAtingida' => $meses === 0,
            'totalInvestido' => $totalInvestido,
            'rendimento' => $montanteFinal - $totalInvestido,
            'montanteFinal' => $montanteFinal,
        ]);
    }

    public function calcularIndependenciaFinanceira(array $dados): array|false
    {
        $this->erros = [];

        $rendaMensal = $this->valor($dados, 'rendaMensal', 'Informe uma renda mensal maior que zero.', true);
        $patrimonioAtual = $this->valor($dados, 'patrimonioAtual', 'Informe um patrimônio atual válido.');
        $aporteMensal = $this->valor($dados, 'aporteMensal', 'Informe um aporte mensal válido.');
        $taxaMensal = $this->taxaMensal($dados);

        if ($taxaMensal === 0.0) {
            $this->erros['taxa'] = 'Informe uma taxa maior que zero.';
        }

        if (!empty($this->erros)) {
            return false;
        }

        // patrimônio que, rendendo a taxa informada, gera a renda mensal sem gastar o principal
        $patrimonioNecessario = $rendaMensal / $taxaMensal;

        $meses = $this->mesesParaMeta($patrimonioNecessario, $patrimonioAtual, $aporteMensal, $taxaMensal);

        if ($meses === null) {
            return false;
        }

        $montanteFinal = $this->montante($patrimonioAtual, $aporteMensal, $taxaMensal, $meses);
        $totalInvestido = $patrimonioAtual + ($aporteMensal * $meses);

        return array_merge($this->resumoTaxa($taxaMensal), [
            'patrimonioNecessario' => $patrimonioNecessario,
            'falta' => max(0, $patrimonioNecessario - $patrimonioAtual),
            'meses' => $meses,
            'anos' => intdiv($meses, 12),
            'mesesRestantes' => $meses % 12,
            'metaJaAtingida' => $meses === 0,
            'totalInvestido' => $totalInvestido,
            'rendimento' => $montanteFinal - $totalInvestido,
            'montanteFinal' => $montanteFinal,
        ]);
    }

    public function calcularAposentadoria(array $dados): array|false
    {
        $this->erros = [];

        $idadeAtual = $this->inteiro($dados, 'idadeAtual', 'Informe uma idade atual válida.');
        $idadeAposentadoria = $this->inteiro($dados, 'idadeAposentadoria', 'Informe uma idade de aposentadoria válida.');
        $patrimonioAtual = $this->valor($dados, 'patrimonioAtual', 'Informe um patrimônio atual válido.');
        $aporteMensal = $this->valor($dados, 'aporteMensal', 'Informe um aporte mensal válido.');
        $taxaMensal = $this->taxaMensal($dados);

        if ($idadeAtual !== null && $idadeAposentadoria !== null) {
            if ($idadeAposentadoria <= $idadeAtual) {
                $this->erros['idadeAposentadoria'] = 'A idade de aposentadoria deve ser maior que a idade atual.';
            } elseif (($idadeAposentadoria - $idadeAtual) * 12 > self::LIMITE_MESES) {
                $this->erros['idadeAposentadoria'] = 'O prazo até a aposentadoria deve ser de no máximo 100 anos.';
            }
        }

        if (!empty($this->erros)) {
            return false;
        }

        $anos = $idadeAposentadoria - $idadeAtual;
        $meses = $anos * 12;

        $montanteFinal = $this->montante($patrimonioAtual, $aporteMensal, $taxaMensal, $meses);

        if (!is_finite($montanteFinal)) {
            $this->erros['geral'] = 'Os valores informados geram um resultado grande demais.';

            return false;
        }

        $totalInvestido = $patrimonioAtual + ($aporteMensal * $meses);

        return array_merge($this->resumoTaxa($taxaMensal), [
            'anos' => $anos,
            'totalInvestido' => $totalInvestido,
            'rendimento' => $montanteFinal - $totalInvestido,
            'montanteFinal' => $montanteFinal,
            // estimativa: o patrimônio rendendo a taxa informada, sem gastar o principal
            'rendaMensal' => $montanteFinal * $taxaMensal,
        ]);
    }

    public function calcularReservaEmergencia(array $dados): array|false
    {
        $this->erros = [];

        $gastoMensal = $this->valor($dados, 'gastoMensal', 'Informe um gasto mensal maior que zero.', true);
        $mesesCobertura = $this->inteiro($dados, 'mesesCobertura', 'Informe os meses de cobertura (1 ou mais).', 1);
        $valorGuardado = $this->valor($dados, 'valorGuardado', 'Informe um valor guardado válido.');
        $aporteMensal = $this->valor($dados, 'aporteMensal', 'Informe um aporte mensal válido.');
        $taxaMensal = $this->taxaMensal($dados);

        if (!empty($this->erros)) {
            return false;
        }

        $valorReserva = $gastoMensal * $mesesCobertura;

        $meses = $this->mesesParaMeta($valorReserva, $valorGuardado, $aporteMensal, $taxaMensal);

        if ($meses === null) {
            return false;
        }

        $montanteFinal = $this->montante($valorGuardado, $aporteMensal, $taxaMensal, $meses);
        $totalInvestido = $valorGuardado + ($aporteMensal * $meses);

        return array_merge($this->resumoTaxa($taxaMensal), [
            'valorReserva' => $valorReserva,
            'falta' => max(0, $valorReserva - $valorGuardado),
            'meses' => $meses,
            'anos' => intdiv($meses, 12),
            'mesesRestantes' => $meses % 12,
            'metaJaAtingida' => $meses === 0,
            'totalInvestido' => $totalInvestido,
            'rendimento' => $montanteFinal - $totalInvestido,
            'montanteFinal' => $montanteFinal,
        ]);
    }

    public function calcularValorReal(array $dados): array|false
    {
        $this->erros = [];

        $valor = $this->valor($dados, 'valor', 'Informe um valor maior que zero.', true);
        $prazoMeses = $this->prazoMeses($dados);
        $inflacaoMensal = $this->taxaMensal($dados, 'inflacao', 'unidadeInflacao', 'inflação');

        if (!empty($this->erros)) {
            return false;
        }

        $fator = pow(1 + $inflacaoMensal, $prazoMeses);

        if (!is_finite($fator)) {
            $this->erros['geral'] = 'Os valores informados geram um resultado grande demais.';

            return false;
        }

        return array_merge($this->resumoTaxa($inflacaoMensal), [
            // quanto será preciso no futuro para comprar o mesmo que o valor compra hoje
            'valorCorrigido' => $valor * $fator,
            // quanto o valor, recebido no futuro, vale em poder de compra de hoje
            'poderDeCompra' => $valor / $fator,
            'perdaPercentual' => (1 - (1 / $fator)) * 100,
        ]);
    }

    public function calcularComparadorCenarios(array $dados): array|false
    {
        $this->erros = [];

        $cenario1 = $this->cenario($dados, 1);
        $cenario2 = $this->cenario($dados, 2);

        if (!empty($this->erros)) {
            return false;
        }

        $diferenca = $cenario1['montanteFinal'] - $cenario2['montanteFinal'];

        return [
            'cenario1' => $cenario1,
            'cenario2' => $cenario2,
            // 1 ou 2: o cenário com o maior montante final; 0: empate (diferença menor que 1 centavo)
            'melhor' => abs($diferenca) < 0.005 ? 0 : ($diferenca > 0 ? 1 : 2),
            'diferenca' => abs($diferenca),
            'prazosDiferentes' => $cenario1['prazoMeses'] !== $cenario2['prazoMeses'],
        ];
    }

    public function calcularRendimentoInvestimento(array $dados): array|false
    {
        $this->erros = [];

        $valorInicial = $this->valor($dados, 'valorInicial', 'Informe um valor inicial maior que zero.', true);
        $valorFinal = $this->valor($dados, 'valorFinal', 'Informe um valor final válido.');
        $prazoMeses = $this->prazoMeses($dados);

        if (!empty($this->erros)) {
            return false;
        }

        $razao = $valorFinal / $valorInicial;

        // taxa média mensal: a taxa que, aplicada todo mês, leva o valor inicial ao valor final
        $taxaMensal = pow($razao, 1 / $prazoMeses) - 1;
        $taxaAnual = pow(1 + $taxaMensal, 12) - 1;

        if (!is_finite($taxaAnual)) {
            $this->erros['geral'] = 'Os valores informados geram um resultado grande demais.';

            return false;
        }

        return [
            'rendimento' => $valorFinal - $valorInicial,
            'rentabilidadeTotal' => ($razao - 1) * 100,
            'taxaMensal' => $taxaMensal * 100,
            'taxaAnual' => $taxaAnual * 100,
        ];
    }

    public function calcularJurosSimples(array $dados): array|false
    {
        $this->erros = [];

        $capital = $this->valor($dados, 'capital', 'Informe um capital maior que zero.', true);
        $taxaSimples = $this->taxaMensalSimples($dados);
        $prazoMeses = $this->prazoMeses($dados);

        if (!empty($this->erros)) {
            return false;
        }

        $juros = $capital * $taxaSimples * $prazoMeses;
        $montanteSimples = $capital + $juros;

        // mesma taxa em juros compostos, só para comparar (sem aportes)
        $montanteComposto = $this->montante($capital, 0.0, $this->taxaMensal($dados), $prazoMeses);

        if (!is_finite($montanteSimples) || !is_finite($montanteComposto)) {
            $this->erros['geral'] = 'Os valores informados geram um resultado grande demais.';

            return false;
        }

        return [
            'taxaMensal' => $taxaSimples * 100,
            'taxaAnual' => $taxaSimples * 12 * 100,
            'juros' => $juros,
            'montanteFinal' => $montanteSimples,
            'montanteComposto' => $montanteComposto,
            'diferencaComposto' => $montanteComposto - $montanteSimples,
        ];
    }

    public function getErros(): array
    {
        return $this->erros;
    }

    // valida e calcula um dos cenários do comparador; as mensagens de erro levam o número do cenário
    private function cenario(array $dados, int $numero): ?array
    {
        $complemento = ' (cenário ' . $numero . ')';

        $valorInicial = $this->valor($dados, 'valorInicial' . $numero, 'Informe um valor inicial válido' . $complemento . '.');
        $aporteMensal = $this->valor($dados, 'aporteMensal' . $numero, 'Informe um aporte mensal válido' . $complemento . '.');
        $taxaMensal = $this->taxaMensal($dados, 'taxa' . $numero, 'unidadeTaxa' . $numero, 'taxa' . $complemento);
        $prazoMeses = $this->prazoMeses($dados, 'prazoMeses' . $numero, $complemento);

        if ($valorInicial === null || $aporteMensal === null || $taxaMensal === null || $prazoMeses === null) {
            return null;
        }

        $montanteFinal = $this->montante($valorInicial, $aporteMensal, $taxaMensal, $prazoMeses);

        if (!is_finite($montanteFinal)) {
            $this->erros['geral'] = 'Os valores informados geram um resultado grande demais' . $complemento . '.';

            return null;
        }

        $totalInvestido = $valorInicial + ($aporteMensal * $prazoMeses);

        return array_merge($this->resumoTaxa($taxaMensal), [
            'prazoMeses' => $prazoMeses,
            'totalInvestido' => $totalInvestido,
            'rendimento' => $montanteFinal - $totalInvestido,
            'montanteFinal' => $montanteFinal,
        ]);
    }

    // juros simples: a taxa anual vira mensal por proporção (÷ 12), e não pela equivalência composta
    private function taxaMensalSimples(array $dados): ?float
    {
        // a leitura valida a taxa e a unidade e preenche os erros
        if ($this->taxaMensal($dados) === null) {
            return null;
        }

        $taxa = (float) $dados['taxa'];

        return $dados['unidadeTaxa'] === 'anual' ? $taxa / 100 / 12 : $taxa / 100;
    }

    // valor final com aportes no fim de cada mês: V0 x (1+i)^n + A x ((1+i)^n - 1) / i
    private function montante(float $valorInicial, float $aporteMensal, float $taxaMensal, int $meses): float
    {
        $fator = pow(1 + $taxaMensal, $meses);

        $montanteAportes = $taxaMensal > 0
            ? $aporteMensal * (($fator - 1) / $taxaMensal)
            : $aporteMensal * $meses;

        return ($valorInicial * $fator) + $montanteAportes;
    }

    // meses para o valor inicial, com aportes no fim de cada mês, chegar ao valor alvo;
    // devolve 0 se o valor inicial já chega lá e null (com o erro preenchido) se não chega em até 100 anos
    private function mesesParaMeta(float $valorAlvo, float $valorInicial, float $aporteMensal, float $taxaMensal): ?int
    {
        if ($valorInicial >= $valorAlvo) {
            return 0;
        }

        if ($aporteMensal == 0 && ($taxaMensal == 0 || $valorInicial == 0)) {
            $this->erros['aporteMensal'] = 'Com esses valores a meta nunca é atingida. Informe um aporte mensal ou um valor inicial com taxa maior que zero.';

            return null;
        }

        if ($taxaMensal > 0) {
            $base = $aporteMensal / $taxaMensal;
            $meses = log(($valorAlvo + $base) / ($valorInicial + $base)) / log(1 + $taxaMensal);
        } else {
            $meses = ($valorAlvo - $valorInicial) / $aporteMensal;
        }

        if ($meses > self::LIMITE_MESES) {
            $this->erros['geral'] = 'Com esses valores a meta levaria mais de 100 anos. Ajuste os dados.';

            return null;
        }

        // a fórmula devolve o prazo fracionado: arredonda para cima e confere mês a mês,
        // para o erro de arredondamento do ponto flutuante não mudar o resultado
        $meses = max(1, (int) ceil($meses - 1e-9));

        while ($this->montante($valorInicial, $aporteMensal, $taxaMensal, $meses) < $valorAlvo - 1e-6) {
            $meses++;
        }

        while ($meses > 1 && $this->montante($valorInicial, $aporteMensal, $taxaMensal, $meses - 1) >= $valorAlvo - 1e-6) {
            $meses--;
        }

        return $meses;
    }

    private function inteiro(array $dados, string $campo, string $mensagem, int $minimo = 0): ?int
    {
        $inteiro = filter_var($dados[$campo] ?? '', FILTER_VALIDATE_INT);

        if ($inteiro === false || $inteiro < $minimo) {
            $this->erros[$campo] = $mensagem;

            return null;
        }

        return $inteiro;
    }

    // lê a taxa e a unidade (mensal ou anual) e devolve a taxa mensal em decimal (1% = 0.01);
    // os nomes dos campos e o rótulo da mensagem podem mudar, para servir também à inflação e aos cenários
    private function taxaMensal(array $dados, string $campo = 'taxa', string $campoUnidade = 'unidadeTaxa', string $rotulo = 'taxa'): ?float
    {
        $taxa = $this->numero($dados[$campo] ?? '');
        $unidade = $dados[$campoUnidade] ?? '';

        if ($taxa === null || $taxa < 0) {
            $this->erros[$campo] = 'Informe uma ' . $rotulo . ' válida.';

            return null;
        }

        if (!in_array($unidade, ['mensal', 'anual'])) {
            $this->erros[$campoUnidade] = 'Selecione se a ' . $rotulo . ' é mensal ou anual.';

            return null;
        }

        // a taxa anual vira mensal pela equivalência composta
        return $unidade === 'anual' ? pow(1 + ($taxa / 100), 1 / 12) - 1 : $taxa / 100;
    }

    private function prazoMeses(array $dados, string $campo = 'prazoMeses', string $complemento = ''): ?int
    {
        $prazoMeses = filter_var($dados[$campo] ?? '', FILTER_VALIDATE_INT);

        if ($prazoMeses === false || $prazoMeses <= 0 || $prazoMeses > self::LIMITE_MESES) {
            $this->erros[$campo] = 'O prazo deve ser de 1 a ' . self::LIMITE_MESES . ' meses' . $complemento . '.';

            return null;
        }

        return $prazoMeses;
    }

    private function valor(array $dados, string $campo, string $mensagem, bool $maiorQueZero = false): ?float
    {
        $valor = $this->numero($dados[$campo] ?? '');

        if ($valor === null || $valor < 0 || ($maiorQueZero && $valor == 0)) {
            $this->erros[$campo] = $mensagem;

            return null;
        }

        return $valor;
    }

    // a mesma taxa nas duas unidades, para o usuário ver a conversão
    private function resumoTaxa(float $taxaMensal): array
    {
        return [
            'taxaMensal' => $taxaMensal * 100,
            'taxaAnual' => (pow(1 + $taxaMensal, 12) - 1) * 100,
        ];
    }

    private function numero(mixed $valor): ?float
    {
        if ($valor === '' || !is_numeric($valor)) {
            return null;
        }

        return (float) $valor;
    }
}