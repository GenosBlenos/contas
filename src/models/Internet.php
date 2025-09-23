<?php

class Internet
{
    private $pdo;
    private $table = 'internet';

    public function __construct()
    {
        // Assume que a conexão PDO está disponível globalmente ou é injetada.
        // Com base em `processa_pdf.php`, o arquivo de conexão é incluído e cria a variável $pdo.
        global $pdo;
        if (!isset($pdo)) {
            // O caminho é relativo ao script que executa, não a este arquivo.
            // `internet.php` está na raiz, então o caminho é direto.
            require_once __DIR__ . '/../../app/conexao.php';
        }
        $this->pdo = $pdo;
    }

    public function create(array $data): bool
    {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            // Em um ambiente real, logar o erro: error_log($e->getMessage());
            return false;
        }
    }

    public function update(int $id, array $data): bool
    {
        try {
            $set_parts = [];
            foreach (array_keys($data) as $key) {
                $set_parts[] = "$key = :$key";
            }
            $set_clause = implode(', ', $set_parts);
            $sql = "UPDATE {$this->table} SET $set_clause WHERE id_internet = :id";
            $data['id'] = $id;
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_internet = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function buscarComFiltros(array $filtros): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $where = [];
        $params = [];

        if (!empty($filtros['secretaria'])) {
            $where[] = "secretaria LIKE :secretaria";
            $params['secretaria'] = '%' . $filtros['secretaria'] . '%';
        }
        if (!empty($filtros['provedor'])) {
            $where[] = "provedor LIKE :provedor";
            $params['provedor'] = '%' . $filtros['provedor'] . '%';
        }
        if (!empty($filtros['instalacao'])) {
            $where[] = "instalacao LIKE :instalacao";
            $params['instalacao'] = '%' . $filtros['instalacao'] . '%';
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY data_vencimento DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $stats = [];
        $stmt = $this->pdo->prepare("SELECT SUM(valor) as total FROM {$this->table} WHERE Conta_status = 'pendente'");
        $stmt->execute();
        $stats['totalPendente'] = $stmt->fetchColumn() ?: 0;
        $sqlMedia = "SELECT ROUND(AVG(
            CASE
                WHEN velocidade LIKE '%Gbps%' OR velocidade LIKE '%Giga%' THEN CAST(velocidade AS UNSIGNED) * 1000
                ELSE CAST(velocidade AS UNSIGNED)
            END
        )) as media_velocidade FROM {$this->table}";
        $stmt = $this->pdo->prepare($sqlMedia);
        $stmt->execute();
        $stats['mediaVelocidade'] = $stmt->fetchColumn() ?: 0;

        $stmt = $this->pdo->prepare("SELECT DATE_FORMAT(data_vencimento, '%Y-%m') as mes_ano, SUM(valor) as valor_total FROM {$this->table} WHERE data_vencimento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY mes_ano ORDER BY mes_ano ASC");
        $stmt->execute();
        $stats['valorMensal'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $stats;
    }
}