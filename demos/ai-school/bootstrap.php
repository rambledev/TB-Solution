<?php

declare(strict_types=1);

/**
 * AI School Intelligence — bootstrap.
 * Reuses the TB-Solution core helpers (e(), tb_log(), tb_logo_mark()) so the
 * demo keeps the same brand and logging conventions as the landing page.
 */
require_once dirname(__DIR__, 2) . '/includes/functions.php';

define('AIS_ROOT', __DIR__);

require_once AIS_ROOT . '/lib/helpers.php';
require_once AIS_ROOT . '/lib/SchoolData.php';
require_once AIS_ROOT . '/lib/SchoolAnalytics.php';
require_once AIS_ROOT . '/lib/Ai/AiProvider.php';
require_once AIS_ROOT . '/lib/Ai/MockAiProvider.php';
require_once AIS_ROOT . '/lib/Ai/OpenAiProvider.php';
require_once AIS_ROOT . '/lib/Ai/AiAssistant.php';
