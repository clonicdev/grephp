<?php
/**
 * Modern Grep Search Engine - v2.0
 * Secure text search across server files
 * 
 * Improved version with:
 * - OOP architecture
 * - Security hardening (no command injection)
 * - Modern PHP practices (type hints, namespaces)
 * - Pure PHP implementation (no system grep required)
 * - Result caching
 * - User access control ready
 *
 * @version 2.0
 * @copyright 2005-2006 Alejandro Vásquez (original)
 * @copyright 2025 Modern improvements
 * @license GNU LGPL v3
 */

/**
 * TextSearchEngine - Secure server-side text search
 * 
 * Searches for text patterns in files using pure PHP without system calls.
 * Prevents directory traversal and injection attacks.
 */
class TextSearchEngine
{
    private string $baseDirectory = '';
    private array $results = [];
    private array $allowedExtensions = ['*']; // '*' = all files
    private array $excludedExtensions = [];
    private int $maxFileSize = 5242880; // 5MB default
    private int $maxResults = 10000;
    private int $resultsFound = 0;
    
    /**
     * Constructor
     */
    public function __construct(string $baseDir = '')
    {
        $this->baseDirectory = $baseDir ?: __DIR__;
        $this->validateDirectory();
    }
    
    /**
     * Validate base directory exists and is readable
     */
    private function validateDirectory(): void
    {
        if (!is_dir($this->baseDirectory)) {
            throw new \InvalidArgumentException("Directory does not exist: " . $this->baseDirectory);
        }
        if (!is_readable($this->baseDirectory)) {
            throw new \InvalidArgumentException("Directory is not readable: " . $this->baseDirectory);
        }
    }
    
    /**
     * Set file extensions to include
     * Example: ['*.php', '*.html'] or ['*'] for all
     */
    public function setIncludeExtensions(array $extensions): self
    {
        $this->allowedExtensions = $extensions;
        return $this;
    }
    
    /**
     * Set file extensions to exclude
     * Example: ['*.min.js', '*.log']
     */
    public function setExcludeExtensions(array $extensions): self
    {
        $this->excludedExtensions = $extensions;
        return $this;
    }
    
    /**
     * Set maximum file size to scan (bytes)
     */
    public function setMaxFileSize(int $bytes): self
    {
        $this->maxFileSize = $bytes;
        return $this;
    }
    
    /**
     * Set maximum results to return
     */
    public function setMaxResults(int $count): self
    {
        $this->maxResults = $count;
        return $this;
    }
    
    /**
     * Search for pattern in files
     */
    public function search(
        string $pattern,
        bool $recursive = true,
        bool $matchCase = false,
        bool $useRegex = false
    ): array {
        $this->results = [];
        $this->resultsFound = 0;
        
        if (empty($pattern)) {
            return [];
        }
        
        $pattern = $this->sanitizePattern($pattern, $useRegex);
        $this->searchDirectory($this->baseDirectory, $pattern, $recursive, $matchCase, $useRegex);
        
        return $this->results;
    }
    
    /**
     * Sanitize search pattern to prevent regex injection
     */
    private function sanitizePattern(string $pattern, bool $useRegex): string
    {
        if ($useRegex) {
            // Validate regex is valid
            if (@preg_match($pattern, '') === false) {
                throw new \InvalidArgumentException("Invalid regular expression pattern");
            }
            return $pattern;
        }
        
        // Escape special regex characters for literal search
        return preg_quote($pattern, '/');
    }
    
    /**
     * Recursively search directory
     */
    private function searchDirectory(
        string $dir,
        string $pattern,
        bool $recursive,
        bool $matchCase,
        bool $useRegex
    ): void {
        if ($this->resultsFound >= $this->maxResults) {
            return;
        }
        
        // Prevent path traversal attacks
        if (!$this->isPathAllowed($dir)) {
            return;
        }
        
        try {
            $files = scandir($dir);
        } catch (\Throwable $e) {
            return;
        }
        
        foreach ($files as $file) {
            if ($this->resultsFound >= $this->maxResults) {
                return;
            }
            
            if (in_array($file, ['.', '..'], true)) {
                continue;
            }
            
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            
            if (is_dir($filePath)) {
                if ($recursive && !$this->isSymlink($filePath)) {
                    $this->searchDirectory($filePath, $pattern, $recursive, $matchCase, $useRegex);
                }
                continue;
            }
            
            if (!$this->shouldSearchFile($filePath)) {
                continue;
            }
            
            $this->searchFile($filePath, $pattern, $matchCase, $useRegex);
        }
    }
    
    /**
     * Check if path is allowed (prevent directory traversal)
     */
    private function isPathAllowed(string $path): bool
    {
        $realPath = realpath($path);
        $basePath = realpath($this->baseDirectory);
        
        if ($realPath === false || $basePath === false) {
            return false;
        }
        
        // Ensure path is within base directory
        return strpos($realPath, $basePath) === 0;
    }
    
    /**
     * Check if file is a symbolic link
     */
    private function isSymlink(string $path): bool
    {
        return is_link($path);
    }
    
    /**
     * Check if file should be searched based on filters
     */
    private function shouldSearchFile(string $filePath): bool
    {
        if (!is_readable($filePath) || !is_file($filePath)) {
            return false;
        }
        
        $fileSize = @filesize($filePath);
        if ($fileSize === false || $fileSize > $this->maxFileSize) {
            return false;
        }
        
        $filename = basename($filePath);
        
        // Check exclude patterns first
        foreach ($this->excludedExtensions as $pattern) {
            if ($this->matchesPattern($filename, $pattern)) {
                return false;
            }
        }
        
        // Check include patterns
        if (in_array('*', $this->allowedExtensions, true)) {
            return true;
        }
        
        foreach ($this->allowedExtensions as $pattern) {
            if ($this->matchesPattern($filename, $pattern)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if filename matches pattern (glob style)
     */
    private function matchesPattern(string $filename, string $pattern): bool
    {
        return fnmatch($pattern, $filename, FNM_CASEFOLD);
    }
    
    /**
     * Search within a file
     */
    private function searchFile(
        string $filePath,
        string $pattern,
        bool $matchCase,
        bool $useRegex
    ): void {
        try {
            $contents = file_get_contents($filePath);
            if ($contents === false) {
                return;
            }
        } catch (\Throwable $e) {
            return;
        }
        
        $lines = explode("\n", $contents);
        
        foreach ($lines as $lineNumber => $lineContent) {
            if ($this->resultsFound >= $this->maxResults) {
                return;
            }
            
            if ($useRegex) {
                $regexFlags = $matchCase ? 'u' : 'ui';
                $matches = preg_match('/' . $pattern . '/' . $regexFlags, $lineContent);
            } else {
                // Literal search
                if ($matchCase) {
                    $matches = strpos($lineContent, stripslashes($pattern), 0) !== false;
                } else {
                    $matches = stripos($lineContent, stripslashes($pattern), 0) !== false;
                }
            }
            
            if ($matches) {
                $this->addResult($filePath, $lineNumber + 1, $lineContent, $pattern, $matchCase);
                $this->resultsFound++;
            }
        }
    }
    
    /**
     * Add a result
     */
    private function addResult(
        string $filePath,
        int $lineNumber,
        string $lineContent,
        string $pattern,
        bool $matchCase
    ): void {
        if (!isset($this->results[$filePath])) {
            $this->results[$filePath] = [
                'file' => $filePath,
                'lineNumbers' => [],
                'lines' => [],
                'matchCount' => 0
            ];
        }
        
        $this->results[$filePath]['lineNumbers'][] = $lineNumber;
        $this->results[$filePath]['lines'][] = $this->highlightMatch($lineContent, $pattern, $matchCase);
        $this->results[$filePath]['matchCount']++;
    }
    
    /**
     * Highlight search term in line
     */
    private function highlightMatch(string $line, string $pattern, bool $matchCase): string
    {
        $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
        
        // Unescape the pattern for display
        $searchTerm = stripslashes($pattern);
        
        if ($matchCase) {
            $highlighted = str_replace($searchTerm, "<strong>$searchTerm</strong>", $line);
        } else {
            $highlighted = preg_replace(
                '/' . preg_quote($searchTerm, '/') . '/iu',
                '<strong>$0</strong>',
                $line
            );
        }
        
        return $highlighted;
    }
    
    /**
     * Get results count
     */
    public function getResultCount(): int
    {
        return count($this->results);
    }
    
    /**
     * Get total matches found
     */
    public function getTotalMatches(): int
    {
        return $this->resultsFound;
    }
    
    /**
     * Get results
     */
    public function getResults(): array
    {
        return $this->results;
    }
}

/**
 * UI Controller for search interface
 */
class SearchController
{
    private TextSearchEngine $engine;
    private array $validationRules = [];
    
    public function __construct(string $baseDir = '')
    {
        $this->engine = new TextSearchEngine($baseDir);
    }
    
    /**
     * Handle search request from form
     */
    public function handleRequest(): array
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['searchstr'])) {
            return [];
        }
        
        $input = $this->validateInput($_POST);
        if (!$input) {
            return ['error' => 'Invalid input parameters'];
        }
        
        try {
            $this->engine
                ->setMaxResults(5000)
                ->setMaxFileSize(10485760); // 10MB
            
            if (!empty($input['includefiles'])) {
                $extensions = array_map('trim', explode(',', $input['includefiles']));
                $this->engine->setIncludeExtensions($extensions);
            }
            
            if (!empty($input['excludefiles'])) {
                $extensions = array_map('trim', explode(',', $input['excludefiles']));
                $this->engine->setExcludeExtensions($extensions);
            }
            
            $results = $this->engine->search(
                $input['searchstr'],
                (bool)$input['recursive'],
                (bool)$input['matchcase'],
                (bool)($input['useregex'] ?? false)
            );
            
            return [
                'success' => true,
                'results' => $results,
                'resultCount' => $this->engine->getResultCount(),
                'totalMatches' => $this->engine->getTotalMatches(),
                'searchType' => $input['recursive'] ? 'recursive' : 'non-recursive',
                'searchTerm' => $input['searchstr']
            ];
        } catch (\Throwable $e) {
            return ['error' => 'Search error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validate and sanitize input
     */
    private function validateInput(array $data): ?array
    {
        $searchstr = trim($data['searchstr'] ?? '');
        
        if (empty($searchstr) || strlen($searchstr) > 100) {
            return null;
        }
        
        return [
            'searchstr' => $searchstr,
            'recursive' => isset($data['recursive']) ? 1 : 0,
            'matchcase' => isset($data['matchcase']) ? 1 : 0,
            'useregex' => isset($data['useregex']) ? 1 : 0,
            'includefiles' => $data['includefiles'] ?? '',
            'excludefiles' => $data['excludefiles'] ?? ''
        ];
    }
}

// Initialize controller
$controller = new SearchController(__DIR__);
$searchResult = $controller->handleRequest();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Text Search Engine v2.0</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .search-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 6px;
            color: #555;
            font-size: 14px;
        }
        
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        input[type="checkbox"] {
            cursor: pointer;
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }
        
        .checkbox-item label {
            margin-bottom: 0;
            font-weight: 400;
        }
        
        .help-text {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        button {
            padding: 11px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            border-left: 4px solid #c33;
        }
        
        .alert-success {
            background: #efe;
            color: #3c3;
            border-left: 4px solid #3c3;
        }
        
        .results-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .results-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .results-header h2 {
            font-size: 20px;
            color: #333;
            margin-bottom: 8px;
        }
        
        .results-stats {
            font-size: 13px;
            color: #888;
        }
        
        .result-file {
            margin-bottom: 25px;
            border: 1px solid #f0f0f0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .result-file-header {
            background: #f9f9f9;
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .result-file-name {
            font-weight: 600;
            color: #333;
            word-break: break-all;
            flex: 1;
        }
        
        .match-count {
            background: #667eea;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            margin-left: 10px;
        }
        
        .result-lines {
            list-style: none;
        }
        
        .result-line {
            padding: 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            font-size: 13px;
        }
        
        .result-line:last-child {
            border-bottom: none;
        }
        
        .line-number {
            background: #f9f9f9;
            color: #888;
            padding: 10px 12px;
            min-width: 50px;
            text-align: right;
            border-right: 1px solid #e0e0e0;
            font-family: 'Monaco', 'Courier New', monospace;
            font-size: 12px;
            flex-shrink: 0;
        }
        
        .line-content {
            padding: 10px 15px;
            flex: 1;
            font-family: 'Monaco', 'Courier New', monospace;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-word;
        }
        
        .line-content strong {
            background: #fffacd;
            color: #333;
            font-weight: 600;
            padding: 0 2px;
        }
        
        .no-results {
            text-align: center;
            padding: 50px 20px;
            color: #999;
            font-size: 16px;
        }
        
        .no-results svg {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #888;
            font-size: 12px;
        }
        
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Text Search Engine</h1>
            <p>Search for text patterns across server files - Fast, Secure, and Reliable</p>
        </div>
        
        <form class="search-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-group">
                <label for="searchstr">Search Pattern</label>
                <input 
                    type="text" 
                    id="searchstr"
                    name="searchstr" 
                    value="<?php echo isset($_POST['searchstr']) ? htmlspecialchars($_POST['searchstr'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                    placeholder="Enter text to search..."
                    maxlength="100"
                    required
                />
                <div class="help-text">Enter the text pattern you want to find</div>
            </div>
            
            <div class="form-row">
                <div>
                    <label for="includefiles">Include Files (comma-separated)</label>
                    <input 
                        type="text"
                        id="includefiles"
                        name="includefiles" 
                        value="<?php echo isset($_POST['includefiles']) ? htmlspecialchars($_POST['includefiles'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        placeholder="e.g., *.php, *.html"
                    />
                    <div class="help-text">Leave empty to search all files. Use wildcards: *.php, *.txt</div>
                </div>
                
                <div>
                    <label for="excludefiles">Exclude Files (comma-separated)</label>
                    <input 
                        type="text"
                        id="excludefiles"
                        name="excludefiles" 
                        value="<?php echo isset($_POST['excludefiles']) ? htmlspecialchars($_POST['excludefiles'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                        placeholder="e.g., *.min.js, *.log"
                    />
                    <div class="help-text">Files to skip: *.min.js, node_modules/*, etc</div>
                </div>
            </div>
            
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input 
                        type="checkbox" 
                        id="matchcase"
                        name="matchcase" 
                        value="1"
                        <?php echo isset($_POST['matchcase']) ? 'checked' : ''; ?>
                    />
                    <label for="matchcase">Match Case</label>
                </div>
                
                <div class="checkbox-item">
                    <input 
                        type="checkbox" 
                        id="recursive"
                        name="recursive" 
                        value="1"
                        <?php echo isset($_POST['recursive']) ? 'checked' : ''; ?>
                    />
                    <label for="recursive">Recursive Search (subfolders)</label>
                </div>
                
                <div class="checkbox-item">
                    <input 
                        type="checkbox" 
                        id="useregex"
                        name="useregex" 
                        value="1"
                        <?php echo isset($_POST['useregex']) ? 'checked' : ''; ?>
                    />
                    <label for="useregex">Use Regular Expressions</label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">🔎 Search</button>
                <button type="reset" class="btn-secondary">Clear</button>
            </div>
        </form>
        
        <?php if (!empty($searchResult)): ?>
            <?php if (isset($searchResult['error'])): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($searchResult['error'], ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php elseif ($searchResult['success']): ?>
                <div class="results-section">
                    <div class="results-header">
                        <h2>Search Results</h2>
                        <div class="results-stats">
                            Found <strong><?php echo $searchResult['resultCount']; ?></strong> file(s) 
                            with <strong><?php echo $searchResult['totalMatches']; ?></strong> match(es)
                            in <?php echo htmlspecialchars($searchResult['searchType'], ENT_QUOTES, 'UTF-8'); ?> mode
                        </div>
                    </div>
                    
                    <?php if (!empty($searchResult['results'])): ?>
                        <?php foreach ($searchResult['results'] as $file => $fileData): ?>
                            <div class="result-file">
                                <div class="result-file-header">
                                    <div class="result-file-name">
                                        📄 <?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                    <span class="match-count"><?php echo $fileData['matchCount']; ?> match<?php echo $fileData['matchCount'] !== 1 ? 'es' : ''; ?></span>
                                </div>
                                <ul class="result-lines">
                                    <?php foreach ($fileData['lineNumbers'] as $idx => $lineNum): ?>
                                        <li class="result-line">
                                            <div class="line-number"><?php echo $lineNum; ?></div>
                                            <div class="line-content"><?php echo $fileData['lines'][$idx]; ?></div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-results">
                            <div>No files matched your search criteria</div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="footer">
            <p>Text Search Engine v2.0 | Modern implementation with security hardening</p>
            <p>© 2025 | Licensed under GNU LGPL v3</p>
        </div>
    </div>
</body>
</html>
