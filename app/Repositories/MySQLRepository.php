<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;

class MySQLRepository
{
    public function createTable(string $nameTable, array $columns): ?array
    {
        try {
            $columnsSQL = implode(', ', array_map(fn($col) => "{$col['name']} {$col['type']}", $columns));
            $sql = "CREATE TABLE IF NOT EXISTS {$nameTable} (id INT AUTO_INCREMENT PRIMARY KEY, {$columnsSQL})";
            
            DB::statement($sql);

            return [
                'table' => $nameTable,
                'columns' => $columns
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    public function addValues(string $nameTable, array $data): ?array
    {
        try {
            $keys = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $values = array_values($data);

            $sql = "INSERT INTO {$nameTable} ({$keys}) VALUES ({$placeholders})";
            DB::insert($sql, $values);

            return [
                'table' => $nameTable,
                'data' => $data
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateValues(string $nameTable, int $idValue, array $data): ?array
    {
        try {
            $updates = implode(', ', array_map(fn($key) => "{$key} = ?", array_keys($data)));
            $values = array_values($data);
            $values[] = $idValue;

            $sql = "UPDATE {$nameTable} SET {$updates} WHERE id = ?";
            DB::update($sql, $values);

            return [
                'table' => $nameTable,
                'updated_data' => $data
            ];
        } catch (Exception $e) {
            return null;
        }
    }

    public function getValues(string $nameTable): ?array
    {
        try {
            return DB::select("SELECT * FROM {$nameTable}");
        } catch (Exception $e) {
            return null;
        }
    }

    public function getByIdValues(string $nameTable, int $idValue): ?array
    {
        try {
            return DB::select("SELECT * FROM {$nameTable} WHERE id = ?", [$idValue]);
        } catch (Exception $e) {
            return null;
        }
    }

    public function deleteValues(string $nameTable, int $idValues): string
    {
        try {
            $deleted = DB::delete("DELETE FROM {$nameTable} WHERE id = ?", [$idValues]);

            if ($deleted === 0) {
                throw new Exception("No se encontró el dato en la tabla {$nameTable} con id = {$idValues}");
            }

            return "Se eliminó el dato en la tabla {$nameTable} con id = {$idValues}";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function deleteTable(string $nameTable): string
    {
        try {
            DB::statement("DROP TABLE IF EXISTS {$nameTable}");

            return "Se eliminó la tabla {$nameTable}";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
