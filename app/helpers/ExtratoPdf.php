<?php

namespace app\helpers;

require_once __DIR__ . '/../libs/fpdf/fpdf.php';

// RF 11: gera o PDF do extrato usando a biblioteca FPDF
class ExtratoPdf extends \FPDF
{
    private string $titulo;
    private string $subtitulo;
    private string $nomeUsuario;

    public function __construct(string $titulo, string $subtitulo, string $nomeUsuario)
    {
        parent::__construct('P', 'mm', 'A4');

        $this->titulo = $titulo;
        $this->subtitulo = $subtitulo;
        $this->nomeUsuario = $nomeUsuario;

        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 20);
        $this->AliasNbPages();
        // true = texto já está em UTF-8 (evita a função utf8_encode, obsoleta no PHP 8.2)
        $this->SetTitle($titulo . ' - ' . $subtitulo, true);
        $this->SetAuthor('DFLG Investments', true);
    }

    public function gerar(string $tipo, array $extrato): void
    {
        $this->AddPage();

        $this->SetFont('Helvetica', 'B', 16);
        $this->SetTextColor(33, 37, 41);
        $this->celula(180, 8, $this->titulo . ': ' . $this->subtitulo, '', 1);

        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(108, 117, 125);
        $this->celula(180, 5, 'Cliente: ' . $this->nomeUsuario . '   |   Gerado em ' . date('d/m/Y H:i'), '', 1);
        $this->Ln(5);

        $this->caixasResumo($extrato, $tipo === 'anual' ? 'Saldo no início do ano' : 'Saldo no início do mês');

        if ($tipo === 'anual') {
            $this->tabelaAnual($extrato);
        } else {
            $this->tabelaMensal($extrato);
        }
    }

    public function Header()
    {
        $this->SetFont('Helvetica', 'B', 13);
        $this->SetTextColor(25, 135, 84);
        $this->celula(70, 8, 'DFLG Investments', '', 0);

        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(108, 117, 125);
        $this->celula(110, 8, $this->titulo . ' - ' . $this->subtitulo, '', 1, 'R');

        $this->SetDrawColor(222, 226, 230);
        $this->Line(15, 23, 195, 23);
        $this->SetY(28);
    }

    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', '', 8);
        $this->SetTextColor(108, 117, 125);
        $this->celula(120, 6, 'Considera apenas registros com data até hoje.', '', 0);
        $this->celula(60, 6, 'Página ' . $this->PageNo() . ' de {nb}', '', 0, 'R');
    }

    private function caixasResumo(array $extrato, string $rotuloInicial): void
    {
        $itens = [
            [$rotuloInicial, $extrato['saldoInicial'], null],
            ['Receitas', $extrato['totalReceitas'], [25, 135, 84]],
            ['Despesas', $extrato['totalDespesas'], [220, 53, 69]],
            ['Saldo final', $extrato['saldoFinal'], null],
        ];

        $y = $this->GetY();

        foreach ($itens as $i => [$rotulo, $valor, $cor]) {
            $x = 15 + $i * 45.5;

            $this->SetFillColor(244, 245, 249);
            $this->Rect($x, $y, 43.5, 16, 'F');

            $this->SetXY($x + 2, $y + 2);
            $this->SetFont('Helvetica', '', 8);
            $this->SetTextColor(108, 117, 125);
            $this->celula(39.5, 4, $rotulo, '', 0);

            $cor = $cor ?? ($valor < 0 ? [220, 53, 69] : [33, 37, 41]);
            $this->SetXY($x + 2, $y + 8);
            $this->SetFont('Helvetica', 'B', 10);
            $this->SetTextColor($cor[0], $cor[1], $cor[2]);
            $this->celula(39.5, 5, $this->dinheiro($valor), '', 0);
        }

        $this->SetY($y + 22);
    }

    private function tabelaMensal(array $extrato): void
    {
        if (empty($extrato['lancamentos'])) {
            $this->mensagemVazia('Nenhum lançamento neste mês.');

            return;
        }

        $this->cabecalhoMensal();

        foreach ($extrato['lancamentos'] as $lancamento) {
            $transacao = $lancamento['transacao'];

            if ($this->GetY() + 7 > $this->PageBreakTrigger) {
                $this->AddPage();
                $this->cabecalhoMensal();
            }

            $this->SetFont('Helvetica', '', 9);
            $this->SetDrawColor(222, 226, 230);
            $this->SetTextColor(33, 37, 41);
            $this->celula(22, 7, date('d/m/Y', strtotime($transacao->getDataRegistro())), 'B', 0);
            $this->celula(36, 7, $transacao->getNomeCategoria() ?? 'Sem categoria', 'B', 0);
            $this->celula(56, 7, $transacao->getDescricao() ?: '-', 'B', 0);

            $cor = $transacao->eReceita() ? [25, 135, 84] : [220, 53, 69];
            $this->SetTextColor($cor[0], $cor[1], $cor[2]);
            $this->celula(33, 7, ($transacao->eReceita() ? '+ ' : '- ') . $this->dinheiro($transacao->getValor()), 'B', 0, 'R');

            $this->SetTextColor($lancamento['saldo'] < 0 ? 220 : 33, $lancamento['saldo'] < 0 ? 53 : 37, $lancamento['saldo'] < 0 ? 69 : 41);
            $this->celula(33, 7, $this->dinheiro($lancamento['saldo']), 'B', 1, 'R');
        }
    }

    private function tabelaAnual(array $extrato): void
    {
        if (empty($extrato['meses'])) {
            $this->mensagemVazia('Nenhum mês com registros neste ano.');

            return;
        }

        $this->cabecalhoAnual();

        foreach ($extrato['meses'] as $mes) {
            if ($this->GetY() + 7 > $this->PageBreakTrigger) {
                $this->AddPage();
                $this->cabecalhoAnual();
            }

            $this->SetFont('Helvetica', '', 9);
            $this->SetDrawColor(222, 226, 230);
            $this->SetTextColor(33, 37, 41);
            $this->celula(45, 7, $mes['mes'], 'B', 0);

            $this->SetTextColor(25, 135, 84);
            $this->celula(33, 7, $this->dinheiro($mes['receitas']), 'B', 0, 'R');

            $this->SetTextColor(220, 53, 69);
            $this->celula(33, 7, $this->dinheiro($mes['despesas']), 'B', 0, 'R');

            $this->corPeloSinal($mes['resultado']);
            $this->celula(33, 7, $this->dinheiro($mes['resultado']), 'B', 0, 'R');

            $this->corPeloSinal($mes['saldoAcumulado']);
            $this->celula(36, 7, $this->dinheiro($mes['saldoAcumulado']), 'B', 1, 'R');
        }

        // linha de total do ano
        if ($this->GetY() + 8 > $this->PageBreakTrigger) {
            $this->AddPage();
            $this->cabecalhoAnual();
        }

        $resultadoAno = round($extrato['totalReceitas'] - $extrato['totalDespesas'], 2);

        $this->SetFont('Helvetica', 'B', 9);
        $this->SetFillColor(233, 236, 239);
        $this->SetTextColor(33, 37, 41);
        $this->celula(45, 8, 'Total do ano', '', 0, 'L', true);
        $this->celula(33, 8, $this->dinheiro($extrato['totalReceitas']), '', 0, 'R', true);
        $this->celula(33, 8, $this->dinheiro($extrato['totalDespesas']), '', 0, 'R', true);
        $this->celula(33, 8, $this->dinheiro($resultadoAno), '', 0, 'R', true);
        $this->celula(36, 8, $this->dinheiro($extrato['saldoFinal']), '', 1, 'R', true);
    }

    private function cabecalhoMensal(): void
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetFillColor(233, 236, 239);
        $this->SetTextColor(33, 37, 41);
        $this->celula(22, 8, 'Data', '', 0, 'L', true);
        $this->celula(36, 8, 'Categoria', '', 0, 'L', true);
        $this->celula(56, 8, 'Descrição', '', 0, 'L', true);
        $this->celula(33, 8, 'Valor', '', 0, 'R', true);
        $this->celula(33, 8, 'Saldo', '', 1, 'R', true);
    }

    private function cabecalhoAnual(): void
    {
        $this->SetFont('Helvetica', 'B', 9);
        $this->SetFillColor(233, 236, 239);
        $this->SetTextColor(33, 37, 41);
        $this->celula(45, 8, 'Mês', '', 0, 'L', true);
        $this->celula(33, 8, 'Receitas', '', 0, 'R', true);
        $this->celula(33, 8, 'Despesas', '', 0, 'R', true);
        $this->celula(33, 8, 'Resultado do mês', '', 0, 'R', true);
        $this->celula(36, 8, 'Saldo acumulado', '', 1, 'R', true);
    }

    private function mensagemVazia(string $mensagem): void
    {
        $this->SetFont('Helvetica', '', 10);
        $this->SetTextColor(108, 117, 125);
        $this->celula(180, 8, $mensagem, '', 1);
    }

    private function corPeloSinal(float $valor): void
    {
        if ($valor < 0) {
            $this->SetTextColor(220, 53, 69);
        } else {
            $this->SetTextColor(33, 37, 41);
        }
    }

    // mesmo formato do restante do sistema (valor negativo: R$ -10,00)
    private function dinheiro(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    // o FPDF trabalha em Windows-1252: converte o texto (UTF-8) e corta o que não cabe na célula
    private function celula(float $largura, float $altura, string $texto, string $borda = '', int $quebra = 0, string $alinhamento = 'L', bool $preencher = false): void
    {
        $texto = mb_convert_encoding($texto, 'Windows-1252', 'UTF-8');

        if ($this->GetStringWidth($texto) > $largura - 2) {
            while ($texto !== '' && $this->GetStringWidth($texto . '...') > $largura - 2) {
                $texto = substr($texto, 0, -1);
            }

            $texto .= '...';
        }

        $this->Cell($largura, $altura, $texto, $borda, $quebra, $alinhamento, $preencher);
    }
}
