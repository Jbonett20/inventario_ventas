<?php
/**
 * Definición de Roles y Permisos del Sistema
 * 
 * tipo: 0=Administrador, 1=Cajero, 2=Inventario, 3=Superadministrador
 */

return [
    // Definición de roles
    'roles' => [
        0 => [
            'nombre' => 'Administrador',
            'descripcion' => 'Acceso a gestión del sistema excepto FE y creación de usuarios',
        ],
        1 => [
            'nombre' => 'Cajero',
            'descripcion' => 'Facturación, clientes, consulta de inventario',
        ],
        2 => [
            'nombre' => 'Inventario',
            'descripcion' => 'Gestión de productos, inventario, ingresos',
        ],
        3 => [
            'nombre' => 'Superadministrador',
            'descripcion' => 'Acceso total incluyendo FE, rangos y gestión de usuarios',
        ],
    ],

    // Matriz de permisos: recurso => [roles permitidos]
    'permisos' => [
        // Facturación
        'facturacion.crear'           => [0, 1, 3],
        'facturacion.anular'          => [0, 3],
        'facturacion.electronica'     => [0, 3],
        'facturacion.descuento'       => [0, 3],
        'facturacion.ver_ganancia'    => [0, 3],

        // Inventario
        'inventario.ver'              => [0, 1, 2, 3],
        'inventario.ajustar'          => [0, 2, 3],
        'inventario.ingresar'         => [0, 2, 3],

        // Productos
        'productos.crear'             => [0, 2, 3],
        'productos.editar'            => [0, 2, 3],
        'productos.eliminar'          => [0, 3],

        // Clientes
        'clientes.crear'              => [0, 1, 3],
        'clientes.editar'             => [0, 1, 3],
        'clientes.eliminar'           => [0, 3],

        // Proveedores
        'proveedores.gestion'         => [0, 2, 3],

        // Créditos
        'creditos.gestion'            => [0, 1, 3],

        // Plan Separe
        'plan_separe.gestion'         => [0, 1, 3],

        // Egresos
        'egresos.gestion'             => [0, 3],

        // Configuración
        'configuracion.ver'           => [0, 3],
        'configuracion.fe.ver'        => [3],
        'configuracion.fe.editar'     => [3],
        'configuracion.rangos.ver'    => [3],
        'configuracion.rangos.editar' => [3],
        'configuracion.usuarios'      => [0, 3],
        'configuracion.empresa'       => [3],

        // Reportes
        'reportes.ver'                => [0, 3],
        'reportes.exportar'           => [0, 3],

        // Dashboard
        'dashboard.ver'               => [0, 1, 2, 3],

        // Catálogos (categorías, secciones, tipos de IVA)
        'catalogos.gestion'           => [0, 2, 3],

        // Vendedores
        'vendedores.gestion'          => [0, 1, 3],
    ],
];
