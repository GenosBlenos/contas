<?php
require_once __DIR__ . '/../models/Unidade.php';
class UnidadesController {
    public function index() {
        $model = new Unidade();
        return $model->all();
    }
}
