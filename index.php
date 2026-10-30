<?php

declare(strict_types=1);

/**
 * TB-Solution — Landing Page entry point.
 */
require __DIR__ . '/includes/functions.php';

tb_log('Page', 'index', 'START render', ['uri' => $_SERVER['REQUEST_URI'] ?? '']);

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>
<main id="main">
<?php
foreach (['hero', 'features', 'demo-systems', 'about', 'process', 'contact'] as $section) {
    tb_section($section);
}
?>
</main>
<?php
require __DIR__ . '/includes/footer.php';

tb_log('Page', 'index', 'END render');
