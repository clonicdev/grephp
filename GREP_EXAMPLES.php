<?php
/**
 * EJEMPLOS DE USO - Text Search Engine v2.0
 * 
 * Este archivo contiene ejemplos de cómo usar el TextSearchEngine
 * directamente desde tu código PHP, sin pasar por la interfaz web.
 * 
 * Puedes copiar estos ejemplos e integrarlos en tus controladores
 * de Mavisa.
 */

// ============================================================================
// EJEMPLO 1: BÚSQUEDA SIMPLE
// ============================================================================
/*
require_once __DIR__ . '/grep.php';

$engine = new TextSearchEngine(__DIR__);
$results = $engine->search('TODO');

echo "Encontradas " . $engine->getResultCount() . " archivos\n";
echo "Total de matches: " . $engine->getTotalMatches() . "\n";

foreach ($results as $file => $data) {
    echo "\nArchivo: $file\n";
    echo "  Matches: " . $data['matchCount'] . "\n";
    foreach ($data['lineNumbers'] as $i => $lineNum) {
        echo "    Línea $lineNum: " . strip_tags($data['lines'][$i]) . "\n";
    }
}
*/

// ============================================================================
// EJEMPLO 2: BÚSQUEDA RECURSIVA SOLO EN PHP
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__ . '/includes');
$engine->setIncludeExtensions(['*.php']);

$results = $engine->search('function', recursive: true, matchCase: true);
*/

// ============================================================================
// EJEMPLO 3: BÚSQUEDA CON REGEX
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);

// Buscar todas las líneas que comienzan con "class "
$results = $engine->search(
    '^class\s+\w+',
    recursive: true,
    matchCase: true,
    useRegex: true
);

// Buscar direcciones de email
$results = $engine->search(
    '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}',
    recursive: true,
    useRegex: true
);

// Buscar números de teléfono
$results = $engine->search(
    '\+?[0-9]{1,3}[\s.-]?[0-9]{1,4}[\s.-]?[0-9]{1,4}[\s.-]?[0-9]{1,9}',
    recursive: true,
    useRegex: true
);
*/

// ============================================================================
// EJEMPLO 4: BÚSQUEDA EXCLUIR DIRECTORIOS
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);

$engine->setExcludeExtensions([
    '*.min.js',      // JavaScript minificado
    '*.log',         // Archivos de log
    'vendor/*',      // Dependencias
    'node_modules/*', // NPM packages
    '*.tmp',         // Archivos temporales
]);

$results = $engine->search('error', recursive: true);
*/

// ============================================================================
// EJEMPLO 5: BÚSQUEDA SOLO ARCHIVOS ESPECÍFICOS
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__ . '/includes/classes');

$engine->setIncludeExtensions([
    '*.php'  // Solo PHP
]);

$results = $engine->search('public function', recursive: true);
*/

// ============================================================================
// EJEMPLO 6: BÚSQUEDA CON LÍMITES DE SEGURIDAD
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);

// Configuración para producción
$engine->setMaxResults(1000);        // Máximo 1000 resultados
$engine->setMaxFileSize(2097152);    // Máximo 2MB por archivo

$results = $engine->search('SELECT * FROM', recursive: true);

// Comprobar si llegamos al límite
if ($engine->getTotalMatches() >= 1000) {
    echo "Aviso: Se alcanzó el límite de resultados\n";
}
*/

// ============================================================================
// EJEMPLO 7: INTEGRACIÓN CON TU SISTEMA MAVISA
// ============================================================================
/*
// En un controlador de Mavisa, por ejemplo en includes/controllers/search.php
class SearchAction
{
    private TextSearchEngine $engine;
    
    public function __construct()
    {
        $this->engine = new TextSearchEngine(ROOT_DIR);
    }
    
    public function search()
    {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            return json_response(['error' => 'Query required']);
        }
        
        try {
            $this->engine->setMaxResults(500);
            $results = $this->engine->search($query, recursive: true);
            
            return json_response([
                'success' => true,
                'query' => $query,
                'results' => $results,
                'count' => $this->engine->getResultCount(),
                'matches' => $this->engine->getTotalMatches()
            ]);
        } catch (Exception $e) {
            return json_response(['error' => $e->getMessage()], 500);
        }
    }
}
*/

// ============================================================================
// EJEMPLO 8: BÚSQUEDA CASE-SENSITIVE
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);

// Buscar "class" exactamente (mayúscula)
// No encontrará "CLASS" ni "Class"
$results = $engine->search(
    'class',
    recursive: true,
    matchCase: true  // ← Activar sensibilidad a mayúsculas
);
*/

// ============================================================================
// EJEMPLO 9: BÚSQUEDA INSENSIBLE A MAYÚSCULAS (POR DEFECTO)
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);

// Buscar "function" en cualquier formato
// Encontrará: function, Function, FUNCTION, FuNcTiOn
$results = $engine->search(
    'function',
    recursive: true,
    matchCase: false  // ← Defecto: insensible
);
*/

// ============================================================================
// EJEMPLO 10: PROCESAR RESULTADOS AVANZADO
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);
$results = $engine->search('TODO', recursive: true);

// Agrupar por archivo
$fileGroups = [];
foreach ($results as $file => $data) {
    $fileGroups[$file] = [
        'count' => $data['matchCount'],
        'path' => $file,
        'lines' => $data['lineNumbers']
    ];
}

// Ordenar por cantidad de matches (descendente)
usort($fileGroups, function($a, $b) {
    return $b['count'] <=> $a['count'];
});

// Mostrar top 10 archivos con más matches
$top10 = array_slice($fileGroups, 0, 10);
foreach ($top10 as $file => $info) {
    echo sprintf(
        "%s - %d matches en líneas: %s\n",
        $info['path'],
        $info['count'],
        implode(', ', $info['lines'])
    );
}
*/

// ============================================================================
// EJEMPLO 11: BÚSQUEDA DETECTAR PATRONES COMUNES
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);

// Buscar TODO comments
$todos = $engine->search(
    'TODO|FIXME|HACK|XXX',
    recursive: true,
    useRegex: true
);

// Buscar funciones MySQL antiguas
$mysql = $engine->search(
    'mysql_|sqlite_',
    recursive: true,
    useRegex: true
);

// Buscar console.log
$console = $engine->search(
    'console\.(log|error|warn)',
    recursive: true,
    useRegex: true
);

// Buscar var_dump/print_r olvidados
$debug = $engine->search(
    'var_dump\(|print_r\(',
    recursive: true,
    useRegex: true
);
*/

// ============================================================================
// EJEMPLO 12: ERROR HANDLING
// ============================================================================
/*
try {
    $engine = new TextSearchEngine('/ruta/invalida');
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage();
}

try {
    $engine = new TextSearchEngine(__DIR__);
    $results = $engine->search(
        '[invalid(regex',  // Regex inválido
        useRegex: true
    );
} catch (InvalidArgumentException $e) {
    echo "Regex error: " . $e->getMessage();
}
*/

// ============================================================================
// EJEMPLO 13: ESTADÍSTICAS DE BÚSQUEDA
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);

$startTime = microtime(true);
$results = $engine->search('require', recursive: true);
$elapsed = microtime(true) - $startTime;

echo "=== Estadísticas de Búsqueda ===\n";
echo "Duración: " . round($elapsed, 4) . " segundos\n";
echo "Archivos encontrados: " . $engine->getResultCount() . "\n";
echo "Total de matches: " . $engine->getTotalMatches() . "\n";
echo "Velocidad: " . round($engine->getTotalMatches() / $elapsed, 0) . " matches/segundo\n";
*/

// ============================================================================
// EJEMPLO 14: BÚSQUEDA INCREMENTAL CON FILTROS
// ============================================================================
/*
function searchPhpFiles($pattern)
{
    $engine = new TextSearchEngine(__DIR__ . '/includes');
    
    // Primero filter: solo PHP
    $engine->setIncludeExtensions(['*.php']);
    
    // Segundo filter: excluir backups
    $engine->setExcludeExtensions(['*.bk', '*.bk2', '*.bk3']);
    
    // Ejecutar búsqueda
    return $engine->search($pattern, recursive: true);
}

// Uso:
$controllers = searchPhpFiles('function handle');
*/

// ============================================================================
// EJEMPLO 15: BÚSQUEDA GUARDAR RESULTADOS A ARCHIVO
// ============================================================================
/*
$engine = new TextSearchEngine(__DIR__);
$results = $engine->search('deprecated', recursive: true);

$report = fopen('search_report.txt', 'w');
foreach ($results as $file => $data) {
    fwrite($report, "=== $file ===\n");
    foreach ($data['lineNumbers'] as $i => $lineNum) {
        $line = strip_tags($data['lines'][$i]);
        fwrite($report, "[$lineNum] $line\n");
    }
    fwrite($report, "\n");
}
fclose($report);

echo "Reporte guardado en search_report.txt";
*/

// ============================================================================
// NOTAS IMPORTANTES
// ============================================================================
/*
1. SEGURIDAD:
   - Nunca pasar user input directamente a setIncludeExtensions/setExcludeExtensions
   - Validar siempre el baseDirectory
   - Usar realpath() para convertir paths relativos

2. PERFORMANCE:
   - Para búsquedas en repositorios grandes, usar filtros restrictivos
   - Establecer límites de resultados razonables
   - Considerar indexación si hay muchas búsquedas frecuentes

3. REGEX:
   - Las regex pueden ser lentas en archivos grandes
   - Preferir búsqueda literal cuando sea posible
   - Usar anchors (^, $) para acelerar

4. ARCHIVOS BINARIOS:
   - El motor NO detecta archivos binarios
   - Pueden causar problemas con ciertos patrones
   - Excluir extensiones binarias: *.jpg, *.png, *.gif, *.zip, etc.

5. SYMLINKS:
   - Los symbolic links son ignorados para evitar loops
   - Esto es seguridad by default

6. MEMORIA:
   - setMaxFileSize limita lectura individual
   - setMaxResults limita total de matches
   - No hay caché - cada búsqueda es nueva
*/

?>
