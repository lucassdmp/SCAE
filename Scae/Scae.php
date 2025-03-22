<?php

namespace Scae;

class Scae
{
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Scae();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action('init', array($this, 'init'));
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function admin_menu()
    {
        add_menu_page(
            'SCAE System',
            'SCAE System',
            'manage_options',
            'scae-system',
            array($this, 'scae_system_page')
        );
    }

    public function scae_system_page()
    {
        echo 'SCAE System Page';
    }
}