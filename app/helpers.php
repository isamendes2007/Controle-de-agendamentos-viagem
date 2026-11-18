<?php
/**
 * Horizonte Viagens — funções auxiliares compartilhadas.
 *
 * Reúne formatação, URLs, mensagens, CSRF, validações simples, componentes
 * de interface (ícones, selos, paginação, gráficos) e utilidades de domínio.
 * Não há dependência de frameworks nem de bibliotecas externas.
 */

declare(strict_types=1);

/* -------------------------------------------------------------------------
 * Configuração e URLs
 * ---------------------------------------------------------------------- */

/**
 * Lê uma configuração da aplicação. As constantes de config/app.php são a
 * fonte oficial; o array legado em $GLOBALS['config'] continua aceito.
 */
function config(?string $chave = null, mixed $padrao = null): mixed
{
    $mapa = [
        'app.nome'   => APP_NOME,
        'app.versao' => APP_VERSAO,
        'app.debug'  => APP_DEBUG,
        'app.fuso'   => APP_TIMEZONE,
    ];

    if ($chave !== null && array_key_exists($chave, $mapa)) {
        return $mapa[$chave];
    }

    $cfg = $GLOBALS['config'] ?? [];

    if ($chave === null) {
        return $cfg === [] ? $mapa : $cfg;
    }

    foreach (explode('.', $chave) as $parte) {
        if (!is_array($cfg) || !array_key_exists($parte, $cfg)) {
            return $padrao;
        }
        $cfg = $cfg[$parte];
    }

    return $cfg;
}

/** Escapa texto para saída em HTML. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Monta uma URL a partir da pasta de instalação. */
function base_url(string $caminho = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($caminho, '/');
}

/**
 * URL de uma rota interna do sistema. Aceita parâmetros extras e rotas que
 * já trazem query string embutida (ex.: "agendamentos?status=solicitado").
 */
function url(string $rota = 'dashboard', array $parametros = []): string
{
    $queryString = '';
    if (str_contains($rota, '?')) {
        [$rota, $queryString] = explode('?', $rota, 2);
    }

    $query = ['r' => trim($rota, '/')];
    if ($queryString !== '') {
        parse_str($queryString, $extras);
        $query = array_merge($query, $extras);
    }
    $query = array_merge($query, $parametros);

    return base_url('index.php') . '?' . http_build_query($query);
}

/** URL de um arquivo em assets/. */
function asset(string $caminho): string
{
    return base_url('assets/' . ltrim($caminho, '/'));
}

/** Redireciona para uma rota interna e encerra a requisição. */
function redirecionar(string $rota, array $parametros = []): void
{
    header('Location: ' . url($rota, $parametros));
    exit;
}

/** Método HTTP da requisição atual. */
function metodo(): string
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
}

/** Valor de um campo POST (aparado quando texto). */
function post(string $campo, mixed $padrao = ''): mixed
{
    $valor = $_POST[$campo] ?? $padrao;
    return is_string($valor) ? trim($valor) : $valor;
}

/** Valor de um campo GET (aparado quando texto). */
function get(string $campo, mixed $padrao = ''): mixed
{
    $valor = $_GET[$campo] ?? $padrao;
    return is_string($valor) ? trim($valor) : $valor;
}

/* -------------------------------------------------------------------------
 * CSRF e mensagens
 * ---------------------------------------------------------------------- */

/** Token CSRF da sessão. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

/** Campo oculto com o token CSRF. */
function csrf_campo(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Valida o token CSRF enviado no POST. */
function csrf_valido(): bool
{
    $enviado = $_POST['_token'] ?? '';
    $sessao = $_SESSION['csrf_token'] ?? '';

    return is_string($enviado) && is_string($sessao) && $sessao !== '' && hash_equals($sessao, $enviado);
}

/** Exige token válido; em caso de falha, volta com mensagem amigável. */
function exigir_csrf(): void
{
    if (csrf_valido()) {
        return;
    }

    flash('erro', 'O formulário expirou por segurança. Envie novamente.');
    $destino = $_SERVER['HTTP_REFERER'] ?? null;

    if (is_string($destino) && $destino !== '') {
        header('Location: ' . $destino);
        exit;
    }

    redirecionar('dashboard');
}

/** Registra uma mensagem temporária exibida após o redirecionamento. */
function flash(string $tipo, string $mensagem): void
{
    $permitidos = ['sucesso', 'erro', 'atencao', 'info'];
    if (!in_array($tipo, $permitidos, true)) {
        $tipo = 'info';
    }
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/**
 * Retira da sessão as mensagens temporárias pendentes.
 *
 * @return list<array{tipo: string, mensagem: string}>
 */
function flash_obter(): array
{
    $mensagens = $_SESSION['flash'] ?? [];
    $_SESSION['flash'] = [];
    return is_array($mensagens) ? $mensagens : [];
}

/* -------------------------------------------------------------------------
 * Formatação de valores, datas e documentos
 * ---------------------------------------------------------------------- */

/** Formata um valor como moeda brasileira. */
function moeda(mixed $valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

/** Versão curta de moeda para gráficos. */
function moeda_curta(mixed $valor): string
{
    $valor = (float) $valor;
    if (abs($valor) >= 1000000) {
        return 'R$ ' . number_format($valor / 1000000, 1, ',', '.') . ' mi';
    }
    if (abs($valor) >= 1000) {
        return 'R$ ' . number_format($valor / 1000, 1, ',', '.') . ' mil';
    }
    return 'R$ ' . number_format($valor, 0, ',', '.');
}

/** Formata um número com separadores brasileiros. */
function numero(mixed $valor, int $decimais = 0): string
{
    return number_format((float) $valor, $decimais, ',', '.');
}

/** Converte um valor monetário digitado no Brasil para decimal. */
function para_decimal(mixed $valor): float
{
    if (is_int($valor) || is_float($valor)) {
        return (float) $valor;
    }

    $texto = trim((string) $valor);
    if ($texto === '') {
        return 0.0;
    }

    $texto = preg_replace('/[^\d,.\-]/', '', $texto) ?? '';
    if (str_contains($texto, ',')) {
        $texto = str_replace('.', '', $texto);
        $texto = str_replace(',', '.', $texto);
    } elseif (substr_count($texto, '.') > 1) {
        $texto = str_replace('.', '', $texto);
    }

    return round((float) $texto, 2);
}

/** Data no formato dd/mm/aaaa. */
function data_br(?string $data): string
{
    if ($data === null || $data === '' || str_starts_with($data, '0000-00-00')) {
        return '—';
    }
    $timestamp = strtotime($data);
    return $timestamp !== false ? date('d/m/Y', $timestamp) : '—';
}

/** Data e hora no formato dd/mm/aaaa às HH:MM. */
function data_hora_br(?string $data): string
{
    if ($data === null || $data === '' || str_starts_with($data, '0000-00-00')) {
        return '—';
    }
    $timestamp = strtotime($data);
    return $timestamp !== false ? date('d/m/Y \à\s H:i', $timestamp) : '—';
}

/** Data no formato ISO (aaaa-mm-dd). */
function data_iso(?string $data): string
{
    if ($data === null || $data === '') {
        return '';
    }
    $timestamp = strtotime($data);
    return $timestamp !== false ? date('Y-m-d', $timestamp) : '';
}

/** Data de hoje em ISO. */
function hoje(): string
{
    return date('Y-m-d');
}

/** Dias entre hoje e a data informada (negativo quando já passou). */
function dias_restantes(?string $data): int
{
    if ($data === null || $data === '') {
        return 0;
    }
    $hoje = new DateTimeImmutable(date('Y-m-d'));
    $alvo = new DateTimeImmutable(date('Y-m-d', (int) strtotime($data)));
    return (int) $hoje->diff($alvo)->format('%r%a');
}

/** Quantidade de dias entre duas datas. */
function dias_entre(?string $inicio, ?string $fim): int
{
    if ($inicio === null || $fim === null || $inicio === '' || $fim === '') {
        return 0;
    }
    $a = new DateTimeImmutable(date('Y-m-d', (int) strtotime($inicio)));
    $b = new DateTimeImmutable(date('Y-m-d', (int) strtotime($fim)));
    return (int) $a->diff($b)->format('%a');
}

/** Idade em anos a partir da data de nascimento. */
function idade(?string $nascimento): ?int
{
    if ($nascimento === null || $nascimento === '') {
        return null;
    }
    try {
        return (new DateTimeImmutable($nascimento))->diff(new DateTimeImmutable())->y;
    } catch (Exception) {
        return null;
    }
}

/** Alias usado pelos controladores. */
function calcular_idade(?string $nascimento): ?int
{
    return idade($nascimento);
}

/** Iniciais do nome (ex.: "Marina Costa" → "MC"). */
function iniciais(?string $nome): string
{
    $partes = array_values(array_filter(preg_split('/\s+/', trim((string) $nome)) ?: []));
    if ($partes === []) {
        return '?';
    }
    $primeira = mb_substr($partes[0], 0, 1);
    $ultima = count($partes) > 1 ? mb_substr((string) end($partes), 0, 1) : '';
    return mb_strtoupper($primeira . $ultima);
}

/** Encurta um texto longo para exibição. */
function resumir(?string $texto, int $limite = 90): string
{
    $texto = trim((string) $texto);
    if (mb_strlen($texto) <= $limite) {
        return $texto;
    }
    return rtrim(mb_substr($texto, 0, $limite)) . '…';
}

/** Alias usado pelas visões. */
function limitar_texto(?string $texto, int $limite = 90): string
{
    return resumir($texto, $limite);
}

/** Remove tudo que não for dígito. */
function apenas_digitos(?string $valor): string
{
    return preg_replace('/\D+/', '', (string) $valor) ?? '';
}

/** Alias usado pelos serviços. */
function somente_digitos(?string $valor): string
{
    return apenas_digitos($valor);
}

/** Valida um CPF pelos dígitos verificadores. */
function cpf_valido(?string $cpf): bool
{
    $cpf = apenas_digitos($cpf);

    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
        return false;
    }

    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int) $cpf[$i] * (($t + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;
        if ((int) $cpf[$t] !== $digito) {
            return false;
        }
    }

    return true;
}

/** CPF no formato 000.000.000-00. */
function formatar_cpf(?string $cpf): string
{
    $cpf = apenas_digitos($cpf);
    if (strlen($cpf) !== 11) {
        return $cpf !== '' ? $cpf : '—';
    }
    return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
}

/** Alias mantido para compatibilidade. */
function cpf_formatado(?string $cpf): string
{
    return formatar_cpf($cpf);
}

/** Telefone no formato (00) 00000-0000. */
function formatar_telefone(?string $telefone): string
{
    $digitos = apenas_digitos($telefone);

    if (strlen($digitos) === 11) {
        return '(' . substr($digitos, 0, 2) . ') ' . substr($digitos, 2, 5) . '-' . substr($digitos, 7, 4);
    }
    if (strlen($digitos) === 10) {
        return '(' . substr($digitos, 0, 2) . ') ' . substr($digitos, 2, 4) . '-' . substr($digitos, 6, 4);
    }

    return $telefone !== null && trim($telefone) !== '' ? trim($telefone) : '—';
}

/** CEP no formato 00000-000. */
function cep_formatado(?string $cep): string
{
    $cep = apenas_digitos($cep);
    if (strlen($cep) !== 8) {
        return $cep !== '' ? $cep : '—';
    }
    return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
}

/** Link de telefone para uso em tel:. */
function telefone_link(?string $telefone): string
{
    $digitos = apenas_digitos($telefone);
    return $digitos === '' ? '#' : 'tel:+55' . $digitos;
}

/** Saudação conforme o horário atual. */
function saudacao(): string
{
    $hora = (int) date('H');
    if ($hora < 12) {
        return 'Bom dia';
    }
    if ($hora < 18) {
        return 'Boa tarde';
    }
    return 'Boa noite';
}

/** Percentual seguro (0 a 100). */
function percentual(float|int $parte, float|int $total, int $decimais = 0): float
{
    if ((float) $total <= 0) {
        return 0.0;
    }
    return round(((float) $parte / (float) $total) * 100, $decimais);
}

/** Meses em português, indexados de 1 a 12. */
function meses_pt(): array
{
    return [
        1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
        5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
        9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
    ];
}

/** Rótulo curto de um mês no formato aaaa-mm (ex.: "nov/26"). */
function mes_abreviado(string $ym): string
{
    [$ano, $mes] = array_pad(explode('-', $ym), 2, '');
    $rotulo = mb_substr(meses_pt()[(int) $mes] ?? (string) $mes, 0, 3);
    return mb_strtolower($rotulo) . '/' . substr($ano, 2, 2);
}

/** Rótulo completo de um mês no formato aaaa-mm. */
function mes_completo(string $ym): string
{
    [$ano, $mes] = array_pad(explode('-', $ym), 2, '');
    return (meses_pt()[(int) $mes] ?? (string) $mes) . ' de ' . $ano;
}

/* -------------------------------------------------------------------------
 * Domínio: rótulos, opções e situações
 * ---------------------------------------------------------------------- */

/** Rótulo de qualquer status usado pelo sistema. */
function status_rotulo(?string $status): string
{
    return match ($status) {
        'pendente'     => 'Pendente',
        'confirmada'   => 'Confirmada',
        'concluida'    => 'Concluída',
        'cancelada'    => 'Cancelada',
        'pago'         => 'Pago',
        'atrasado'     => 'Atrasado',
        'estornado'    => 'Estornado',
        'cancelado'    => 'Cancelado',
        'planejada'    => 'Planejada',
        'em_andamento' => 'Em andamento',
        'solicitado'   => 'Solicitado',
        'aprovado'     => 'Aprovado',
        'rejeitado'    => 'Rejeitado',
        'ativo'        => 'Ativo',
        'inativo'      => 'Inativo',
        default        => $status === null || $status === '' ? '—' : ucfirst(str_replace('_', ' ', $status)),
    };
}

/** Selo colorido de status. */
function selo_status(?string $status): string
{
    $classe = match ($status) {
        'pendente', 'planejada', 'solicitado' => 'selo--pendente',
        'pago', 'confirmada', 'concluida', 'aprovado', 'ativo' => 'selo--confirmada',
        'atrasado'    => 'selo--atrasado',
        'estornado'   => 'selo--estornado',
        'em_andamento' => 'selo--em_andamento',
        'cancelada', 'rejeitado' => 'selo--cancelada',
        'cancelado', 'inativo'   => 'selo--cancelado',
        default       => 'selo--cancelado',
    };

    return '<span class="selo ' . $classe . '">' . e(status_rotulo($status)) . '</span>';
}

/** Status possíveis de uma reserva. */
function opcoes_status_reserva(): array
{
    return [
        'pendente'   => 'Pendente',
        'confirmada' => 'Confirmada',
        'concluida'  => 'Concluída',
        'cancelada'  => 'Cancelada',
    ];
}

/** Status possíveis de um pagamento. */
function opcoes_status_pagamento(): array
{
    return [
        'pendente'  => 'Pendente',
        'pago'      => 'Pago',
        'atrasado'  => 'Atrasado',
        'estornado' => 'Estornado',
        'cancelado' => 'Cancelado',
    ];
}

/** Status possíveis de uma viagem. */
function opcoes_status_viagem(): array
{
    return [
        'planejada'    => 'Planejada',
        'confirmada'   => 'Confirmada',
        'em_andamento' => 'Em andamento',
        'concluida'    => 'Concluída',
        'cancelada'    => 'Cancelada',
    ];
}

/** Status possíveis de um agendamento. */
function opcoes_status_agendamento(): array
{
    return [
        'solicitado' => 'Solicitado',
        'aprovado'   => 'Aprovado',
        'rejeitado'  => 'Rejeitado',
        'cancelado'  => 'Cancelado',
    ];
}

/** Formas de pagamento aceitas. */
function opcoes_forma_pagamento(): array
{
    return [
        'pix'            => 'PIX',
        'cartao_credito' => 'Cartão de crédito',
        'cartao_debito'  => 'Cartão de débito',
        'boleto'         => 'Boleto',
        'transferencia'  => 'Transferência bancária',
        'dinheiro'       => 'Dinheiro',
    ];
}

/** Rótulo de uma forma de pagamento. */
function forma_pagamento_rotulo(?string $forma): string
{
    return opcoes_forma_pagamento()[$forma ?? ''] ?? '—';
}

/** Alias mantido para compatibilidade. */
function rotulo_forma_pagamento(?string $forma): string
{
    return forma_pagamento_rotulo($forma);
}

/** Categorias de pacote aceitas. */
function categorias_pacote(): array
{
    return [
        'praia'      => 'Praia',
        'aventura'   => 'Aventura',
        'cultural'   => 'Cultural',
        'gastronomia' => 'Gastronomia',
        'ecoturismo' => 'Ecoturismo',
        'luxo'       => 'Luxo',
        'religioso'  => 'Religioso',
        'negocios'   => 'Negócios',
    ];
}

/** Rótulo de categoria de pacote. */
function rotulo_categoria(?string $categoria): string
{
    return categorias_pacote()[$categoria ?? ''] ?? '—';
}

/** Estados brasileiros (UF => nome). */
function estados_brasil(): array
{
    return [
        'AC' => 'Acre', 'AL' => 'Alagoas', 'AP' => 'Amapá', 'AM' => 'Amazonas',
        'BA' => 'Bahia', 'CE' => 'Ceará', 'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo', 'GO' => 'Goiás', 'MA' => 'Maranhão',
        'MT' => 'Mato Grosso', 'MS' => 'Mato Grosso do Sul', 'MG' => 'Minas Gerais',
        'PA' => 'Pará', 'PB' => 'Paraíba', 'PR' => 'Paraná', 'PE' => 'Pernambuco',
        'PI' => 'Piauí', 'RJ' => 'Rio de Janeiro', 'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul', 'RO' => 'Rondônia', 'RR' => 'Roraima',
        'SC' => 'Santa Catarina', 'SP' => 'São Paulo', 'SE' => 'Sergipe',
        'TO' => 'Tocantins',
    ];
}

/** Rótulo do perfil de acesso. */
function rotulo_perfil(?string $perfil): string
{
    return $perfil === 'administrador' ? 'Administrador' : 'Agente de viagens';
}

/**
 * Situação operacional de um pacote, calculada pelas datas e pelas vagas.
 *
 * @return array{rotulo: string, classe: string}
 */
function situacao_pacote(array $pacote, int $ocupadas = 0): array
{
    if ((int) ($pacote['ativo'] ?? 1) !== 1) {
        return ['rotulo' => 'Inativo', 'classe' => 'inativo'];
    }
    if ((int) ($pacote['vagas'] ?? 0) > 0 && $ocupadas >= (int) $pacote['vagas']) {
        return ['rotulo' => 'Esgotado', 'classe' => 'pendente'];
    }
    return ['rotulo' => 'Disponível', 'classe' => 'confirmada'];
}

/* -------------------------------------------------------------------------
 * Componentes de interface
 * ---------------------------------------------------------------------- */

/** Paginação a partir do resultado paginado de um serviço. */
function paginacao_html(array $paginacao, string $rota, array $parametros = []): string
{
    $pagina = max(1, (int) ($paginacao['pagina'] ?? 1));
    $totalPaginas = max(1, (int) ($paginacao['total_paginas'] ?? 1));
    $total = (int) ($paginacao['total'] ?? 0);

    if ($totalPaginas <= 1) {
        return '';
    }

    $intervalo = 2;
    $inicio = max(1, $pagina - $intervalo);
    $fim = min($totalPaginas, $pagina + $intervalo);

    $link = static function (int $destino, string $rotulo, bool $desabilitado = false, bool $ativo = false) use ($rota, $parametros): string {
        if ($desabilitado) {
            return '<span class="paginacao__botao is-disabled">' . $rotulo . '</span>';
        }
        $href = url($rota, array_merge($parametros, ['pagina' => $destino]));
        $classe = $ativo ? 'is-active' : '';
        return '<a class="' . $classe . '" href="' . e($href) . '">' . $rotulo . '</a>';
    };

    $html = '<nav class="paginacao mt-2" aria-label="Paginação">';
    $html .= $link(max(1, $pagina - 1), '&laquo; Anterior', $pagina <= 1);
    $html .= '<span class="paginacao__numeros">';

    if ($inicio > 1) {
        $html .= $link(1, '1');
        if ($inicio > 2) {
            $html .= '<span class="paginacao__reticencias">…</span>';
        }
    }

    for ($i = $inicio; $i <= $fim; $i++) {
        $html .= $link($i, (string) $i, false, $i === $pagina);
    }

    if ($fim < $totalPaginas) {
        if ($fim < $totalPaginas - 1) {
            $html .= '<span class="paginacao__reticencias">…</span>';
        }
        $html .= $link($totalPaginas, (string) $totalPaginas);
    }

    $html .= '</span>';
    $html .= $link(min($totalPaginas, $pagina + 1), 'Próxima &raquo;', $pagina >= $totalPaginas);
    $html .= '</nav>';

    return $html;
}

/**
 * Gráfico de barras em HTML/CSS a partir de um mapa rótulo => valor.
 */
function grafico_barras(array $dados, string $formato = 'moeda'): string
{
    if ($dados === []) {
        return '<p class="texto-suave">Sem dados para exibir.</p>';
    }

    $maior = max(array_map(static fn ($valor): float => (float) $valor, $dados));
    $html = '<div class="grafico-barras">';

    foreach ($dados as $rotulo => $valor) {
        $altura = $maior > 0 ? max(4.0, ((float) $valor / $maior) * 100) : 4.0;
        $exibicao = $formato === 'moeda' ? moeda_curta($valor) : numero($valor);
        $html .= '<div class="grafico-barras__item" title="' . e($rotulo . ': ' . $exibicao) . '">';
        $html .= '<span class="texto-muted" style="font-size:.68rem;">' . e($exibicao) . '</span>';
        $html .= '<div class="grafico-barras__trilho"><div class="grafico-barras__barra" style="height:' . number_format($altura, 2, '.', '') . '%"></div></div>';
        $html .= '<span class="grafico-barras__rotulo">' . e($rotulo) . '</span>';
        $html .= '</div>';
    }

    return $html . '</div>';
}

/** Ícones em SVG (estilo linha) usados na interface. */
function icone(string $nome, int $tamanho = 20): string
{
    $conteudo = match ($nome) {
        // Painel e navegação
        'painel', 'home'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'aviao', 'plane'      => '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>',
        'menu'                => '<line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'sair', 'log-out'     => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'config', 'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6h.09a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',

        // Pessoas
        'usuarios', 'users', 'clientes' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'usuario', 'user'     => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',

        // Operação
        'destino', 'destinos', 'local', 'map-pin', 'map' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
        'globe'               => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'pacote', 'pacotes'   => '<line x1="16.5" y1="9.4" x2="7.5" y2="4.21"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'viagens'             => '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>',
        'agendamentos'        => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/>',
        'reserva', 'reservas' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/>',

        // Financeiro
        'pagamento', 'pagamentos' => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'dinheiro'            => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'relatorio', 'relatorios', 'documento' => '<line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/><line x1="2" y1="20" x2="22" y2="20"/>',
        'tendencia'           => '<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>',

        // Ações
        'mais', 'plus'        => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'buscar', 'search'    => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'editar', 'edit'      => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'excluir', 'trash'   => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
        'ver', 'visualizar', 'eye' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'eye-off'             => '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M1 1l22 22"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/>',
        'voltar', 'chevron-left', 'arrow-left' => '<polyline points="15 18 9 12 15 6"/>',
        'avancar', 'chevron-right', 'arrow-right' => '<polyline points="9 18 15 12 9 6"/>',
        'check'               => '<polyline points="20 6 9 17 4 12"/>',
        'check-circle'        => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
        'x', 'fechar'         => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
        'save'                => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
        'refresh'             => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>',
        'download'            => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'imprimir', 'printer' => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'filtro', 'filter'    => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',

        // Informação e alertas
        'alerta', 'alert-triangle' => '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'alert-circle'        => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
        'info'                => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'relogio', 'clock'    => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'calendario', 'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        'phone', 'telefone'   => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail', 'email'       => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'lock', 'cadeado', 'escudo' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'atividade'           => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'bussola'             => '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
        'sparkles'            => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6.3 6.3l2.8 2.8M14.9 14.9l2.8 2.8M6.3 17.7l2.8-2.8M14.9 9.1l2.8-2.8"/>',
        'flag'                => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
        default               => '<circle cx="12" cy="12" r="10"/>',
    };

    return '<svg class="icone" width="' . $tamanho . '" height="' . $tamanho . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $conteudo . '</svg>';
}
