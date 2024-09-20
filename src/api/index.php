<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header('Access-Control-Allow-Methods: POST, GET, PATCH, DELETE');
header("Allow: GET, POST, PATCH, DELETE");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {    
   return 0;    
}  

$metodo = strtolower($_SERVER['REQUEST_METHOD']);
$accion = explode('/', strtolower($_GET['accion']));
$funcionNombre = $metodo . ucfirst($accion[0]);
$parametros = array_slice($accion, 1);
if (count($parametros) >0 && $metodo == 'get') {
    $funcionNombre = $funcionNombre.'ConParametros';
}
if (function_exists($funcionNombre)) {
    call_user_func_array ($funcionNombre, $parametros);
} else {
    outputError(400);
}

function outputJson($data, $codigo = 200)
{
    header('', true, $codigo);
    header('Content-type: application/json');
    print json_encode($data);
}

function outputError($codigo = 500)
{
    switch ($codigo) {
        case 400:
            header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad request", true, 400);
            die;
        case 404:
            header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found", true, 404);
            die;
        default:
            header($_SERVER["SERVER_PROTOCOL"] . " 500 Internal Server Error", true, 500);
            die;
            break;
    }
}

function inicializarBBDD() {
	return $bd = new SQLite3('peliculas.db');
}

function postRestablecer() {
	$bd = inicializarBBDD();
	$sql = file_get_contents(__DIR__ . '/dump.sql');
	$result = $bd->exec($sql);
	outputJson([]);
}

function getGeneros() {
	$bd = inicializarBBDD();
	$result = $bd->query('SELECT * FROM generos');
	$ret = [];
	while ($fila = $result->fetchArray(SQLITE3_ASSOC)) {
		settype($fila['id'], 'integer');
		$ret[] = $fila;
	}
	outputJson($ret);
}

function getPeliculas() {
	$bd = inicializarBBDD();
	$result = $bd->query('SELECT peliculas.id AS id, peliculas.titulo AS titulo, peliculas.anio AS anio, GROUP_CONCAT(generos.descripcion) AS generos FROM peliculas LEFT JOIN peliculas_generos ON peliculas.id=peliculas_generos.id_pelicula LEFT JOIN generos ON generos.id=peliculas_generos.id_genero GROUP BY peliculas.id');
	$ret = [];
	while ($fila = $result->fetchArray(SQLITE3_ASSOC)) {
		settype($fila['id'], 'integer');
		$fila['generos'] = explode(',', $fila['generos']);
		$ret[] = $fila;
	}
	outputJson($ret);
}

function getPeliculasConParametros($id) {
	$bd = inicializarBBDD();
	settype($id, 'integer');
	$result = $bd->query("SELECT peliculas.id AS id, peliculas.titulo AS titulo, peliculas.anio AS anio, GROUP_CONCAT(peliculas_generos.id_genero) AS generos FROM peliculas LEFT JOIN peliculas_generos ON peliculas.id=peliculas_generos.id_pelicula WHERE id=$id GROUP BY peliculas.id ");
	$ret = [];
	$fila = $result->fetchArray(SQLITE3_ASSOC);
	settype($fila['id'], 'integer');
	$fila['generos'] = $fila['generos']=='' ? [] : array_map(function ($v) { return $v+0;},  explode(',', $fila['generos']));
	outputJson($fila);
}

function postPeliculas() {
	$bd = inicializarBBDD();
	$datos = json_decode(file_get_contents('php://input'), true);
	
	$titulo = $bd->escapeString($datos['titulo']);
	$anio = $datos['anio']+0;

	$result = @$bd->exec("INSERT INTO peliculas (titulo, anio) VALUES ('$titulo', $anio)");
	$id = $bd->lastInsertRowID();
	foreach ($datos['generos'] as $genid) {
		$result = @$bd->exec("INSERT INTO peliculas_generos (id_pelicula, id_genero) VALUES ($id, $genid)");
	}
	outputJson(['id' => $id]);
}

function patchPeliculas($id) {
	settype($id, 'integer');
	$bd = inicializarBBDD();
	$datos = json_decode(file_get_contents('php://input'), true);
	
	$titulo = $bd->escapeString($datos['titulo']);
	$anio = $datos['anio']+0;

	$result = @$bd->exec("UPDATE peliculas SET titulo='$titulo', anio=$anio WHERE id=$id");
	$result = $bd->query("DELETE FROM peliculas_generos WHERE id_pelicula=$id");
	
	foreach ($datos['generos'] as $genid) {
		$result = @$bd->exec("INSERT INTO peliculas_generos (id_pelicula, id_genero) VALUES ($id, $genid)");
	}
	outputJson([]);
}

function deletePeliculas($id) {
	settype($id, 'integer');
	$bd = inicializarBBDD();
	$result = $bd->query("DELETE FROM peliculas WHERE id=$id");
	outputJson([]);
}

?>
