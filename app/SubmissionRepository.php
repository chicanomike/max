<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class SubmissionRepository
{
    public function __construct(private PDO $database)
    {
    }

    public function create(array $data, string $submissionKey): array
    {
        $statement = $this->database->prepare(
            'INSERT INTO solicitudes (nombre, telefono, correo, servicio, descripcion, fecha_registro, clave_envio)
             VALUES (:nombre, :telefono, :correo, :servicio, :descripcion, :fecha_registro, :clave_envio)'
        );

        try {
            $statement->execute([
                'nombre' => $data['nombre'],
                'telefono' => $data['telefono'],
                'correo' => $data['correo'],
                'servicio' => $data['servicio'],
                'descripcion' => $data['descripcion'],
                'fecha_registro' => date('Y-m-d H:i:s'),
                'clave_envio' => $submissionKey,
            ]);

            return ['request' => $this->find((int) $this->database->lastInsertId()), 'created' => true];
        } catch (PDOException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $existing = $this->findBySubmissionKey($submissionKey);
            if ($existing === null) {
                throw $exception;
            }

            return ['request' => $existing, 'created' => false];
        }
    }

    public function find(int $id): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM solicitudes WHERE id = :id');
        $statement->execute(['id' => $id]);
        $request = $statement->fetch();

        return $request === false ? null : $request;
    }

    public function markWhatsAppResult(int $id, bool $sent, ?string $error): void
    {
        $statement = $this->database->prepare(
            'UPDATE solicitudes
             SET whatsapp_estado = :estado,
                 whatsapp_error = :error,
                 whatsapp_intentos = whatsapp_intentos + 1,
                 whatsapp_actualizado_en = :actualizado
             WHERE id = :id'
        );
        $statement->execute([
            'estado' => $sent ? 'enviado' : 'error',
            'error' => $error,
            'actualizado' => date('Y-m-d H:i:s'),
            'id' => $id,
        ]);
    }

    public function search(array $filters): array
    {
        $conditions = [];
        $parameters = [];

        if ($filters['q'] !== '') {
            $conditions[] = '(nombre LIKE :nombre OR telefono LIKE :telefono)';
            $parameters['nombre'] = '%' . $filters['q'] . '%';
            $parameters['telefono'] = '%' . $filters['q'] . '%';
        }
        if ($filters['servicio'] !== '') {
            $conditions[] = 'servicio = :servicio';
            $parameters['servicio'] = $filters['servicio'];
        }
        if ($filters['desde'] !== '') {
            $conditions[] = 'fecha_registro >= :desde';
            $parameters['desde'] = $filters['desde'] . ' 00:00:00';
        }
        if ($filters['hasta'] !== '') {
            $conditions[] = 'fecha_registro < :hasta';
            $parameters['hasta'] = date('Y-m-d H:i:s', strtotime($filters['hasta'] . ' +1 day'));
        }

        $where = $conditions === [] ? '' : ' WHERE ' . implode(' AND ', $conditions);
        $statement = $this->database->prepare(
            'SELECT * FROM solicitudes' . $where . ' ORDER BY fecha_registro DESC, id DESC LIMIT 200'
        );
        $statement->execute($parameters);

        return $statement->fetchAll();
    }

    public function services(): array
    {
        return $this->database->query('SELECT DISTINCT servicio FROM solicitudes ORDER BY servicio')->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $statement = $this->database->prepare('UPDATE solicitudes SET estado = :estado WHERE id = :id');
        $statement->execute(['estado' => $status, 'id' => $id]);

        return $statement->rowCount() > 0 || $this->find($id) !== null;
    }

    public function delete(int $id): bool
    {
        $statement = $this->database->prepare('DELETE FROM solicitudes WHERE id = :id');
        $statement->execute(['id' => $id]);

        return $statement->rowCount() > 0;
    }

    private function findBySubmissionKey(string $submissionKey): ?array
    {
        $statement = $this->database->prepare('SELECT * FROM solicitudes WHERE clave_envio = :clave_envio');
        $statement->execute(['clave_envio' => $submissionKey]);
        $request = $statement->fetch();

        return $request === false ? null : $request;
    }
}