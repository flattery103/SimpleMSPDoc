<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
use PDO;
final class Database
{
    private int $savepointSequence=0;
    public function __construct(private PDO $pdo) { $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false); $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC); }
    public static function connect(array $settings): self { return new self(new PDO($settings['dsn'],$settings['username'],$settings['password'],[PDO::ATTR_TIMEOUT=>5])); }
    public function connection(): PDO { return $this->pdo; }
    public function run(string $sql,array $params=[]): \PDOStatement { $s=$this->pdo->prepare($sql);$s->execute($params);return $s; }
    public function one(string $sql,array $params=[]): ?array { return $this->run($sql,$params)->fetch() ?: null; }
    public function all(string $sql,array $params=[]): array { return $this->run($sql,$params)->fetchAll(); }
    public function transaction(callable $fn): mixed {
        $nested=$this->pdo->inTransaction();$savepoint='mspdoc_sp_'.(++$this->savepointSequence);
        if($nested)$this->run('SAVEPOINT '.$savepoint);else $this->pdo->beginTransaction();
        try{$result=$fn();if($nested)$this->run('RELEASE SAVEPOINT '.$savepoint);else $this->pdo->commit();return $result;}
        catch(\Throwable $e){if($this->pdo->inTransaction()){if($nested){$this->run('ROLLBACK TO SAVEPOINT '.$savepoint);$this->run('RELEASE SAVEPOINT '.$savepoint);}else $this->pdo->rollBack();}throw $e;}
    }
}
