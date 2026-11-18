<?php
/**
 * Horizonte Viagens — Camada de acesso a dados.
 *
 * Encapsula a conexão PDO e oferece métodos tipados para consultas e gravações.
 * Suporta dois drivers:
 *
 *  - sqlite: banco em arquivo (storage/banco.sqlite), sem servidor. É criado
 *    automaticamente na primeira execução a partir de database/sqlite/.
 *  - mysql: servidor MySQL/MariaDB, indicado para hospedagem.
 *
 * Todas as consultas usam prepared statements.
 */

declare(strict_types=1);

final class Database
{
    private static ?self $instancia = null;
    private PDO $conexao;
    private string $driver;

    private function __construct()
    {
        $this->driver = DB_DRIVER;

        if ($this->driver === 'sqlite') {
            $this->conectarSqlite();
            return;
        }

        $this->conectarMysql();
    }

    public static function instanciar(): self
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    public function conexao(): PDO
    {
        return $this->conexao;
    }

    public function driver(): string
    {
        return $this->driver;
    }

    /** Nome amigável do banco em uso, exibido na interface. */
    public function descricao(): string
    {
        return $this->driver === 'sqlite'
            ? 'SQLite (arquivo local)'
            : 'MySQL (' . DB_HOST . '/' . DB_NAME . ')';
    }

    /** Consulta todos os registros. */
    public function buscarTodos(string $sql, array $parametros = [], array $tipos = []): array
    {
        $comando = $this->preparar($sql, $parametros, $tipos);
        $comando->execute();
        return $comando->fetchAll();
    }

    /** Devolve o primeiro registro ou null. */
    public function buscarUm(string $sql, array $parametros = [], array $tipos = []): ?array
    {
        $comando = $this->preparar($sql, $parametros, $tipos);
        $comando->execute();
        $registro = $comando->fetch();
        return $registro === false ? null : $registro;
    }

    /** Executa uma escrita e devolve o número de linhas afetadas. */
    public function executar(string $sql, array $parametros = [], array $tipos = []): int
    {
        $comando = $this->preparar($sql, $parametros, $tipos);
        $comando->execute();
        return $comando->rowCount();
    }

    /** Devolve o ID gerado no último INSERT. */
    public function ultimoId(): int
    {
        return (int) $this->conexao->lastInsertId();
    }

    /** Inicia uma transação. */
    public function iniciarTransacao(): void
    {
        $this->conexao->beginTransaction();
    }

    /** Confirma a transação em andamento. */
    public function confirmarTransacao(): void
    {
        $this->conexao->commit();
    }

    /** Desfaz a transação em andamento. */
    public function desfazerTransacao(): void
    {
        if ($this->conexao->inTransaction()) {
            $this->conexao->rollBack();
        }
    }

    /** Executa uma callable dentro de uma transação, com rollback em caso de exceção. */
    public function emTransacao(callable $acao): mixed
    {
        $this->iniciarTransacao();
        try {
            $resultado = $acao($this);
            $this->confirmarTransacao();
            return $resultado;
        } catch (Throwable $erro) {
            $this->desfazerTransacao();
            throw $erro;
        }
    }

    /** Prepara um comando aplicando os parâmetros informados. */
    private function preparar(string $sql, array $parametros, array $tipos): PDOStatement
    {
        $comando = $this->conexao->prepare($sql);
        foreach ($parametros as $chave => $valor) {
            $tipo = $tipos[$chave] ?? (is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
            $comando->bindValue($chave, $valor, $tipo);
        }
        return $comando;
    }

    private function conectarMysql(): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $this->conexao = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            $this->opcoesConexao()
        );
        $this->conexao->exec("SET time_zone = '-03:00'");
    }

    private function conectarSqlite(): void
    {
        $caminho = DB_SQLITE_PATH;
        $diretorio = dirname($caminho);

        if (!is_dir($diretorio) && !@mkdir($diretorio, 0775, true) && !is_dir($diretorio)) {
            throw new RuntimeException(
                'Não foi possível criar a pasta de dados em "' . $diretorio . '". '
                . 'Verifique as permissões de escrita do servidor.'
            );
        }

        $bancoNovo = !is_file($caminho) || filesize($caminho) === 0;

        try {
            $this->conexao = new PDO('sqlite:' . $caminho, null, null, $this->opcoesConexao());
        } catch (PDOException $erro) {
            throw new RuntimeException(
                'Não foi possível abrir o banco SQLite. Verifique se a extensão pdo_sqlite '
                . 'está habilitada no PHP. Detalhe: ' . $erro->getMessage(),
                0,
                $erro
            );
        }

        $this->conexao->exec('PRAGMA foreign_keys = ON');
        $this->conexao->exec('PRAGMA busy_timeout = 5000');
        $this->conexao->exec('PRAGMA journal_mode = WAL');

        if ($bancoNovo) {
            if (!DB_AUTO_INSTALAR) {
                throw new RuntimeException(
                    'O banco SQLite "' . $caminho . '" não existe e a instalação automática está desativada.'
                );
            }
            $this->instalarSqlite();
        }
    }

    /** Opções comuns de conexão PDO. */
    private function opcoesConexao(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => DB_DRIVER === 'mysql',
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ];
    }

    /**
     * Cria a estrutura e os dados de demonstração do SQLite.
     * Executado apenas quando o arquivo do banco ainda não existe.
     */
    private function instalarSqlite(): void
    {
        $pastaSql = RAIZ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'sqlite';

        foreach (['schema.sql', 'seed.sql'] as $arquivo) {
            $caminho = $pastaSql . DIRECTORY_SEPARATOR . $arquivo;
            if (!is_file($caminho)) {
                throw new RuntimeException(
                    'Arquivo de instalação não encontrado: database/sqlite/' . $arquivo . '.'
                );
            }

            $conteudo = (string) file_get_contents($caminho);
            // SQLite aceita vários comandos em uma única chamada (inclusive
            // gatilhos com "BEGIN ... END"), por isso não é necessário dividir.
            $this->conexao->exec($conteudo);
        }
    }
}

/** Devolve a instância única do banco de dados. */
function banco(): Database
{
    return Database::instanciar();
}
