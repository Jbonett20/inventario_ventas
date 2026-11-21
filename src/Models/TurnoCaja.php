<?php
namespace SIG\Models;

use SIG\Core\Database;
use DomainException;
use InvalidArgumentException;
use LogicException;
use Throwable;

class TurnoCaja
{
    private Database $db;

    private const MAXIMO_MONTO = 999999999999.99;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** @return array<string, mixed>|null */
    public function obtenerAbierto(int $idUsuario): ?array
    {
        return $this->db->fetchOne(
            "SELECT id_turno, id_usuario, fecha, base_inicial, abierto_en
             FROM vb_turnos_caja
             WHERE id_usuario = :id_usuario AND estado = 'ABIERTO'
             ORDER BY id_turno DESC
             LIMIT 1",
            ['id_usuario' => $idUsuario]
        );
    }

    /** @return array<string, mixed>|null */
    public function obtener(int $idTurno): ?array
    {
        return $this->db->fetchOne(
            "SELECT t.id_turno, t.id_usuario, t.fecha, t.base_inicial, t.abierto_en, t.cerrado_en,
                    t.efectivo_esperado, t.efectivo_contado, t.diferencia, t.estado,
                    COALESCE(NULLIF(u.nombre_completo, ''), u.nombre_usuario) AS cajero_nombre
             FROM vb_turnos_caja t
             JOIN vb_usuarios u ON u.id_usuario = t.id_usuario
             WHERE t.id_turno = :id_turno
             LIMIT 1",
            ['id_turno' => $idTurno]
        );
    }

    /**
     * Obtiene y bloquea el turno abierto dentro de la transacción que registra el movimiento.
     */
    public function bloquearTurnoAbierto(int $idUsuario): ?int
    {
        if (!$this->db->getConnection()->inTransaction()) {
            throw new LogicException('El turno solo puede bloquearse dentro de una transacción.');
        }

        $turno = $this->db->fetchOne(
            "SELECT id_turno FROM vb_turnos_caja
             WHERE id_usuario = :id_usuario AND estado = 'ABIERTO'
             ORDER BY id_turno DESC
             LIMIT 1 FOR UPDATE",
            ['id_usuario' => $idUsuario]
        );

        return $turno ? (int)$turno['id_turno'] : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function resumen(int $idTurno): array
    {
        if ($idTurno <= 0) {
            throw new InvalidArgumentException('El turno seleccionado no es válido.');
        }

        $turno = $this->db->fetchOne(
            'SELECT id_turno, base_inicial FROM vb_turnos_caja WHERE id_turno = :id_turno',
            ['id_turno' => $idTurno]
        );
        if ($turno === null) {
            throw new DomainException('No se encontró el turno solicitado.');
        }

        $ventas = $this->totalesPorMetodo($this->db->select(
            "SELECT UPPER(TRIM(pg.metodo)) AS metodo, COALESCE(SUM(pg.monto), 0) AS total
             FROM vb_facturas_pagos pg
             JOIN vb_facturas f ON f.id_factura = pg.id_factura
             WHERE pg.id_turno = :id_turno AND f.estado IN ('ACTIVA', 'ACTIVO')
             GROUP BY UPPER(TRIM(pg.metodo))",
            ['id_turno' => $idTurno]
        ));
        $devoluciones = $this->totalesPorMetodo($this->db->select(
            "SELECT UPPER(TRIM(metodo)) AS metodo, COALESCE(SUM(monto), 0) AS total
             FROM vb_devoluciones_pagos
             WHERE id_turno = :id_turno
             GROUP BY UPPER(TRIM(metodo))",
            ['id_turno' => $idTurno]
        ));
        $egresos = $this->totalesPorMetodo($this->db->select(
            "SELECT metodo, COALESCE(SUM(monto), 0) AS total
             FROM (
                 SELECT UPPER(TRIM(metodo_pago)) AS metodo, valor AS monto
                 FROM vb_egresos
                 WHERE id_turno = :id_egreso
                 UNION ALL
                 SELECT UPPER(TRIM(metodo)) AS metodo, monto
                 FROM vb_ingresos_factura_pagos
                 WHERE id_turno = :id_factura
             ) movimientos_egreso
             GROUP BY metodo",
            ['id_egreso' => $idTurno, 'id_factura' => $idTurno]
        ));
        $cambio = $this->db->fetchOne(
            "SELECT COALESCE(SUM(cambio), 0) AS total
             FROM vb_facturas
             WHERE id_turno = :id_turno AND estado IN ('ACTIVA', 'ACTIVO')",
            ['id_turno' => $idTurno]
        );

        $efectivoVentas = $ventas['EFECTIVO'] ?? 0.0;
        $devolucionEfectivo = $devoluciones['EFECTIVO'] ?? 0.0;
        $egresoEfectivo = $egresos['EFECTIVO'] ?? 0.0;
        // Los pagos de factura ya guardan los importes netos después de descontar el cambio.
        $efectivoEsperado = round(
            (float)$turno['base_inicial'] + $efectivoVentas - $devolucionEfectivo - $egresoEfectivo,
            2
        );

        $metodos = array_unique(array_merge(array_keys($ventas), array_keys($devoluciones), array_keys($egresos)));
        $mediosNoEfectivos = [];
        foreach ($metodos as $metodo) {
            if ($metodo === 'EFECTIVO') {
                continue;
            }

            $ventasMetodo = $ventas[$metodo] ?? 0.0;
            $devolucionesMetodo = $devoluciones[$metodo] ?? 0.0;
            $egresosMetodo = $egresos[$metodo] ?? 0.0;
            $mediosNoEfectivos[] = [
                'metodo'       => $metodo,
                'ventas'       => $ventasMetodo,
                'devoluciones' => $devolucionesMetodo,
                'egresos'      => $egresosMetodo,
                'neto'         => round($ventasMetodo - $devolucionesMetodo - $egresosMetodo, 2),
            ];
        }

        return [
            'id_turno'              => $idTurno,
            'base_inicial'          => (float)$turno['base_inicial'],
            'ventas_por_metodo'     => $ventas,
            'devoluciones_por_metodo' => $devoluciones,
            'egresos_por_metodo'    => $egresos,
            'ventas_efectivo'       => $efectivoVentas,
            'cambio_total'          => (float)($cambio['total'] ?? 0),
            'devolucion_efectivo'   => $devolucionEfectivo,
            'egreso_efectivo'      => $egresoEfectivo,
            'efectivo_esperado'    => $efectivoEsperado,
            'medios_no_efectivos'  => $mediosNoEfectivos,
        ];
    }

    public function abrir(int $idUsuario, float $baseInicial): int
    {
        $this->validarMonto($baseInicial);
        if ($idUsuario <= 0) {
            throw new InvalidArgumentException('No se pudo identificar al cajero.');
        }

        $this->db->beginTransaction();
        try {
            $usuario = $this->db->fetchOne(
                'SELECT id_usuario FROM vb_usuarios WHERE id_usuario = :id FOR UPDATE',
                ['id' => $idUsuario]
            );
            if (!$usuario) {
                throw new DomainException('El cajero no existe.');
            }

            $turnoAbierto = $this->db->fetchOne(
                "SELECT id_turno FROM vb_turnos_caja
                 WHERE id_usuario = :id_usuario AND estado = 'ABIERTO'
                 LIMIT 1 FOR UPDATE",
                ['id_usuario' => $idUsuario]
            );
            if ($turnoAbierto) {
                throw new DomainException('Ya tienes un turno de caja abierto.');
            }

            $idTurno = $this->db->insert(
                "INSERT INTO vb_turnos_caja (id_usuario, fecha, base_inicial)
                 VALUES (:id_usuario, CURDATE(), :base_inicial)",
                ['id_usuario' => $idUsuario, 'base_inicial' => round($baseInicial, 2)]
            );

            $this->db->commit();
            return $idTurno;
        } catch (Throwable $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    /** @return array<string, mixed> */
    public function cerrar(int $idUsuario, int $idTurno, float $efectivoContado): array
    {
        $this->validarMonto($efectivoContado);
        if ($idUsuario <= 0 || $idTurno <= 0) {
            throw new InvalidArgumentException('El turno o el cajero no son válidos.');
        }

        $this->db->beginTransaction();
        try {
            $usuario = $this->db->fetchOne(
                'SELECT id_usuario FROM vb_usuarios WHERE id_usuario = :id FOR UPDATE',
                ['id' => $idUsuario]
            );
            if (!$usuario) {
                throw new DomainException('El cajero no existe.');
            }

            $turno = $this->db->fetchOne(
                "SELECT id_turno FROM vb_turnos_caja
                 WHERE id_turno = :id_turno AND id_usuario = :id_usuario AND estado = 'ABIERTO'
                 FOR UPDATE",
                ['id_turno' => $idTurno, 'id_usuario' => $idUsuario]
            );
            if (!$turno) {
                throw new DomainException('El turno ya está cerrado o no pertenece a este cajero.');
            }

            $resumen = $this->resumen($idTurno);
            $diferencia = round($efectivoContado - (float)$resumen['efectivo_esperado'], 2);
            $actualizados = $this->db->executeAffected(
                "UPDATE vb_turnos_caja
                 SET estado = 'CERRADO', cerrado_en = NOW(), efectivo_esperado = :efectivo_esperado,
                     efectivo_contado = :efectivo_contado, diferencia = :diferencia
                 WHERE id_turno = :id_turno AND estado = 'ABIERTO'",
                [
                    'efectivo_esperado' => $resumen['efectivo_esperado'],
                    'efectivo_contado'  => round($efectivoContado, 2),
                    'diferencia'        => $diferencia,
                    'id_turno'          => $idTurno,
                ]
            );
            if ($actualizados !== 1) {
                throw new DomainException('No se pudo cerrar el turno. Actualiza la página e inténtalo de nuevo.');
            }

            $turnoCerrado = $this->db->fetchOne(
                "SELECT id_turno, id_usuario, fecha, base_inicial, abierto_en, cerrado_en,
                        efectivo_esperado, efectivo_contado, diferencia, estado
                 FROM vb_turnos_caja
                 WHERE id_turno = :id_turno",
                ['id_turno' => $idTurno]
            );

            if (!$turnoCerrado) {
                throw new DomainException('No se encontró el turno después de cerrarlo.');
            }

            $turnoCerrado['resumen'] = $resumen;
            $this->db->commit();
            return $turnoCerrado;
        } catch (Throwable $e) {
            if ($this->db->getConnection()->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    /**
     * Reporte de turnos de caja agrupados por día, mes o año.
     *
     * @return array{agrupacion: string, inicio: string, fin: string, periodo_inicio: string, periodo_fin: string,
     *               turnos: list<array<string, mixed>>, grupos: list<array<string, mixed>>,
     *               totales: array<string, int|float>}
     */
    public function reporte(string $agrupacion, string $inicio, string $fin): array
    {
        $formatos = ['dia' => 10, 'mes' => 7, 'anio' => 4];
        if (!isset($formatos[$agrupacion])) {
            throw new InvalidArgumentException('La agrupación del reporte no es válida.');
        }
        foreach ([$inicio, $fin] as $fecha) {
            if (!$this->fechaValida($fecha)) {
                throw new InvalidArgumentException('El rango de fechas del reporte no es válido.');
            }
        }
        if ($inicio > $fin) {
            throw new InvalidArgumentException('La fecha inicial no puede ser posterior a la fecha final.');
        }

        $turnos = $this->db->select(
            "SELECT t.id_turno, t.id_usuario, t.fecha, t.estado, t.abierto_en, t.cerrado_en,
                    t.base_inicial, t.efectivo_esperado, t.efectivo_contado, t.diferencia,
                    COALESCE(NULLIF(u.nombre_completo, ''), u.nombre_usuario) AS cajero_nombre,
                    COALESCE(v.cantidad, 0) AS ventas_cantidad,
                    COALESCE(v.total, 0) AS ventas_total,
                    COALESCE(v.efectivo, 0) AS ventas_efectivo,
                    COALESCE(v.cambio, 0) AS cambio_total
             FROM vb_turnos_caja t
             JOIN vb_usuarios u ON u.id_usuario = t.id_usuario
             LEFT JOIN (
                 SELECT f.id_turno, COUNT(*) AS cantidad, SUM(f.total) AS total,
                        SUM(CASE WHEN f.tipo_pago = 'EFECTIVO' THEN f.total ELSE 0 END) AS efectivo,
                        SUM(f.cambio) AS cambio
                 FROM vb_facturas f
                 WHERE f.id_turno IS NOT NULL AND f.estado IN ('ACTIVA', 'ACTIVO')
                 GROUP BY f.id_turno
             ) v ON v.id_turno = t.id_turno
             WHERE t.fecha BETWEEN :inicio AND :fin
             ORDER BY t.abierto_en DESC, t.id_turno DESC",
            ['inicio' => $inicio, 'fin' => $fin]
        );

        $devoluciones = $this->totalesPorTurno(
            "SELECT id_turno, SUM(monto) AS total
             FROM vb_devoluciones_pagos
             WHERE id_turno IS NOT NULL
             GROUP BY id_turno"
        );
        $egresos = $this->totalesPorTurno(
            "SELECT id_turno, SUM(monto) AS total
             FROM (
                 SELECT id_turno, valor AS monto FROM vb_egresos WHERE id_turno IS NOT NULL
                 UNION ALL
                 SELECT id_turno, monto FROM vb_ingresos_factura_pagos WHERE id_turno IS NOT NULL
             ) movimientos
             GROUP BY id_turno"
        );

        $formato = $formatos[$agrupacion];
        $grupos = [];
        $lista = [];
        foreach ($turnos as $turno) {
            $idTurno = (int)$turno['id_turno'];
            $cerrado = $turno['estado'] === 'CERRADO';
            $fila = [
                'id_turno'          => $idTurno,
                'fecha'             => (string)$turno['fecha'],
                'cajero_nombre'     => (string)$turno['cajero_nombre'],
                'estado'            => (string)$turno['estado'],
                'abierto_en'        => $turno['abierto_en'],
                'cerrado_en'        => $turno['cerrado_en'],
                'base_inicial'      => (float)$turno['base_inicial'],
                'ventas_cantidad'   => (int)$turno['ventas_cantidad'],
                'ventas_total'      => round((float)$turno['ventas_total'], 2),
                'ventas_efectivo'   => round((float)$turno['ventas_efectivo'], 2),
                'cambio_total'      => round((float)$turno['cambio_total'], 2),
                'devoluciones_total' => $devoluciones[$idTurno] ?? 0.0,
                'egresos_total'     => $egresos[$idTurno] ?? 0.0,
                'efectivo_esperado' => $cerrado ? (float)$turno['efectivo_esperado'] : null,
                'efectivo_contado'  => $cerrado ? (float)$turno['efectivo_contado'] : null,
                'diferencia'        => $cerrado ? (float)$turno['diferencia'] : null,
            ];
            $lista[] = $fila;

            $periodo = substr($fila['fecha'], 0, $formato);
            if (!isset($grupos[$periodo])) {
                $grupos[$periodo] = [
                    'periodo'           => $periodo,
                    'turnos'            => 0,
                    'turnos_abiertos'   => 0,
                    'turnos_cerrados'   => 0,
                    'ventas_cantidad'   => 0,
                    'ventas_total'      => 0.0,
                    'devoluciones_total' => 0.0,
                    'egresos_total'     => 0.0,
                    'efectivo_contado'  => 0.0,
                    'diferencia'        => 0.0,
                ];
            }

            $grupo = &$grupos[$periodo];
            $grupo['turnos']++;
            $grupo[$cerrado ? 'turnos_cerrados' : 'turnos_abiertos']++;
            $grupo['ventas_cantidad'] += $fila['ventas_cantidad'];
            $grupo['ventas_total'] += $fila['ventas_total'];
            $grupo['devoluciones_total'] += $fila['devoluciones_total'];
            $grupo['egresos_total'] += $fila['egresos_total'];
            if ($cerrado) {
                $grupo['efectivo_contado'] += $fila['efectivo_contado'];
                $grupo['diferencia'] += $fila['diferencia'];
            }
            unset($grupo);
        }

        $grupos = array_values($grupos);
        foreach ($grupos as &$grupo) {
            foreach (['ventas_total', 'devoluciones_total', 'egresos_total', 'efectivo_contado', 'diferencia'] as $campo) {
                $grupo[$campo] = round($grupo[$campo], 2);
            }
        }
        unset($grupo);

        $totales = [
            'turnos'            => count($lista),
            'turnos_abiertos'   => 0,
            'turnos_cerrados'   => 0,
            'ventas_cantidad'   => 0,
            'ventas_total'      => 0.0,
            'devoluciones_total' => 0.0,
            'egresos_total'     => 0.0,
            'efectivo_contado'  => 0.0,
            'diferencia'        => 0.0,
        ];
        foreach ($lista as $fila) {
            $totales[$fila['estado'] === 'CERRADO' ? 'turnos_cerrados' : 'turnos_abiertos']++;
            $totales['ventas_cantidad'] += $fila['ventas_cantidad'];
            $totales['ventas_total'] += $fila['ventas_total'];
            $totales['devoluciones_total'] += $fila['devoluciones_total'];
            $totales['egresos_total'] += $fila['egresos_total'];
            if ($fila['estado'] === 'CERRADO') {
                $totales['efectivo_contado'] += $fila['efectivo_contado'];
                $totales['diferencia'] += $fila['diferencia'];
            }
        }
        foreach (['ventas_total', 'devoluciones_total', 'egresos_total', 'efectivo_contado', 'diferencia'] as $campo) {
            $totales[$campo] = round($totales[$campo], 2);
        }

        return [
            'agrupacion'      => $agrupacion,
            'inicio'          => $inicio,
            'fin'             => $fin,
            'periodo_inicio'  => $grupos ? $grupos[0]['periodo'] : '',
            'periodo_fin'     => $grupos ? $grupos[count($grupos) - 1]['periodo'] : '',
            'turnos'          => $lista,
            'grupos'          => $grupos,
            'totales'         => $totales,
        ];
    }

    /**
     * @return array<int, float>
     */
    private function totalesPorTurno(string $sql): array
    {
        $totales = [];
        foreach ($this->db->select($sql) as $fila) {
            $totales[(int)$fila['id_turno']] = round((float)$fila['total'], 2);
        }

        return $totales;
    }

    private function fechaValida(string $fecha): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $fecha)) {
            return false;
        }

        $partes = explode('-', $fecha);
        return checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0]);
    }

    private function validarMonto(float $monto): void
    {
        if (!is_finite($monto) || $monto < 0 || $monto > self::MAXIMO_MONTO) {
            throw new InvalidArgumentException('El monto debe ser un número no negativo y válido.');
        }
    }

    /**
     * @param list<array<string, mixed>> $filas
     * @return array<string, float>
     */
    private function totalesPorMetodo(array $filas): array
    {
        $totales = [];
        foreach ($filas as $fila) {
            $metodo = strtoupper(trim((string)($fila['metodo'] ?? '')));
            if ($metodo !== '') {
                $totales[$metodo] = round((float)($fila['total'] ?? 0), 2);
            }
        }

        return $totales;
    }
}
