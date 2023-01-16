<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    private const SESSION_DATA_KEY = 'sessionData';
    private const SESSION_FLASH_DATA_KEY = 'sessionFlashData';

    public function __construct()
    {
        session_start();

        $_SESSION[self::SESSION_DATA_KEY] ??= [];
        $_SESSION[self::SESSION_FLASH_DATA_KEY] ??= [];
    }

    public function put(string $key, $value): void
    {
        $_SESSION[self::SESSION_DATA_KEY][$key] = $value;
    }

    public function get(string $key, $defaultValue = null): mixed
    {
        if (array_key_exists($key, $_SESSION[self::SESSION_DATA_KEY])) {
            return $_SESSION[self::SESSION_DATA_KEY][$key];
        } elseif (array_key_exists($key, $_SESSION[self::SESSION_FLASH_DATA_KEY])) {
            return $_SESSION[self::SESSION_FLASH_DATA_KEY][$key]['value'];
        } else {
            return $defaultValue;
        }
    }

    public function exists(string $key): bool
    {
        return array_key_exists($key, $_SESSION[self::SESSION_DATA_KEY]) || array_key_exists($key, $_SESSION[self::SESSION_FLASH_DATA_KEY]);
    }

    public function flash(string $key, $value): void
    {
        $_SESSION[self::SESSION_FLASH_DATA_KEY][$key] = ['value' => $value, 'delete' => false];
    }

    private function deleteFlashData(): void
    {
        $_SESSION[self::SESSION_FLASH_DATA_KEY] = array_filter($_SESSION[self::SESSION_FLASH_DATA_KEY], fn ($data) => !$data['delete']);
    }

    private function updateFlashDataStatuses(): void {
        foreach ($_SESSION[self::SESSION_FLASH_DATA_KEY] as $key => &$data) {
            $data['delete'] = true;
        }
    }

    public function __destruct()
    {
        $this->deleteFlashData();
        $this->updateFlashDataStatuses();
    }
}
