<?php
/**
 * CONFIGURACIÓN RECOMENDADA - Text Search Engine v2.0
 * 
 * Este archivo contiene configuraciones pre-definidas para diferentes
 * escenarios. Copia la configuración que necesites a tu código.
 */

// ============================================================================
// CONFIGURACIÓN 1: DESARROLLO LOCAL
// ============================================================================
/**
 * Uso: Máxima comodidad durante desarrollo
 * Entorno: Tu máquina de desarrollo
 */
class DevelopmentSearchEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        
        // Configuración de desarrollo
        $this->setMaxFileSize(104857600);   // 100MB (muy permisivo)
        $this->setMaxResults(50000);        // Muchos resultados
    }
}

// Uso:
// $engine = new DevelopmentSearchEngine(__DIR__);
// $results = $engine->search('TODO', recursive: true);


// ============================================================================
// CONFIGURACIÓN 2: PRODUCCIÓN (CONSERVADORA)
// ============================================================================
/**
 * Uso: Máxima seguridad en servidor de producción
 * Entorno: Servidor en vivo con usuarios
 */
class ProductionSearchEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        
        // Configuración segura
        $this->setMaxFileSize(2097152);     // 2MB
        $this->setMaxResults(1000);         // Pocos resultados
        
        // Excluir archivos peligrosos por defecto
        $this->setExcludeExtensions([
            '*.log',          // Archivos de log (pueden ser enormes)
            '*.tmp',          // Temporales
            '*.bak',          // Backups
            '*.cache',        // Cache
            'vendor/*',       // Dependencias
            'node_modules/*', // NPM packages
            '.git/*',         // Git metadata
            '*.min.js',       // Minificado
            '*.min.css',      // Minificado
        ]);
    }
}

// Uso:
// $engine = new ProductionSearchEngine('/var/www/html/app');
// $results = $engine->search($userQuery, recursive: true);


// ============================================================================
// CONFIGURACIÓN 3: AUDITORÍA DE SEGURIDAD
// ============================================================================
/**
 * Uso: Buscar vulnerabilidades de seguridad
 * Entorno: Análisis de código
 */
class SecurityAuditEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        
        $this->setMaxFileSize(5242880);     // 5MB
        $this->setMaxResults(5000);
        $this->setIncludeExtensions(['*.php', '*.js', '*.py']); // Solo código
        $this->setExcludeExtensions(['*.min.js', 'vendor/*', 'node_modules/*']);
    }
    
    /**
     * Buscar funciones peligrosas
     */
    public function findDangerousFunctions(): array
    {
        $dangerous = [
            'eval(' => 'Code execution',
            'system(' => 'Command execution',
            'exec(' => 'Command execution',
            'shell_exec(' => 'Shell execution',
            'passthru(' => 'Command execution',
            'proc_open(' => 'Process execution',
            'popen(' => 'Pipe execution',
            'mysql_' => 'Deprecated database',
            'md5(' => 'Weak hash (use bcrypt/Argon2)',
            'sha1(' => 'Weak hash (use bcrypt/Argon2)',
        ];
        
        $results = [];
        foreach ($dangerous as $function => $description) {
            $found = $this->search($function, recursive: true);
            if (!empty($found)) {
                $results[$function] = [
                    'description' => $description,
                    'locations' => $found,
                    'count' => $this->getTotalMatches()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Buscar malas prácticas
     */
    public function findBadPractices(): array
    {
        $patterns = [
            'hardcoded_password' => '/password\s*=\s*["\'].*["\']/',
            'sql_injection' => '\$_(GET|POST|REQUEST)\[',
            'unvalidated_input' => '/\$_GET\[|\$_POST\[/',
            'var_dump_debug' => 'var_dump\(|print_r\(|dd\(',
            'global_variables' => 'global \$|\\$GLOBALS',
        ];
        
        $results = [];
        foreach ($patterns as $name => $pattern) {
            $found = $this->search($pattern, recursive: true, useRegex: true);
            if (!empty($found)) {
                $results[$name] = $found;
            }
        }
        
        return $results;
    }
}

// Uso:
// $audit = new SecurityAuditEngine('/var/www/html/app');
// $dangerous = $audit->findDangerousFunctions();
// $bad = $audit->findBadPractices();


// ============================================================================
// CONFIGURACIÓN 4: BÚSQUEDA DE DOCUMENTACIÓN
// ============================================================================
/**
 * Uso: Buscar en comentarios y documentación
 * Entorno: Generación de documentación
 */
class DocumentationSearchEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        
        $this->setMaxFileSize(5242880);
        $this->setMaxResults(10000);
        $this->setIncludeExtensions(['*.php', '*.md', '*.txt', '*.rst']);
    }
    
    /**
     * Buscar TODOs y FIXMEs
     */
    public function findTasks(): array
    {
        $tasks = [];
        
        // TODO items
        $todo = $this->search('/TODO|FIXME|XXX|HACK|BUG/i', recursive: true, useRegex: true);
        $tasks['todo'] = $todo;
        
        return $tasks;
    }
    
    /**
     * Buscar comentarios desactualizados
     */
    public function findObsoleteComments(): array
    {
        $obsolete = [
            'deprecated_function' => '// Deprecated',
            'old_version' => 'Version 1.',
            'obsolete' => '@deprecated|@obsolete|old code',
        ];
        
        $results = [];
        foreach ($obsolete as $name => $pattern) {
            $found = $this->search($pattern, recursive: true, useRegex: false);
            if (!empty($found)) {
                $results[$name] = $found;
            }
        }
        
        return $results;
    }
}

// Uso:
// $docs = new DocumentationSearchEngine(__DIR__);
// $tasks = $docs->findTasks();


// ============================================================================
// CONFIGURACIÓN 5: BÚSQUEDA PERFORMANTE (ARCHIVOS GRANDES)
// ============================================================================
/**
 * Uso: Repositorios muy grandes con muchos archivos
 * Entorno: Codebases gigantes (1000+ archivos)
 */
class LargeRepositoryEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        
        // Muy restrictivo para performance
        $this->setMaxFileSize(1048576);     // 1MB máximo
        $this->setMaxResults(500);          // Pocos resultados
        
        // Excluir directorios pesados
        $this->setExcludeExtensions([
            'vendor/*',
            'node_modules/*',
            '.git/*',
            '.svn/*',
            'dist/*',
            'build/*',
            'coverage/*',
            '*.log',
            '*.cache',
            '*.tmp',
            '*.bak',
            '*.min.js',
            '*.min.css',
        ]);
    }
}

// Uso:
// $engine = new LargeRepositoryEngine('/huge/codebase');
// $results = $engine->search('critical_function', recursive: true);


// ============================================================================
// CONFIGURACIÓN 6: BÚSQUEDA POR TIPO DE ARCHIVO
// ============================================================================

class PHPOnlyEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        $this->setIncludeExtensions(['*.php']);
        $this->setExcludeExtensions(['*.min.php', 'vendor/*']);
        $this->setMaxFileSize(5242880);
        $this->setMaxResults(5000);
    }
}

class JavaScriptOnlyEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        $this->setIncludeExtensions(['*.js', '*.jsx', '*.ts', '*.tsx']);
        $this->setExcludeExtensions(['*.min.js', 'node_modules/*']);
        $this->setMaxFileSize(5242880);
        $this->setMaxResults(5000);
    }
}

class ConfigOnlyEngine extends TextSearchEngine
{
    public function __construct(string $baseDir = '')
    {
        parent::__construct($baseDir);
        $this->setIncludeExtensions(['*.json', '*.yaml', '*.yml', '*.ini', '*.conf']);
        $this->setMaxFileSize(1048576);
        $this->setMaxResults(1000);
    }
}

// Uso:
// $php = new PHPOnlyEngine(__DIR__);
// $results = $php->search('namespace', recursive: true);


// ============================================================================
// CONFIGURACIÓN 7: BÚSQUEDA RÁPIDA (SÍNTESIS)
// ============================================================================

/**
 * Helper para búsquedas rápidas sin instanciar
 */
class QuickSearch
{
    /**
     * Búsqueda rápida en directorio específico
     */
    public static function find(
        string $pattern,
        string $directory = __DIR__,
        array $includeExt = ['*'],
        array $excludeExt = []
    ): array {
        $engine = new TextSearchEngine($directory);
        
        if (!empty($includeExt) && !in_array('*', $includeExt)) {
            $engine->setIncludeExtensions($includeExt);
        }
        
        if (!empty($excludeExt)) {
            $engine->setExcludeExtensions($excludeExt);
        }
        
        return $engine->search($pattern, recursive: true);
    }
    
    /**
     * Búsqueda en PHP
     */
    public static function findInPHP(string $pattern, string $directory = __DIR__): array
    {
        return self::find($pattern, $directory, ['*.php'], ['vendor/*']);
    }
    
    /**
     * Búsqueda en JavaScript
     */
    public static function findInJS(string $pattern, string $directory = __DIR__): array
    {
        return self::find($pattern, $directory, ['*.js', '*.jsx'], ['node_modules/*', '*.min.js']);
    }
    
    /**
     * Búsqueda en Todo
     */
    public static function findTODO(string $directory = __DIR__): array
    {
        return self::find('TODO|FIXME', $directory, ['*'], [], true);
    }
}

// Uso:
// $results = QuickSearch::findInPHP('function myFunc');
// $todos = QuickSearch::findTODO(__DIR__);


// ============================================================================
// RECOMENDACIONES POR TIPO DE PROYECTO
// ============================================================================

/*
TIPO DE PROYECTO: Web Application (PHP + JavaScript)
RECOMENDACIÓN: ProductionSearchEngine o DevelopmentSearchEngine

    $env = getenv('APP_ENV');
    $engine = ($env === 'production') 
        ? new ProductionSearchEngine(__DIR__) 
        : new DevelopmentSearchEngine(__DIR__);


TIPO DE PROYECTO: Librería/Package
RECOMENDACIÓN: PHPOnlyEngine con documentación

    $engine = new PHPOnlyEngine(__DIR__ . '/src');
    $docs = new DocumentationSearchEngine(__DIR__);


TIPO DE PROYECTO: Auditoría de Seguridad
RECOMENDACIÓN: SecurityAuditEngine

    $audit = new SecurityAuditEngine('/path/to/app');
    $dangerous = $audit->findDangerousFunctions();


TIPO DE PROYECTO: Repositorio Gigante (>1000 archivos)
RECOMENDACIÓN: LargeRepositoryEngine

    $engine = new LargeRepositoryEngine('/huge/codebase');
    // Con muchas exclusiones para velocidad


TIPO DE PROYECTO: Migración de Código
RECOMENDACIÓN: ProductionSearchEngine + regex específicos

    $engine = new ProductionSearchEngine($sourceDir);
    $engine->setIncludeExtensions(['*.php']);
    
    // Buscar función antigua
    $old = $engine->search('old_function_name', recursive: true);
    // Buscar nueva función
    $new = $engine->search('new_function_name', recursive: true);
*/

?>
