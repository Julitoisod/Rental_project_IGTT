<?php

declare(strict_types=1);

// Fallback front controller for hosts that cannot point DocumentRoot to /public.
require __DIR__ . '/public/index.php';
