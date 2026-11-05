<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/Config.php';

use app\core\Router;

$router = new Router();

$router->get('/', 'HomeController@index');
$router->get('/dashboard', 'DashboardController@index');

//Autenticacao
$router->get('/login', 'AutenticacaoController@login');
$router->post('/logar', 'AutenticacaoController@logar');
$router->get('/logout', 'AutenticacaoController@logout');

$router->get('/cadastrar', 'AutenticacaoController@cadastrar');
$router->post('/cadastrar/salvar', 'AutenticacaoController@salvar');

// Meta Routes
$router->get('/metas', 'MetaController@listarTodas');
$router->get('/metas/cadastrar', 'MetaController@criar');
$router->post('/metas/salvar', 'MetaController@salvar');
$router->get('/metas/editar', 'MetaController@editar');
$router->post('/metas/atualizar', 'MetaController@atualizar');
$router->post('/metas/excluir', 'MetaController@excluir');
$router->get('/metas/aportar', 'MetaController@aportar');
$router->post('/metas/aportar/salvar', 'MetaController@salvarAporte');
$router->get('/metas/resgatar', 'MetaController@resgatar');
$router->post('/metas/resgatar/salvar', 'MetaController@salvarResgate');

// Transacao Routes
$router->get('/transacoes', 'TransacaoController@listarTodas');
$router->get('/transacoes/cadastrar', 'TransacaoController@criar');
$router->post('/transacoes/salvar', 'TransacaoController@salvar');
$router->get('/transacoes/editar', 'TransacaoController@editar');
$router->post('/transacoes/atualizar', 'TransacaoController@atualizar');
$router->post('/transacoes/excluir', 'TransacaoController@excluir');

// Parcelamento Routes
$router->get('/parcelamentos', 'ParcelaController@listarTodos');
$router->get('/parcelamentos/cadastrar', 'ParcelaController@criar');
$router->post('/parcelamentos/salvar', 'ParcelaController@salvar');
$router->get('/parcelamentos/editar', 'ParcelaController@editar');
$router->post('/parcelamentos/atualizar', 'ParcelaController@atualizar');
$router->post('/parcelamentos/excluir', 'ParcelaController@excluir');
$router->post('/parcelamentos/lancar', 'ParcelaController@lancar');
$router->post('/parcelamentos/desfazer', 'ParcelaController@desfazer');

// Relatorio Routes
$router->get('/relatorios', 'RelatorioController@index');
$router->get('/relatorios/pdf', 'RelatorioController@pdf');

// Categoria Routes (limite mensal por categoria, RF 12)
$router->get('/categorias', 'CategoriaController@listarTodas');
$router->post('/categorias/limites', 'CategoriaController@salvarLimites');

// Ranking Routes
$router->get('/ranking', 'RankingController@index');

// Perfil Routes
$router->get('/perfil', 'PerfilController@visualizar');
$router->get('/perfil/editar', 'PerfilController@editar');
$router->get('/perfil/excluir', 'PerfilController@excluir');
$router->post('/perfil/excluir', 'PerfilController@excluirConta');
$router->post('/perfil/atualizar', 'PerfilController@atualizar');
$router->get('/perfil/senha', 'PerfilController@senha');
$router->post('/perfil/senha', 'PerfilController@alterarSenha');
$router->post('/perfil/preferencias', 'PerfilController@atualizarPreferencias');

// Calculadoras Routes
$router->get('/calculadoras', 'CalculadoraController@index');
$router->get('/calculadoras/juros-compostos', 'CalculadoraController@jurosCompostos');
$router->post('/calculadoras/juros-compostos/calcular', 'CalculadoraController@jurosCompostosCalcular');
$router->get('/calculadoras/aporte-meta', 'CalculadoraController@aporteMeta');
$router->post('/calculadoras/aporte-meta/calcular', 'CalculadoraController@aporteMetaCalcular');
$router->get('/calculadoras/tempo-meta', 'CalculadoraController@tempoMeta');
$router->post('/calculadoras/tempo-meta/calcular', 'CalculadoraController@tempoMetaCalcular');
$router->get('/calculadoras/independencia-financeira', 'CalculadoraController@independenciaFinanceira');
$router->post('/calculadoras/independencia-financeira/calcular', 'CalculadoraController@independenciaFinanceiraCalcular');
$router->get('/calculadoras/aposentadoria', 'CalculadoraController@aposentadoria');
$router->post('/calculadoras/aposentadoria/calcular', 'CalculadoraController@aposentadoriaCalcular');
$router->get('/calculadoras/reserva-emergencia', 'CalculadoraController@reservaEmergencia');
$router->post('/calculadoras/reserva-emergencia/calcular', 'CalculadoraController@reservaEmergenciaCalcular');
$router->get('/calculadoras/valor-real', 'CalculadoraController@valorReal');
$router->post('/calculadoras/valor-real/calcular', 'CalculadoraController@valorRealCalcular');
$router->get('/calculadoras/comparador-cenarios', 'CalculadoraController@comparadorCenarios');
$router->post('/calculadoras/comparador-cenarios/calcular', 'CalculadoraController@comparadorCenariosCalcular');
$router->get('/calculadoras/rendimento-investimento', 'CalculadoraController@rendimentoInvestimento');
$router->post('/calculadoras/rendimento-investimento/calcular', 'CalculadoraController@rendimentoInvestimentoCalcular');
$router->get('/calculadoras/juros-simples', 'CalculadoraController@jurosSimples');
$router->post('/calculadoras/juros-simples/calcular', 'CalculadoraController@jurosSimplesCalcular');

$router->get('/admin', 'AdminController@painel');
$router->get('/admin/usuarios', 'AdminController@usuarios');
$router->get('/admin/usuarios/novo', 'AdminController@novo');
$router->post('/admin/usuarios/novo', 'AdminController@criar');
$router->get('/admin/usuarios/editar', 'AdminController@editar');
$router->post('/admin/usuarios/editar', 'AdminController@atualizar');
$router->post('/admin/usuarios/senha', 'AdminController@redefinirSenha');
$router->post('/admin/usuarios/bloquear', 'AdminController@bloquear');
$router->post('/admin/usuarios/desbloquear', 'AdminController@desbloquear');
$router->post('/admin/usuarios/excluir', 'AdminController@excluir');

$router->run();