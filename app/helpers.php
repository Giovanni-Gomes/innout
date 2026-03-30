<?php

if (! function_exists('renderTitle')) {
    /**
     * Render the shared page title block (see resources/views/components/title.blade.php).
     */
    function renderTitle(string $title, string $subtitle, string $icon = ''): void
    {
        require resource_path('views/components/title.blade.php');
    }
}
