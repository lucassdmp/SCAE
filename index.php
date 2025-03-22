<?php

/**
 * Plugin Name: SCAE System Plugin
 * Plugin URI: https://scae.academy/
 * Description: This plugin is used to manage the SCAE system.
 * Version: 0.0
 * Author: SCAE
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use Scae\Scae;

Scae::getInstance();