<?php
/**
 * Configuración del Sistema Genérico de Formularios
 * Define todos los tipos de formularios y sus estructuras
 */

return [
    'general' => [
        'nombre' => 'Consulta General',
        'icono' => 'fas fa-user-md',
        'color' => 'primary',
        'tablas' => [
            'principal' => 'consultas'
        ],
        'campos_principales' => [
            'txtmotivo' => ['tipo' => 'textarea', 'label' => 'Motivo de Consulta', 'required' => false],
            'motivoscomunes' => ['tipo' => 'select', 'label' => 'Motivos Comunes', 'required' => false],
            'visionod' => ['tipo' => 'text', 'label' => 'Visión OD', 'required' => false],
            'visionoi' => ['tipo' => 'text', 'label' => 'Visión OI', 'required' => false],
            'tensionod' => ['tipo' => 'text', 'label' => 'Tensión OD', 'required' => false],
            'tensionoi' => ['tipo' => 'text', 'label' => 'Tensión OI', 'required' => false],
            'consulta_textarea' => ['tipo' => 'textarea', 'label' => 'Consulta', 'required' => false],
            'receta_textarea' => ['tipo' => 'textarea', 'label' => 'Receta', 'required' => false],
            'txtnota' => ['tipo' => 'textarea', 'label' => 'Notas', 'required' => false],
            'proximaconsulta' => ['tipo' => 'date', 'label' => 'Próxima Consulta', 'required' => false],
            'whatsapptxt' => ['tipo' => 'text', 'label' => 'WhatsApp', 'required' => false],
            'email' => ['tipo' => 'email', 'label' => 'Email', 'required' => false]
        ]
    ],
    
    'anteojos' => [
        'nombre' => 'Consulta de Anteojos',
        'icono' => 'fas fa-glasses',
        'color' => 'success',
        'tablas' => [
            'principal' => 'consultas',
            'detalle' => [
                'consulta_anteojos' => [
                    'foreign_key' => 'id_consulta',
                    'campos' => [
                        'esfera_od' => ['tipo' => 'text', 'label' => 'Esfera OD', 'grupo' => 'ojo_derecho'],
                        'cilindro_od' => ['tipo' => 'text', 'label' => 'Cilindro OD', 'grupo' => 'ojo_derecho'],
                        'eje_od' => ['tipo' => 'text', 'label' => 'Eje OD', 'grupo' => 'ojo_derecho'],
                        'dnp_od' => ['tipo' => 'text', 'label' => 'DNP OD', 'grupo' => 'ojo_derecho'],
                        'add_od' => ['tipo' => 'text', 'label' => 'ADD OD', 'grupo' => 'ojo_derecho'],
                        'altura_od' => ['tipo' => 'text', 'label' => 'Altura OD', 'grupo' => 'ojo_derecho'],
                        'nota_od' => ['tipo' => 'textarea', 'label' => 'Notas OD', 'grupo' => 'ojo_derecho'],
                        
                        'esfera_oi' => ['tipo' => 'text', 'label' => 'Esfera OI', 'grupo' => 'ojo_izquierdo'],
                        'cilindro_oi' => ['tipo' => 'text', 'label' => 'Cilindro OI', 'grupo' => 'ojo_izquierdo'],
                        'eje_oi' => ['tipo' => 'text', 'label' => 'Eje OI', 'grupo' => 'ojo_izquierdo'],
                        'dnp_oi' => ['tipo' => 'text', 'label' => 'DNP OI', 'grupo' => 'ojo_izquierdo'],
                        'add_oi' => ['tipo' => 'text', 'label' => 'ADD OI', 'grupo' => 'ojo_izquierdo'],
                        'altura_oi' => ['tipo' => 'text', 'label' => 'Altura OI', 'grupo' => 'ojo_izquierdo'],
                        'nota_oi' => ['tipo' => 'textarea', 'label' => 'Notas OI', 'grupo' => 'ojo_izquierdo'],
                        
                        'dist_interpupilar' => ['tipo' => 'text', 'label' => 'Distancia Interpupilar', 'grupo' => 'general'],
                        'notas' => ['tipo' => 'textarea', 'label' => 'Notas Generales', 'grupo' => 'general']
                    ]
                ]
            ]
        ],
        'grupos' => [
            'ojo_derecho' => ['label' => 'Ojo Derecho (OD)', 'icon' => 'fas fa-eye', 'color' => 'primary'],
            'ojo_izquierdo' => ['label' => 'Ojo Izquierdo (OI)', 'icon' => 'fas fa-eye', 'color' => 'info'],
            'general' => ['label' => 'Información General', 'icon' => 'fas fa-info-circle', 'color' => 'secondary']
        ]
    ],
    
    'informe_imagen' => [
        'nombre' => 'Informe con Imagen',
        'icono' => 'fas fa-camera',
        'color' => 'warning',
        'tablas' => [
            'principal' => 'consultas',
            'detalle' => [
                'consulta_informe_imagen' => [
                    'foreign_key' => 'id_consulta',
                    'campos' => [
                        'equipo_medico' => ['tipo' => 'select', 'label' => 'Equipo Médico', 'grupo' => 'configuracion'],
                        'descripcion_od' => ['tipo' => 'textarea', 'label' => 'Descripción OD', 'grupo' => 'ojo_derecho'],
                        'descripcion_oi' => ['tipo' => 'textarea', 'label' => 'Descripción OI', 'grupo' => 'ojo_izquierdo'],
                        'archivos_od' => ['tipo' => 'file', 'label' => 'Archivos OD', 'grupo' => 'ojo_derecho', 'multiple' => true],
                        'archivos_oi' => ['tipo' => 'file', 'label' => 'Archivos OI', 'grupo' => 'ojo_izquierdo', 'multiple' => true],
                        'emails_compartir' => ['tipo' => 'text', 'label' => 'Emails para Compartir', 'grupo' => 'compartir'],
                        'compartir_activo' => ['tipo' => 'checkbox', 'label' => 'Compartir Activo', 'grupo' => 'compartir']
                    ]
                ]
            ]
        ],
        'grupos' => [
            'configuracion' => ['label' => 'Configuración', 'icon' => 'fas fa-cog', 'color' => 'secondary'],
            'ojo_derecho' => ['label' => 'Ojo Derecho (OD)', 'icon' => 'fas fa-eye', 'color' => 'primary'],
            'ojo_izquierdo' => ['label' => 'Ojo Izquierdo (OI)', 'icon' => 'fas fa-eye', 'color' => 'info'],
            'compartir' => ['label' => 'Compartir', 'icon' => 'fas fa-share', 'color' => 'success']
        ]
    ],
    
    'estudios' => [
        'nombre' => 'Estudios Médicos',
        'icono' => 'fas fa-microscope',
        'color' => 'info',
        'tablas' => [
            'principal' => 'consultas',
            'detalle' => [
                'consulta_estudios' => [
                    'foreign_key' => 'id_consulta',
                    'campos' => [
                        'equipo_medico' => ['tipo' => 'select', 'label' => 'Equipo Médico', 'grupo' => 'configuracion', 'required' => true],
                        'otro_equipo' => ['tipo' => 'text', 'label' => 'Otro Equipo', 'grupo' => 'configuracion'],
                        'resultados' => ['tipo' => 'textarea', 'label' => 'Resultados', 'grupo' => 'resultados'],
                        'emails_compartir' => ['tipo' => 'text', 'label' => 'Emails para Compartir', 'grupo' => 'compartir'],
                        'compartir_activo' => ['tipo' => 'checkbox', 'label' => 'Compartir Activo', 'grupo' => 'compartir']
                    ]
                ]
            ]
        ],
        'grupos' => [
            'configuracion' => ['label' => 'Configuración del Equipo', 'icon' => 'fas fa-cogs', 'color' => 'secondary'],
            'resultados' => ['label' => 'Resultados', 'icon' => 'fas fa-clipboard-check', 'color' => 'success'],
            'compartir' => ['label' => 'Opciones de Compartir', 'icon' => 'fas fa-share-alt', 'color' => 'info']
        ]
    ]
];
?>