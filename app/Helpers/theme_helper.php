<?php

if (!function_exists('theme')) {
    /**
     * Get theme configuration value.
     */
    function theme(string $key = null)
    {
        static $config = null;
        if ($config === null) {
            $config = include APPPATH . 'Views/_config/theme.php';
        }
        if ($key === null)
            return $config;

        $parts = explode('.', $key);
        $value = $config;
        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return null;
            }
            $value = $value[$part];
        }
        return $value;
    }
}

if (!function_exists('config_menu')) {
    /**
     * Get menu configuration by layout and role.
     */
    function config_menu(string $layout, string $role): array
    {
        $file = ($layout === 'superadmin')
            ? include APPPATH . 'Views/_config/menu_superadmin.php'
            : include APPPATH . 'Views/_config/menu_tenant.php';
        return $file[$role] ?? [];
    }
}
