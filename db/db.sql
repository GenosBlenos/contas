CREATE TABLE 'energia' (
    'id_energia' int not NULL primary key,
    'mes' int (2),
    'instalacao' int (10),
    'vencimento' date,
    'consumo' float not NULL,
    'multa' float,
    'total' float,
);

CREATE TABLE 'telefone' (
    'id_telefone' int not NULL primary key,
    'mes' int (2),
    'numero' int (1),
    'tridigito' int,
    'vencimento' date,
    'consumo' float not NULL,
    'multa' float,
    'cobrar' float,
    'total' float,
);

CREATE TABLE 'agua' (
    'id_agua' int NOT NULL primary key,
    'mes' int (2),
    'medido' int not NULL,
    'faturado' int,
    'tarifa' float,
    'afastamento' float,
    'esgoto' float,
    'desconto' float,
    'outros' float,
    'vencimento' date
    'multa' float,
    'total' float,
);

CREATE TABLE 'estagiario' (
    'nome' varchar not NULL primary key,
    'unidade' varchar,
    'pagamento' varchar,
    'secretaria' varchar,
    'contrato' date,
    'mes' int (2),
);

CREATE TABLE 'semparar' (
    'placa' varchar not NULL primary key,
    'combustivel' varchar,
    'veiculo' varchar,
    'marca' varchar,
    'modelo' varchar,
    'tipo' varchar,
    'departamento' varchar,
    'ficha' int,
    'secretaria' varchar,
    'tag' int,
    'mensalidade' float,
    'passagens' float,
    'estacionamento' float,
    'estabelecimentos' float,
    'credito' float,
    'isento' boolean,
    'mes' int (2),
    'total' float,
);

select cast(tarifa as decimal(10,3)) from agua;

select cast(afastamento as decimal(10,3)) from agua;

select cast(esgoto as decimal(10,3)) from agua;