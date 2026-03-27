# 🚀 Referencia Rápida - Text Search Engine v2.0

## Acceso Rápido

### 🌐 Web Interface
```
http://localhost/mavisadev/grep.php
```

### 💻 Línea de Comando (PHP)
```bash
php -r "
require 'grep.php';
\$engine = new TextSearchEngine();
\$results = \$engine->search('TODO', true);
echo 'Encontradas: ' . count(\$results) . ' archivos\n';
"
```

---

## Búsquedas Rápidas

### 1. TODOs y FIXMEs
```
Patrón: TODO|FIXME
Regex: ✅ ON
```

### 2. Funciones Peligrosas
```
Patrón: eval\(|system\(|exec\(
Regex: ✅ ON
```

### 3. Debug Olvidado
```
Patrón: var_dump\(|print_r\(|dd\(
Regex: ✅ ON
```

### 4. Variables Globales
```
Patrón: \$GLOBALS|\$_SERVER
Regex: ✅ ON
```

### 5. SQL Hardcoded
```
Patrón: SELECT |INSERT |UPDATE |DELETE
Mayúsculas: ✅ ON
```

---

## Sintaxis Regex Útil

```
\d+              # Números (123, 456)
\w+              # Palabras (function, _var, $const)
\s+              # Espacios
.+?              # Cualquier cosa (no-greedy)
^pattern         # Inicio de línea
pattern$         # Fin de línea
a|b|c            # Alternativas
[a-z]            # Rango
[^a-z]           # Negación
a*               # 0 o más
a+               # 1 o más
a?               # 0 o 1
```

### Ejemplos Reales
```
^class\s+\w+                    # Clases
^function\s+\w+\s*\(           # Funciones
[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}  # Emails
\$_(GET|POST|REQUEST)\[        # Superglobales
```

---

## Configuración por Entorno

### 🔧 Desarrollo
```php
$engine = new DevelopmentSearchEngine(__DIR__);
// Máximo: 100MB archivos, 50K resultados
```

### 🔒 Producción
```php
$engine = new ProductionSearchEngine(__DIR__);
// Máximo: 2MB archivos, 1K resultados
```

### 🛡️ Auditoría
```php
$engine = new SecurityAuditEngine(__DIR__);
$dangerous = $engine->findDangerousFunctions();
```

### 📚 Documentación
```php
$engine = new DocumentationSearchEngine(__DIR__);
$tasks = $engine->findTasks();
```

---

## Uso Programático

### Búsqueda Simple
```php
require 'grep.php';
$engine = new TextSearchEngine(__DIR__);
$results = $engine->search('pattern');
```

### Con Opciones
```php
$results = $engine->search(
    pattern: 'class\s+\w+',
    recursive: true,
    matchCase: false,
    useRegex: true
);
```

### Procesar Resultados
```php
foreach ($results as $file => $data) {
    echo $file . ': ' . $data['matchCount'] . " matches\n";
    
    foreach ($data['lineNumbers'] as $i => $line) {
        echo "  [" . $line . "] " . $data['lines'][$i] . "\n";
    }
}
```

### Obtener Estadísticas
```php
echo "Archivos: " . $engine->getResultCount();
echo "Matches: " . $engine->getTotalMatches();
```

---

## Filtros de Archivos

### Incluir Solo Estos
```php
$engine->setIncludeExtensions([
    '*.php',      # PHP
    '*.js',       # JavaScript
    '*.html'      # HTML
]);
```

### Excluir Estos
```php
$engine->setExcludeExtensions([
    '*.min.js',      # Minificado
    '*.log',         # Logs
    'vendor/*',      # Dependencias
    'node_modules/*' # NPM
]);
```

### Combinado
```php
$engine
    ->setIncludeExtensions(['*.php'])
    ->setExcludeExtensions(['*.bak', 'vendor/*']);
```

---

## Límites de Seguridad

### Archivos Grandes
```php
$engine->setMaxFileSize(5242880);  // 5MB
```

### Demasiados Resultados
```php
$engine->setMaxResults(5000);      // 5K máximo
```

### Recommended Values
```php
// Desarrollo
->setMaxFileSize(104857600)   # 100MB
->setMaxResults(50000)        # 50K

// Producción
->setMaxFileSize(2097152)     # 2MB
->setMaxResults(1000)         # 1K
```

---

## Atajos de Búsqueda

### `QuickSearch` Helper
```php
// Busca en PHP
$results = QuickSearch::findInPHP('function', __DIR__);

// Busca en JavaScript
$results = QuickSearch::findInJS('console', __DIR__);

// Busca TODOs
$results = QuickSearch::findTODO(__DIR__);
```

---

## Problemas Comunes

### "No encuentra nada"
- [ ] ¿Escrito correctamente?
- [ ] ¿Activado "Recursivo"?
- [ ] ¿Filtros demasiado restrictivos?

### "Búsqueda lenta"
- [ ] Usa filtros específicos
- [ ] Búsqueda literal (no regex)
- [ ] Excluir directorios pesados
- [ ] Reducir maxFileSize

### "Regex no funciona"
- Valida en https://regex101.com
- Prueba con flags: `/pattern/i`
- Sin delimitadores iniciales: `pattern` no `/pattern/`

---

## Estadísticas

### Velocidad Esperada
```
1000 archivos x 100KB (búsqueda simple): 2-3 seg
1000 archivos x 100KB (regex)           : 5-8 seg
```

### Memoria
```
Por defecto: <50MB
Con límites: Muy controlado
```

### Archivos Soportados
```
✅ Texto plano
✅ Código fuente
✅ Configuración (JSON, YAML, INI)
❌ Binarios (imágenes, ejecutables)
❌ Comprimidos (.zip, .tar.gz)
```

---

## Ejemplos One-Liners

```php
// Buscar en PHP
php -r "require 'grep.php'; \$e=new TextSearchEngine(); print_r(\$e->search('function', true));"

// Contar TODO items
php -r "require 'grep.php'; \$e=new TextSearchEngine(); echo \$e->search('TODO',true); echo 'Total: '.\$e->getTotalMatches();"

// Buscar en JSON
php -r "require 'grep.php'; \$e=new TextSearchEngine(); \$e->setIncludeExtensions(['*.json']); print_r(\$e->search('key', true));"
```

---

## Integración con Mavisa

### Opción 1: Directo en controlador
```php
// En includes/controllers/search.php
require_once 'grep.php';

class SearchAction {
    public function execute() {
        $engine = new TextSearchEngine(ROOT_DIR);
        $results = $engine->search($_GET['q'], true);
        return json_encode($results);
    }
}
```

### Opción 2: Via clase personalizada
```php
// En includes/classes/class.search.php
class SearchEngine extends TextSearchEngine {
    public function __construct() {
        parent::__construct(__DIR__ . '/../..');
    }
}

// Uso
$search = new SearchEngine();
$results = $search->search('pattern');
```

---

## Validación

```bash
# Verificar sintaxis
php -l grep.php

# Test búsqueda
php -r "require 'grep.php'; echo new TextSearchEngine() ? 'OK' : 'ERROR';"
```

---

## Documentación

| Archivo | Contenido |
|---------|-----------|
| **GREP_README.md** | Guía completa |
| **GREP_MODERNIZATION.md** | Cambios técnicos |
| **GREP_EXAMPLES.php** | 15 ejemplos |
| **GREP_CONFIGURATIONS.php** | Configuraciones |
| **RESUMEN.md** | Resumen ejecutivo |
| **CHEATSHEET.md** | Esta referencia |

---

## Licencia
GNU LGPL v3 - Libre para usar y modificar

---

**v2.0** | 2025-02-04 | ✅ Producción Ready
