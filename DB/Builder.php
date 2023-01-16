<?php
declare(strict_types=1);

namespace App\Core\DB;

use App\Core\Application;

final class Builder
{
    protected array $wheres = [];
    protected array $orders = [];

    public function __construct(private readonly Model $model)
    {

    }

    public function where(string $column, string $operator, string $value, string $boolean = "AND"): Builder
    {
        $this->wheres[] = compact('column', 'operator', 'value', 'boolean');

        return $this;
    }

    public function orderBy(string $column, string $direction = "ASC"): Builder
    {
        $this->orders[] = compact('column', 'direction');

        return $this;
    }

    public function make($data): Model
    {
        return $this->model->loadAttributes($data);
    }

    public function save(): void
    {
        $queryProtectedAttributes = implode(',', $this->model->fillable);
        $queryProtectedValues = implode(',', array_map(
            fn(string $safeAttribute) => "'{$this->model->attributes[$safeAttribute]}'",
            $this->model->fillable
        ));
        $query = "INSERT INTO {$this->model->getTable()}({$queryProtectedAttributes}) VALUES ({$queryProtectedValues})";

        Application::$app->db->PDO->exec($query);
    }

    public function create(array $data): Model
    {
        $model = $this->make($data);
        $this->save();
        return $model;
    }

    public function get(array $columns = ['*']): array
    {
        $columnsString = implode(',', $columns);
        $query = $this->assembleQuery($columnsString);

        $statement = Application::$app->db->PDO->prepare($query);
        $statement->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $data;
    }

    private function assembleQuery(string $columns): string
    {
        $whereQuery = '';
        $orderQuery = '';

        if (!empty($this->wheres)) {
            $whereQuery = "WHERE";
            foreach ($this->wheres as $index => $where) {
                $whereQuery .= " {$where['column']} {$where['operator']} '{$where['value']}' ";
                if ($index !== count($this->wheres) - 1) $whereQuery .= $where['boolean'];
            }
        }

        if (!empty($this->orders)) {
            $ordersForQuery = implode(
                ',',
                array_map(fn($order) => "{$order['column']} {$order['direction']}", $this->orders)
            );

            $orderQuery = "ORDER BY {$ordersForQuery}";
        }

        $query = "SELECT {$columns} FROM {$this->model->getTable()} {$whereQuery} {$orderQuery}";

        return $query;
    }
}
