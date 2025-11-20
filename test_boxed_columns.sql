-- Test if boxed layout columns exist
SELECT 
    COLUMN_NAME,
    DATA_TYPE,
    COLUMN_DEFAULT,
    IS_NULLABLE
FROM 
    INFORMATION_SCHEMA.COLUMNS
WHERE 
    TABLE_SCHEMA = 'linkmy_db' 
    AND TABLE_NAME = 'appearance'
    AND COLUMN_NAME IN (
        'boxed_layout',
        'outer_bg_type',
        'outer_bg_color',
        'outer_bg_gradient_start',
        'outer_bg_gradient_end',
        'container_bg_color',
        'container_max_width',
        'container_border_radius',
        'container_shadow'
    )
ORDER BY 
    ORDINAL_POSITION;
