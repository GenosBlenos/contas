<?php
require_once __DIR__ . '/../models/Documento.php';
class DocumentosController {
    public function index() {
        $model = new Documento();
        return $model->all();
    }
}
