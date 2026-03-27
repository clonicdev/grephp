# 🔍 Text Search Engine v2.0 - Guía de Uso

## ¿Qué es?

Una herramienta moderna para buscar patrones de texto en archivos del servidor, reemplazando la versión antigua de 2005 que dependía del comando `grep` del sistema.

**Ventajas clave:**
- ✅ **Seguro**: Sin inyección de comandos
- ✅ **Multiplataforma**: Funciona en Windows, Linux, macOS sin cambios
- ✅ **Moderno**: PHP 7.4+, OOP, type hints
- ✅ **Monolítico**: Todo en un archivo, sin dependencias
- ✅ **Responsive**: Interfaz web moderna y mobile-friendly

---

## 🚀 Inicio Rápido

### Opción 1: Acceder por Web

1. Abre `http://localhost/mavisadev/grep.php` en tu navegador
2. Ingresa el texto a buscar
3. Configura opciones (recursivo, mayúsculas, etc.)
4. Haz clic en "🔎 Search"

### Opción 2: Usar desde PHP

```php
require_once 'grep.php';

$engine = new TextSearchEngine(__DIR__);
$results = $engine->search('TODO', recursive: true);

foreach ($results as $file => $data) {
    echo "$file: " . $data['matchCount'] . " matches\n";
}
```

---

## 🎯 Casos de Uso

### 1. Encontrar TODOs y FIXMEs
```
Patrón: TODO|FIXME
Opciones: Regex activado, Recursivo
```

### 2. Buscar funciones deprecated
```
Patrón: mysql_|split\(|ereg\(
Opciones: Regex activado, Recursivo
```

### 3. Encontrar debug olvidado
```
Patrón: var_dump|print_r|console\.log
Opciones: Regex activado, Incluir: *.php, *.js
```

### 4. Búsqueda simple
```
Patrón: nombre de función
Opciones: Case-insensitive, Recursivo
```

### 5. Encontrar SQL queries hardcoded
```
Patrón: SELECT |INSERT |UPDATE |DELETE 
Opciones: Mayúsculas activado, Incluir: *.php
```

---

## 🔧 Opciones de Búsqueda

### Patrón de Búsqueda
- **Texto literal**: Busca la cadena exacta
- **Con Regex**: Activa búsqueda con expresiones regulares

### Recursivo / No Recursivo
- **Recursivo**: Busca en el directorio actual y subdirectorios
- **No recursivo**: Solo archivos en el directorio especificado

### Mayúsculas/Minúsculas
- **Activado**: `FUNCTION` ≠ `function`
- **Desactivado** (defecto): `FUNCTION` = `function`

### Incluir Archivos
Patrón glob para archivos a incluir:
- `*.php` - Solo PHP
- `*.html,*.htm` - HTML
- `*.php,*.html` - PHP e HTML

Dejar vacío = todos los archivos

### Excluir Archivos
Patrón glob para archivos a ignorar:
- `*.min.js` - JavaScript minificado
- `*.log,*.tmp` - Logs y temporales
- `vendor/*` - Directorios

### Usar Expresiones Regulares
Activa búsqueda avanzada con regex:

```regex
^class\s+\w+                          # Definiciones de clases
function\s+\w+\s*\(                   # Definiciones de funciones
[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}  # Emails
\$_\[GET|POST|REQUEST\]               # Variables superglobales
require|include|require_once           # Inclusiones
```

---

## 📊 Entender los Resultados

### Ejemplo de resultado:
```
📄 includes/functions.php (3 matches)
   123 | function getUserData($id) {
   456 | function validateEmail($email) {
   789 | function formatPhoneNumber($phone) {
```

- **📄 Archivo**: Ruta relativa al servidor
- **Número en azul**: Línea del archivo donde está el match
- **Texto resaltado**: La parte que coincide con tu búsqueda

---

## ⚡ Trucos y Tips

### 1. Búsqueda Rápida de Errores en Producción
```
Patrón: error|warning|fatal
Opciones: Regex activado, No mayúsculas
```

### 2. Auditar Funciones Peligrosas
```
Patrón: eval\(|system\(|exec\(|shell_exec\(
Opciones: Regex activado, Solo *.php
```

### 3. Encontrar Variables Globales
```
Patrón: \$GLOBALS|\$_SERVER|\$_ENV
Opciones: Regex activado, Solo *.php
```

### 4. Buscar Comentarios
```
Patrón: //|/\*|#
Opciones: Regex activado (para precisión)
```

### 5. Migración de Código
```
Patrón: old_function_name|OldClassName
Opciones: Case-sensitive, Recursivo, Solo *.php
```

---

## 🔐 Seguridad

### Lo que está protegido:

✅ **Inyección de Comandos**: No usa `exec()`, `system()`, etc.  
✅ **Directory Traversal**: No permite `../../../etc/passwd`  
✅ **XSS**: Output escapado con `htmlspecialchars()`  
✅ **Regex Injection**: Validación de patrones  
✅ **Symlink Loops**: Ignora symbolic links  

### Buenas Prácticas:

1. **Limita a directorios específicos**:
   ```php
   // ✅ Seguro
   new TextSearchEngine('/var/www/app/includes');
   
   // ❌ Riesgoso
   new TextSearchEngine('/');
   ```

2. **Usa filtros de extensión**:
   ```php
   // ✅ Mejor performance y seguridad
   $engine->setIncludeExtensions(['*.php']);
   
   // ❌ Más lento y abre más archivos
   // Sin filtro
   ```

3. **Establece límites**:
   ```php
   $engine->setMaxFileSize(5242880);  // 5MB
   $engine->setMaxResults(5000);      // 5000 matches
   ```

---

## 🐛 Troubleshooting

### "No se encuentran resultados"

- [ ] ¿Escribiste bien el patrón?
- [ ] ¿Activaste "Recursivo" si buscas en subdirectorios?
- [ ] ¿El archivo tiene la extensión correcta?
- [ ] ¿Está excluido por los filtros?

### "Búsqueda muy lenta"

- [ ] Usa filtros más específicos (incluir extensiones)
- [ ] Busca en directorios más pequeños
- [ ] Usa búsqueda literal en lugar de Regex
- [ ] Verifica el tamaño de los archivos (excluir logs)

### "Regex no funciona"

- [ ] Verifica que la regex sea válida (usa [regex101.com](https://regex101.com))
- [ ] Usa `/` como delimitador: `/pattern/flags`
- [ ] Flags disponibles: `i` (case-insensitive), `m` (multiline)

Ejemplo:
```
/todo|fixme/i   ✅ Busca TODO o FIXME (mayúscula insensible)
/^function /m   ✅ Funciones al inicio de línea
[invalid(       ❌ Regex inválida
```

---

## 📚 Ejemplos de Expresiones Regulares Útiles

| Descripción | Patrón | Ejemplo |
|---|---|---|
| Números | `\d+` | Encuentra 123, 456 |
| Palabras | `\w+` | Encuentra word, _private, $var |
| Espacios en blanco | `\s+` | Tabulaciones, espacios, saltos de línea |
| Cualquier carácter | `.+?` | any string |
| Inicio de línea | `^pattern` | `^class` solo al inicio |
| Fin de línea | `pattern$` | `;$` punto y coma al final |
| Grupo alternativo | `a\|b\|c` | Busca a, b, o c |
| 0 o más | `a*` | `a`, `aa`, `aaa` |
| 1 o más | `a+` | `aa`, `aaa` (no `a` solo) |
| Opcional | `a?` | `a` o sin `a` |
| Rango | `[a-z]` | Cualquier minúscula |
| Negación | `[^a-z]` | Cualquier que NO sea minúscula |

---

## 🔄 Comparación con Versión Anterior

| Aspecto | v1.0 (2005) | v2.0 (2025) |
|---|---|---|
| **Velocidad** | Depende del sistema operativo | Consistente PHP puro |
| **Compatibilidad** | Solo Linux con grep | Windows, Linux, macOS |
| **Seguridad** | ❌ Vulnerable | ✅ Segura |
| **Funcionalidad** | Básica | Avanzada (Regex, filtros) |
| **Interfaz** | 2001 | Moderna y responsive |
| **Mantenibilidad** | Baja | Alta (OOP) |

---

## 📝 Notas Técnicas

### Límites por Defecto:
- Máximo archivo: 5 MB
- Máximo resultados: 10,000 matches
- Máximo patrón: 100 caracteres

### Performance Esperado:
- 1000 archivos PHP (~100KB c/u): ~2-3 segundos
- Búsqueda simple vs Regex: 3-5x más rápida la simple
- Windows vs Linux: Performance similar (PHP puro)

### Formatos Soportados:
- Archivos de texto plano
- Código fuente (PHP, Python, JavaScript, etc.)
- Archivos de configuración (JSON, INI, YAML)
- Datos (CSV, SQL dumps)

### No soportados:
- Archivos binarios (imágenes, executables)
- Archivos comprimidos (.zip, .tar.gz)
- Documentos Office (.docx, .xlsx)

---

## 🆘 Soporte y Reporte de Bugs

Si encuentras problemas:

1. Verifica que uses PHP 7.4 o superior
2. Confirma que el directorio es legible
3. Revisa los logs del servidor
4. Valida tus patrones Regex en [regex101.com](https://regex101.com)

---

## 📄 Licencia

Distribuido bajo GNU LGPL v3

Original (2005): Alejandro Vásquez  
Modernización (2025): Mejoras de seguridad y funcionalidad

---

**¡Listo!** Ahora puedes buscar en tu servidor de forma segura y eficiente. 🚀
