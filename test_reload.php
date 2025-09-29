<?php
// Test reload cache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reseteado<br>";
} else {
    echo "OPcache no disponible<br>";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache limpiado<br>";
} else {
    echo "APC cache no disponible<br>";
}

echo "Cache test completed at: " . date('Y-m-d H:i:s');
?>