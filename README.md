# 🔍 GrepSearch - Modern Text Search Engine

Buscador de patrones de texto en archivos del servidor. Rápido, seguro y sin dependencias.

[![License: LGPL v3](https://img.shields.io/badge/License-LGPL%20v3-blue.svg)](https://www.gnu.org/licenses/lgpl-3.0)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4+-blue.svg)](https://php.net)

---

## ⚡ Inicio Rápido

### En 30 segundos:

1. Sube **solo** `grep.php` a tu servidor
2. Abre `http://tuservidor/grep.php` en el navegador
3. ¡Listo! Comienza a buscar

**Sin instalaciones, sin dependencias, sin configuración.**

---

## 🎯 ¿Qué es GrepSearch?

Una herramienta web para buscar texto/patrones en archivos de tu servidor, reemplazando comandos `grep` con una interfaz moderna y segura.

### Casos de uso comunes:

- 🔍 Encontrar funciones o clases en tu código
- 🐛 Buscar TODOs, FIXMEs y debug olvidado
- 🔐 Auditar funciones peligrosas (`eval`, `system`, etc.)
- 📝 Buscar texto en documentación
- 🔄 Migrar código (encontrar funciones deprecated)

---

## 🚀 Instalación

### Opción 1: Web (Recomendada)

```bash
# Solo sube el archivo principal
cp grep.php /var/www/html/
```

Accede a `http://tuservidor/grep.php`

### Opción 2: Como librería PHP

```php
require_once 'grep.php';

$engine = new TextSearchEngine(__DIR__);
$results = $engine->search('TODO', recursive: true);

foreach ($results as $file => $data) {
    echo "$file: {$data['matchCount']} matches\n";
}
```

---

## 📖 Documentación

| Archivo | Descripción |
|---------|-------------|
| [`grep.php`](grep.php) | **Archivo principal** - Funciona solo |
| [`GREP_README.md`](GREP_README.md) | Guía completa de uso |
| [`GREP_CONFIGURATIONS.php`](GREP_CONFIGURATIONS.php) | Configuraciones predefinidas |
| [`GREP_EXAMPLES.php`](GREP_EXAMPLES.php) | Ejemplos de código |

> **Nota:** Solo necesitas `grep.php`. Los demás archivos son documentación y utilidades opcionales.

---

## ✨ Características

| Feature | Descripción |
|---------|-------------|
| 🔒 **Seguro** | Sin inyección de comandos, validación de paths, XSS protegido |
| 📦 **Monolítico** | Todo en un archivo, cero dependencias |
| 🌐 **Multiplataforma** | Windows, Linux, macOS - mismo código |
| 📱 **Responsive** | Interfaz moderna que funciona en móviles |
| ⚡ **Rápido** | PHP puro optimizado, sin llamadas al sistema |
| 🎨 **Dark Mode** | Interfaz oscura moderna (GitHub-style) |
| 🔍 **Regex** | Soporte completo para expresiones regulares |
| 📁 **Filtros** | Incluye/excluye extensiones y directorios |

---

## 💻 Ejemplos de Búsqueda

### 1. Buscar TODOs en el código
```
Patrón: TODO|FIXME
✅ Regex activado
✅ Recursivo activado
```

### 2. Encontrar funciones peligrosas
```
Patrón: eval\(|system\(|exec\(
✅ Regex activado
📁 Incluir: *.php
```

### 3. Buscar una función específica
```
Patrón: function nombreFuncion
✅ Case-insensitive (default)
✅ Recursivo activado
```

### 4. Buscar en archivos específicos
```
Patrón: tu_búsqueda
📁 Incluir: *.php, *.js
📁 Excluir: *.min.js, vendor/*
```

---

## 🔧 Configuración Programática

```php
$engine = new TextSearchEngine(__DIR__);

// Opciones avanzadas
$engine
    ->setMaxFileSize(10485760)     // 10MB máximo por archivo
    ->setMaxResults(5000)          // Máximo resultados
    ->setIncludeExtensions(['*.php', '*.js'])
    ->setExcludeExtensions(['*.min.js', 'vendor/*', '*.log']);

// Búsqueda
$results = $engine->search(
    'pattern',
    recursive: true,      // Buscar en subdirectorios
    matchCase: false,     // Case-insensitive
    useRegex: true        // Usar expresiones regulares
);
```

---

## 🔐 Seguridad

GrepSearch está diseñado con seguridad por defecto:

- ✅ Sin `exec()`, `system()` - PHP puro
- ✅ Validación de directorios (previene directory traversal)
- ✅ Output escapado (previene XSS)
- ✅ Validación de regex (previene inyección)
- ✅ Ignora symlinks (previene loops infinitos)

### Buenas prácticas:

```php
// ✅ Seguro - Directorio específico
new TextSearchEngine('/var/www/mi-app/includes');

// ❌ Riesgoso - Directorio raíz
new TextSearchEngine('/');
```

---

## 📊 Comparación: v1.0 vs v2.0

| Aspecto | v1.0 (2005) | v2.0 (2025) |
|---------|-------------|-------------|
| Dependencias | `grep` del sistema | ✅ Ninguna |
| Plataforma | Solo Linux | ✅ Windows, Linux, macOS |
| Seguridad | ❌ Vulnerable | ✅ Hardened |
| Interfaz | Básica | ✅ Moderna dark mode |
| Arquitectura | Procedural | ✅ OOP PHP 7.4+ |

---

## 🛠️ Requisitos

- PHP 7.4 o superior
- Un navegador web moderno

**Sin extensiones adicionales requeridas.**

---

## 📁 Estructura del Proyecto

```
grephp/
├── grep.php                      # Archivo principal (ÚNICO NECESARIO)
├── README.md                     # Este archivo
├── GREP_README.md                # Guía completa de uso
├── GREP_CONFIGURATIONS.php       # Configuraciones predefinidas (opcional)
├── GREP_EXAMPLES.php             # Ejemplos de código (opcional)
└── *.md                          # Documentación adicional
```

---

## 🤝 Contribuir

1. Fork el repositorio
2. Crea una rama (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Agrega nueva funcionalidad'`)
4. Push (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

---

## 📄 Licencia

Distribuido bajo [GNU LGPL v3](LICENSE)

**Créditos:**
- Original (2005): Alejandro Vásquez
- Modernización (2025): Mejoras de seguridad y funcionalidad

---

## 🆘 Soporte

### Problemas comunes:

| Problema | Solución |
|----------|----------|
| No encuentra resultados | Activa "Recursivo" o verifica los filtros |
| Búsqueda lenta | Usa filtros más específicos, excluye `vendor/*`, `node_modules/*` |
| Regex no funciona | Valida tu patrón en [regex101.com](https://regex101.com) |
| Error de permisos | Verifica que el directorio sea legible por PHP |

### Más ayuda:

- 📖 Lee [`GREP_README.md`](GREP_README.md) para guía completa
- 📝 Revisa [`GREP_EXAMPLES.php`](GREP_EXAMPLES.php) para ejemplos de código

---

## 🔗 Enlaces

- [Repositorio GitHub](https://github.com/clonicdev/grephp)
- [Regex101 - Testeador de Regex](https://regex101.com)
- [Documentación PHP - PCRE](https://www.php.net/manual/es/book.pcre.php)

---

**¡Feliz búsqueda! 🔍**
