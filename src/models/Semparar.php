<?php
require_once __DIR__ . '/../includes/Model.php';
class Semparar extends Model {
    protected $table = 'semparar';
    protected $orderBy = 'data_org';
    protected $fillable = [
        'data_org',
        'combustivel',
        'veiculo',
        'placa',
        'marca',
        'modelo',
        'tipo',
        'departamento',
        'ficha',
        'secretaria',
        'tag',
        'mensalidade',
        'passagens',
        'estacionamento',
        'estabelecimentos',
        'credito',
        'isento',
        'mes',
        'total',
        'valor',
        'consumo',
        'Conta_status',
        'data_vencimento',
        'observacoes',
        'criado_por',
        'atualizado_por'
    ];

    /**
     * Calcula o total de gastos do mês atual.
     * @return float
     */
    public function getTotalDoMes() {
        // Corrigido para usar Conta_status = 'pago'
        $sql = "SELECT SUM(valor) as total FROM {$this->table}
        WHERE YEAR(data_org) = YEAR(CURRENT_DATE()) AND MONTH(data_org) = MONTH(CURRENT_DATE()) AND Conta_status = 'pago'";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

    /**
     * Calcula a média de gastos por veículo.
     * @return float
     */
    public function getMediaPorVeiculo() {
        $sql = "SELECT AVG(total_veiculo) as media FROM (SELECT SUM(valor) as total_veiculo FROM {$this->table} GROUP BY veiculo) as subquery";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc()['media'] : 0;
    }

    /**
     * Calcula o total de gastos do ano atual.
     * @return float
     */
    public function getTotalAnual() {
        // Corrigido para usar Conta_status = 'pago'
        $sql = "SELECT SUM(valor) as total FROM {$this->table} WHERE YEAR(data_org) = YEAR(CURRENT_DATE()) AND Conta_status = 'pago'";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_assoc()['total'] : 0;
    }
}