<?php
require_once __DIR__ . '/../models/Energia.php';

class EnergiaController {
    public function index() {
        $energiaModel = new Energia();
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

        $data['registros'] = $energiaModel->buscarComFiltros($filtros);
        $data['totalPendente'] = $energiaModel->getTotalPendente();
        $data['mediaConsumo'] = $energiaModel->getMediaConsumo();
        $data['consumoMensal'] = $energiaModel->getConsumoMensal();
        
        return $data;
    }

    public function store() {
        $energiaModel = new Energia();
        $data = [
            'mes' => $_POST['mes'] ?? null,
            'local' => $_POST['local'] ?? null,
            'instalacao' => $_POST['instalacao'] ?? null,
            'consumo' => $_POST['consumo'] ?? 0,
            'multa' => $_POST['multa'] ?? 0,
            'total' => $_POST['total'] ?? 0,
            'Conta_status' => $_POST['status'] ?? 'pendente',
            'valor' => $_POST['valor'] ?? 0,
            'data_vencimento' => $_POST['data_vencimento'] ?? null,
            'secretaria' => $_POST['secretaria'] ?? null,
            'classe_consumo' => $_POST['classe_consumo'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null,
            'criado_por' => $_SESSION['usuario_id']
        ];

        if ($energiaModel->create($data)) {
            $_SESSION['success'] = "Registro criado com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao criar registro.";
        }
        header('Location: energia.php');
        exit;
    }

    public function update() {
        $energiaModel = new Energia();
        $id = $_POST['id'];
        $data = [
            'mes' => $_POST['mes'] ?? null,
            'local' => $_POST['local'] ?? null,
            'instalacao' => $_POST['instalacao'] ?? null,
            'consumo' => $_POST['consumo'] ?? 0,
            'multa' => $_POST['multa'] ?? 0,
            'total' => $_POST['total'] ?? 0,
            'Conta_status' => $_POST['status'] ?? 'pendente',
            'valor' => $_POST['valor'] ?? 0,
            'data_vencimento' => $_POST['data_vencimento'] ?? null,
            'secretaria' => $_POST['secretaria'] ?? null,
            'classe_consumo' => $_POST['classe_consumo'] ?? null,
            'observacoes' => $_POST['observacoes'] ?? null
        ];

        if ($energiaModel->update($id, $data)) {
            $_SESSION['success'] = "Registro atualizado com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao atualizar registro.";
        }
        header('Location: energia.php');
        exit;
    }

    public function destroy() {
        $energiaModel = new Energia();
        $id = $_POST['id'];

        if ($energiaModel->delete($id)) {
            $_SESSION['success'] = "Registro excluído com sucesso!";
        } else {
            $_SESSION['error'] = "Erro ao excluir registro.";
        }
        header('Location: energia.php');
        exit;
    }
}
