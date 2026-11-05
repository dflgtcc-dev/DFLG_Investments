/* Dashboard (RF 07): gráficos do painel, desenhados com Chart.js */
(function (raiz) {
    'use strict';

    // cores da marca DFLG (o verde vem da logo)
    var COR_RECEITA = '#08f068';
    var COR_DESPESA = '#ef4444';
    var COR_RESULTADO = '#f5b301';

    function dinheiro(valor) {
        return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function dinheiroCurto(valor) {
        return 'R$ ' + Number(valor).toLocaleString('pt-BR', { notation: 'compact', maximumFractionDigits: 1 });
    }

    // cores que acompanham o tema claro ou escuro do Bootstrap
    function lerTema(lerVariavel) {
        return {
            texto: lerVariavel('--bs-body-color') || '#212529',
            textoSuave: lerVariavel('--bs-secondary-color') || '#6c757d',
            linha: lerVariavel('--bs-border-color') || '#dee2e6',
            fundo: lerVariavel('--bs-body-bg') || '#ffffff'
        };
    }

    // texto no centro da rosca: o total das despesas do mês
    function textoCentral(dados, tema) {
        return {
            id: 'textoCentral',
            afterDraw: function (grafico) {
                var area = grafico.chartArea;
                var contexto = grafico.ctx;
                var x = (area.left + area.right) / 2;
                var y = (area.top + area.bottom) / 2;

                contexto.save();
                contexto.textAlign = 'center';
                contexto.textBaseline = 'middle';
                contexto.fillStyle = tema.textoSuave;
                contexto.font = '500 12px system-ui, sans-serif';
                contexto.fillText('Despesas', x, y - 13);
                contexto.fillStyle = tema.texto;
                contexto.font = '700 18px system-ui, sans-serif';
                contexto.fillText(dinheiro(dados.total), x, y + 9);
                contexto.restore();
            }
        };
    }

    function configRosca(dados, tema) {
        return {
            type: 'doughnut',
            data: {
                labels: dados.rotulos,
                datasets: [{
                    data: dados.valores,
                    backgroundColor: dados.cores,
                    borderColor: tema.fundo,
                    borderWidth: 3,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (item) {
                                var porcentagem = dados.total > 0 ? item.parsed / dados.total * 100 : 0;
                                return ' ' + item.label + ': ' + dinheiro(item.parsed) +
                                    ' (' + porcentagem.toFixed(1).replace('.', ',') + '%)';
                            }
                        }
                    }
                }
            },
            plugins: [textoCentral(dados, tema)]
        };
    }

    function configEvolucao(dados, tema) {
        return {
            type: 'bar',
            data: {
                labels: dados.rotulos,
                datasets: [
                    {
                        label: 'Receitas',
                        data: dados.receitas,
                        backgroundColor: COR_RECEITA,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 26,
                        order: 1
                    },
                    {
                        label: 'Despesas',
                        data: dados.despesas,
                        backgroundColor: COR_DESPESA,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 26,
                        order: 1
                    },
                    {
                        type: 'line',
                        label: 'Resultado',
                        data: dados.resultado,
                        borderColor: COR_RESULTADO,
                        backgroundColor: COR_RESULTADO,
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        order: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: { color: tema.texto, usePointStyle: true, boxWidth: 8, boxHeight: 8 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (item) {
                                return ' ' + item.dataset.label + ': ' + dinheiro(item.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: tema.textoSuave } },
                    y: {
                        grid: { color: tema.linha },
                        border: { display: false },
                        ticks: {
                            color: tema.textoSuave,
                            callback: function (valor) { return dinheiroCurto(valor); }
                        }
                    }
                }
            }
        };
    }

    // desenha os gráficos que têm elemento na tela; "extra" permite ajustar opções (usado nos testes)
    function criarGraficos(Chart, dados, tema, elementos, extra) {
        var graficos = {};

        function mesclar(config) {
            Object.keys(extra || {}).forEach(function (chave) {
                config.options[chave] = extra[chave];
            });

            return config;
        }

        if (elementos.categorias && dados.categorias.valores.length > 0) {
            graficos.categorias = new Chart(elementos.categorias, mesclar(configRosca(dados.categorias, tema)));
        }

        if (elementos.evolucao) {
            graficos.evolucao = new Chart(elementos.evolucao, mesclar(configEvolucao(dados.evolucao, tema)));
        }

        return graficos;
    }

    function iniciar() {
        var arquivo = document.getElementById('dados-dashboard');

        if (!arquivo) {
            return;
        }

        var areas = document.querySelectorAll('[data-grafico]');

        // se a biblioteca não carregou (sem internet, por exemplo), avisa em vez de deixar o espaço em branco
        if (typeof Chart === 'undefined') {
            Array.prototype.forEach.call(areas, function (area) {
                area.innerHTML = '<p class="text-muted small mb-0">Não foi possível carregar o gráfico. ' +
                    'Os valores continuam disponíveis na lista.</p>';
            });

            return;
        }

        var estilo = getComputedStyle(document.documentElement);
        var tema = lerTema(function (nome) { return estilo.getPropertyValue(nome).trim(); });

        criarGraficos(Chart, JSON.parse(arquivo.textContent), tema, {
            categorias: document.getElementById('grafico-categorias'),
            evolucao: document.getElementById('grafico-evolucao')
        });
    }

    raiz.DflgDashboard = { lerTema: lerTema, configRosca: configRosca, configEvolucao: configEvolucao, criarGraficos: criarGraficos };

    if (typeof document !== 'undefined') {
        document.addEventListener('DOMContentLoaded', iniciar);
    }
})(typeof module !== 'undefined' && module.exports ? module.exports : (typeof window !== 'undefined' ? window : this));
