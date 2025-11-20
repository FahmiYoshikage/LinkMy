-- Update v_public_page_data view to include boxed layout columns
-- This view is used by profile.php to fetch user data

DROP VIEW IF EXISTS v_public_page_data;

CREATE VIEW v_public_page_data AS
SELECT 
    u.user_id,
    u.username,
    u.page_slug,
    a.profile_title,
    a.bio,
    a.profile_pic_filename,
    a.bg_image_filename,
    a.theme_name,
    a.button_style,
    a.font_family,
    a.custom_bg_color,
    a.custom_button_color,
    a.custom_text_color,
    a.custom_link_text_color,
    a.gradient_preset,
    a.profile_layout,
    a.container_style,
    a.show_profile_border,
    a.enable_animations,
    a.enable_glass_effect,
    a.shadow_intensity,
    a.enable_categories,
    -- Boxed Layout columns (NEW)
    a.boxed_layout,
    a.outer_bg_type,
    a.outer_bg_color,
    a.outer_bg_gradient_start,
    a.outer_bg_gradient_end,
    a.outer_bg_image,
    a.container_bg_color,
    a.container_max_width,
    a.container_border_radius,
    a.container_shadow
FROM users u
INNER JOIN appearance a ON u.user_id = a.user_id
WHERE u.email_verified = 1;

SELECT 'v_public_page_data view updated successfully with boxed layout columns!' AS Status;
