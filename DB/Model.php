<?php
declare(strict_types=1);

namespace App\Core\DB;

abstract class Model
{
    public array $attributes = [];
    public array $fillable = [];

    abstract public function getTable(): string;

    public function __get(string $name)
    {
        return $this->attributes[$name];
    }

    public function __set(string $name, $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function loadAttributes(array $data): Model
    {
        foreach ($data as $attribute => $value) {
            $this->attributes[$attribute] = $value;
        }

        return $this;
    }

    public static function query(): Builder
    {
        return (new static)->newQuery();
    }

    public function newQuery(): Builder
    {
        return new Builder($this);
    }
}
