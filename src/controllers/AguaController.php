<?php
require_once __DIR__ . '/../models/Agua.php';

class AguaController {
    public function index() {
        $aguaModel = new Agua();
        $data = [];

        $filtros = [];
        if (!empty($_GET['filtro_secretaria'])) {
            $filtros['secretaria'] = $_GET['filtro_secretaria'];
        }
        if (!empty($_GET['filtro_classe_consumo'])) {
            $filtros['classe_consumo'] = $_GET['filtro_classe_consumo'];
        }
        if (!empty($_GET['filtro_instalacao'])) {
            $filtros['instalacao'] = $_GET['filtro_instalacao'];
        }
        if (!empty($_GET['filtro_data_vencimento'])) {
            $filtros['data_vencimento'] = $_GET['filtro_data_vencimento'];
        }

        $data['registros'] = $aguaModel->buscarComFiltros($filtros);
        $data['totalPendente'] = $aguaModel->getTotalPendente();
        $data['mediaConsumo'] = $aguaModel->getMediaConsumo();
        $data['consumoMensal'] = $aguaModel->getConsumoMensal();
        
        return $data;
    }

    public function store() {
        $aguaModel = new Agua();
        $data = [
            'mes' => $_POST['mes'] ?? null,
            'local' => $_POST['local'] ?? null,
            'consumo' => $_POST['consumo'] ?? 0,
            'multa' => $_POST['multa'] ?? 0,
            'total' => $_POST['total'] ?? 0,
            'Conta_status' => $_POST['status'] ?? 'pendente',
            'valor' => $_POST['valor'] ?? 0,
            'data_vencimento' => $_POST['data_vencimento'] ?? null,
            'secretaria' => $_POST['secretaria'] ?? null,
            'classe_consumo' => $_POST['classe_consumo'] ?? null,
            'instalacao' => $_POST['instalacao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];

        if ($aguaModel->create($data)) {
            $_SESSION['success'] = "Registro criado com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao criar registro.";
        }
        header('Location: agua.php');
        exit;
    }

    public function update() {
        $aguaModel = new Agua();
        $id = $_POST['id'];
        $data = [
            'mes' => $_POST['mes'] ?? null,
            'local' => $_POST['local'] ?? null,
            'consumo' => $_POST['consumo'] ?? 0,
            'multa' => $_POST['multa'] ?? 0,
            'total' => $_POST['total'] ?? 0,
            'Conta_status' => $_POST['status'] ?? 'pendente',
            'valor' => $_POST['valor'] ?? 0,
            'data_vencimento' => $_POST['data_vencimento'] ?? null,
            'secretaria' => $_POST['secretaria'] ?? null,
            'classe_consumo' => $_POST['classe_consumo'] ?? null,
            'instalacao' => $_POST['instalacao'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        if ($aguaModel->update($id, $data)) {
            $_SESSION['success'] = "Registro atualizado com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao atualizar registro.";
        }
        header('Location: agua.php');
        exit;
    }

    public function destroy() {
        $aguaModel = new Agua();
        $id = $_POST['id'];

        if ($aguaModel->delete($id)) {
            $_SESSION['success'] = "Registro excluído com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao excluir registro.";
        }
        header('Location: agua.php');
        exit;
    }
}
