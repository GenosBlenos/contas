<?php
require_once __DIR__ . '/../models/Recomendacao.php';
class RecomendacoesController {
    public function index() {
        $model = new Recomendacao();
        return $model->all();
    }
}
