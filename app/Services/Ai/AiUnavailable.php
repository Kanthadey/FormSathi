<?php

namespace App\Services\Ai;

use RuntimeException;

/** The AI could not answer; callers fall back to a non-AI path. */
class AiUnavailable extends RuntimeException {}
