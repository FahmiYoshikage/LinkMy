<?php
/**
 * Performance optimization helper functions
 * Implements caching, query optimization, and resource management
 */

// Simple in-memory cache for session-based caching
class SimpleCache {
    private static $cache = [];
    
    /**
     * Get cached value or execute callback and cache result
     * @param string $key Cache key
     * @param callable $callback Function to execute if cache miss
     * @param int $ttl Time to live in seconds (default: 5 minutes)
     * @return mixed Cached or fresh data
     */
    public static function remember($key, $callback, $ttl = 300) {
        $cache_key = 'cache_' . md5($key);
        $time_key = 'cache_time_' . md5($key);
        
        // Check if cache exists and is not expired
        if (isset($_SESSION[$cache_key]) && isset($_SESSION[$time_key])) {
            if (time() - $_SESSION[$time_key] < $ttl) {
                return $_SESSION[$cache_key];
            }
        }
        
        // Cache miss or expired - execute callback
        $result = $callback();
        
        // Store in session cache
        $_SESSION[$cache_key] = $result;
        $_SESSION[$time_key] = time();
        
        return $result;
    }
    
    /**
     * Clear all cache or specific key
     * @param string|null $key Specific cache key to clear, or null for all
     */
    public static function clear($key = null) {
        if ($key === null) {
            // Clear all cache entries
            foreach (array_keys($_SESSION) as $session_key) {
                if (strpos($session_key, 'cache_') === 0) {
                    unset($_SESSION[$session_key]);
                }
            }
        } else {
            $cache_key = 'cache_' . md5($key);
            $time_key = 'cache_time_' . md5($key);
            unset($_SESSION[$cache_key], $_SESSION[$time_key]);
        }
    }
}

/**
 * Optimized query executor with prepared statements
 * @param mysqli $conn Database connection
 * @param string $query SQL query
 * @param array $params Parameters array
 * @param string $types Parameter types (e.g., 'si' for string, int)
 * @return array|false Query results or false on error
 */
function execute_query_cached($conn, $query, $params = [], $types = '', $cache_ttl = 300) {
    $cache_key = $query . serialize($params);
    
    return SimpleCache::remember($cache_key, function() use ($conn, $query, $params, $types) {
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            return false;
        }
        
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (!$result) {
            mysqli_stmt_close($stmt);
            return false;
        }
        
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $rows;
    }, $cache_ttl);
}

/**
 * Lazy load images with placeholder
 * @param string $src Image source
 * @param string $alt Alt text
 * @param string $class CSS classes
 * @return string HTML img tag with lazy loading
 */
function lazy_image($src, $alt = '', $class = '') {
    return sprintf(
        '<img src="%s" alt="%s" class="%s" loading="lazy" decoding="async">',
        htmlspecialchars($src),
        htmlspecialchars($alt),
        htmlspecialchars($class)
    );
}

/**
 * Minify inline CSS (basic implementation)
 * @param string $css CSS code
 * @return string Minified CSS
 */
function minify_css($css) {
    // Remove comments
    $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
    // Remove whitespace
    $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    ', '    '], '', $css);
    // Remove spaces around operators
    $css = str_replace([' {', '{ ', ' }', '} ', ': ', ' :', '; ', ' ;'], ['{', '{', '}', '}', ':', ':', ';', ';'], $css);
    return trim($css);
}

/**
 * Generate cache-busting query string based on file modification time
 * @param string $file File path
 * @return string Version query string
 */
function asset_version($file) {
    $filepath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($file, '/');
    if (file_exists($filepath)) {
        return '?v=' . filemtime($filepath);
    }
    return '?v=' . time();
}
