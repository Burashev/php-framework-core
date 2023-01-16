<?php
declare(strict_types=1);

function dd($data) {
    echo '<pre>';
    var_dump($data);
    echo '</pre>';

    die();
}
