<?php

require_once __DIR__ . '/../core/Controller.php';

class ApiController extends Controller {
    public function handleRequest($method, $table, $id = null) {
        if (!$this->validateTable($table)) {
            $this->respond(['error' => "La tabla '$table' no existe"], 404);
            return;
        }

        switch ($method) {
            case 'GET':
                $this->get($table, $id);
                break;
            case 'POST':
                $this->create($table);
                break;
            case 'PUT':
                $this->update($table, $id);
                break;
            case 'DELETE':
                $this->delete($table, $id);
                break;
            default:
                $this->respond(['error' => 'Método no soportado'], 405);
        }
    }

    private function get($table, $id) {
        $idColumn = $this->getIdColumn($table);
        if (!$idColumn) {
            $this->respond(['error' => "No se encontró una columna de ID en la tabla '$table'"], 400);
            return;
        }

        $sql = $id ? "SELECT * FROM $table WHERE $idColumn = ?" : "SELECT * FROM $table";
        $stmt = $this->db->prepare($sql);

        if ($id) {
            $stmt->bind_param("i", $id);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $this->respond($data);
    }

    private function create($table) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            $this->respond(['error' => 'Datos no proporcionados'], 400);
            return;
        }

        $columns = $this->getTableColumns($table);
        $fields = array_intersect(array_keys($data), $columns);
        if (empty($fields)) {
            $this->respond(['error' => 'No se proporcionaron campos válidos'], 400);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $values = array_map(fn($field) => $data[$field], $fields);
        $types = str_repeat('s', count($values));

        $sql = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->respond(['error' => "Error al preparar la consulta para la tabla '$table'"], 500);
            return;
        }
    
        $params = array_merge([$types], $values); // Combina tipos y valores
        call_user_func_array([$stmt, 'bind_param'], $params);
        if ($stmt->execute()) {
            $data['id'] = $this->db->insert_id;
            $this->respond($data, 201);
        } else {
            $this->respond(['error' => "Error al insertar en la tabla '$table'"], 500);
        }
    }

    private function update($table, $id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            $this->respond(['error' => 'Datos no proporcionados'], 400);
            return;
        }
        $idColumn = $this->getIdColumn($table);
        if (!$idColumn) {
            $this->respond(['error' => "No se encontró una columna de ID en la tabla '$table'"], 400);
            return;
        }

        $columns = $this->getTableColumns($table);
        $fields = array_intersect(array_keys($data), $columns);


    if (empty($fields)) {
        $this->respond(['error' => 'No se proporcionaron campos válidos'], 400);
        return;
    }

        $setClauses = implode(',', array_map(fn($field) => "$field = ?", $fields));
        $values = array_map(fn($field) => $data[$field], $fields);
        $types = str_repeat('s', count($values)) . 'i';

        $sql = "UPDATE $table SET $setClauses WHERE $idColumn = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->respond(['error' => "Error al preparar la consulta para la tabla '$table'"], 500);
            return;
        }
    
        $params = array_merge([$types], $values, [$id]); // Combina tipos, valores y el ID
        call_user_func_array([$stmt, 'bind_param'], $params);
    
        if ($stmt->execute()) {
            $this->respond(['mensaje' => 'Registro actualizado']);
        } else {
            $this->respond(['error' => "Error al actualizar en la tabla '$table'"], 500);
        }
    }

    private function delete($table, $id) {
        $idColumn = $this->getIdColumn($table);
        if (!$idColumn) {
            $this->respond(['error' => "No se encontró una columna de ID en la tabla '$table'"], 400);
            return;
        }

        $sql = "DELETE FROM $table WHERE $idColumn = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $this->respond(['mensaje' => 'Registro eliminado']);
        } else {
            $this->respond(['error' => "Error al borrar en la tabla '$table'"], 500);
        }
    }
}
