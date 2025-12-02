-- Quick fix: Enable boxed layout for user_id 12 (Fahmi)
UPDATE appearance 
SET container_style = 'boxed', 
    enable_categories = 1 
WHERE user_id = 12;

-- Verify
SELECT user_id, container_style, enable_categories 
FROM appearance 
WHERE user_id = 12;
