<?php
if (!function_exists('clear_input')) {
    function clear_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}
?>
