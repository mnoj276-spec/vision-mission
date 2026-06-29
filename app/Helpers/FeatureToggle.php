<?php

namespace App\Helpers {
    class FeatureToggle
    {
        /**
         * Determine if a given module or sub-feature is enabled.
         *
         * @param string $module
         * @return bool
         */
        public static function isEnabled(string $module): bool
        {
            $activeVersion = (int) config('features.version', 1);
            $modules = config('features.modules', []);

            // If the module isn't mapped, default to enabled to prevent accidental lockouts
            if (!array_key_exists($module, $modules)) {
                return true;
            }

            return $activeVersion >= (int) $modules[$module];
        }
    }
}

namespace {
    if (!function_exists('feature_enabled')) {
        /**
         * Global helper to check if a feature is enabled.
         *
         * @param string $module
         * @return bool
         */
        function feature_enabled(string $module): bool
        {
            return \App\Helpers\FeatureToggle::isEnabled($module);
        }
    }
}
