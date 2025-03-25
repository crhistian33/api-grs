<?php

return [
    "success" => [
        'create_title' => 'Registro creado',
        'create_message' => 'se creó exitosamente.',
        'update_title' => 'Registro actualizado',
        'update_message' => 'se actualizó exitosamente.',
        'create_many_title' => 'Registros creados',
        'create_many_message' => 'se crearon exitosamente.',
        'update_many_title' => 'Registros actualizados',
        'update_many_message' => 'se actualizaron exitosamente.',
        'delete_title' => 'Registro eliminado',
        'delete_message' => 'se eliminó exitosamente.',
        'deleteall_title' => 'Registros eliminados',
        'deleteall_message' => 'se eliminaron exitosamente.',
        'deleteall_no_message' => 'se eliminaron exitosamente, menos los que tienen asignaciones activas: ',
        'deleteall_worker_no_message' => 'se eliminaron exitosamente, menos los que tienen trabajadores asociados: ',
        'deleteall_company_no_message' => 'se eliminaron exitosamente, menos las que tienen clientes o usuarios relacionados: ',
        'deleteall_unit_no_message' => 'se eliminaron exitosamente, menos los que tienen unidades asociadas: ',
        'deleteall_shift_no_message' => 'se eliminaron exitosamente, menos los que tienen turnos asociados: ',
        'remove_title' => 'Registro removido',
        'remove_message' => 'se removió exitosamente.',
        'removeall_title' => 'Registros removidos',
        'removeall_message' => 'se removieron exitosamente.',
        'removeall_no_message' => 'se removieron exitosamente, menos los que tienen asignaciones activas: ',
        'removeall_worker_no_message' => 'se removieron exitosamente, menos los que tienen trabajadores asociados: ',
        'removeall_company_no_message' => 'se removieron exitosamente, menos las que tienen clientes o usuarios relacionados: ',
        'removeall_unit_no_message' => 'se removieron exitosamente, menos los que tienen unidades asociadas: ',
        'removeall_shift_no_message' => 'se removieron exitosamente, menos los que tienen turnos asociadas: ',
        'restore_title' => 'Registro restaurado',
        'restore_message' => 'se restauró exitosamente.',
        'restoreall_title' => 'Registros restaurados',
        'restoreall_message' => 'se restauraron exitosamente.',
        'reassign_title' => 'Reasignación exitosa',
        'reassign_message' => 'Se reasignó el trabajador exitosamente.',
    ],
    'error' => [
        'create' => 'Error al crear el registro.',
        'update' => 'Error al actualizar el registro.',
        'delete' => 'Error al eliminar el registro.',
    ],
    'verified' => [
        'exist_title' => 'Unidad turno asignada',
        'exist_message' => 'La unidad turno seleccionada ya tiene una asignación. ¿Desea generar una nueva asignación a dicha unidad turno?'
    ],
    'validate' => [
        "worker_assign_delete" => "No se puede eliminar porque está \nasignado a una unidad de trabajo",
        "worker_assign_remove" => "No se puede remover porque está \nasignado a una unidad de trabajo",
        "worker_assign_no_all" => "No se pueden eliminar porque están asignados \na unidades de trabajo.",

        "type_worker_delete" => "No se puede eliminar porque hay \ntrabajadores registrados con este tipo",
        "type_worker_remove" => "No se puede remover porque hay \ntrabajadores registrados con este tipo",
        "type_worker_no_all" => "No se pueden eliminar porque hay \ntrabajadores registrados de estos tipos",

        "center_unit_delete" => "No se puede eliminar porque hay \nunidades registradas con este centro de costo",
        "center_unit_remove" => "No se puede remover porque hay \nunidades registradas con este centro de costo",
        "center_unit_no_all" => "No se pueden eliminar porque hay \nunidades registradas con estos centros de costo",

        "company_customer_delete" => "No se puede eliminar porque hay clientes \nregistrados que pertenecen a esta empresa",
        "company_customer_remove" => "No se puede remover porque hay clientes \nregistrados que pertenecen a esta empresa",
        "company_user_delete" => "No se puede eliminar porque hay \nusuarios asignados a esta empresa",
        "company_user_remove" => "No se puede remover porque hay \nusuarios asignados a esta empresa",
        "company_no_all" => "No se pueden eliminar las empresas porque hay \nclientes o usuarios relacionadas a ellas",

        "customer_unit_delete" => "No se puede eliminar porque hay \nunidades registradas que pertenecen a este cliente",
        "customer_unit_remove" => "No se puede remover porque hay \nunidades registradas que pertenecen a este cliente",
        "customer_unit_no_all" => "No se pueden eliminar porque hay \nunidades registradas que pertenecen a estos clientes",

        "unit_assign_delete" => "No se puede eliminar porque hay \nasignaciones asociados a esta unidad",
        "unit_assign_remove" => "No se puede remover porque hay \nasignaciones asociados a esta unidad",
        "unit_assign_no_all" => "No se pueden eliminar porque hay \nasignaciones asociados a estas unidades",

        "shift_unit_delete" => "No se puede eliminar porque hay \nunidades asociadas a este turno",
        "shift_unit_remove" => "No se puede remover porque hay \nunidades asociadas a este turno",
        "shift_unit_no_all" => "No se pueden eliminar porque hay \nunidades asociadas a estos turnos",
    ]
];
