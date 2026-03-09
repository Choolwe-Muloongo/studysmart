<?php
if (!function_exists('render_brand_logo')) {
    /**
     * Render shared StudySmart brand logo.
     *
     * @param array<string, string> $options
     */
    function render_brand_logo(array $options = []): void
    {
        $href = $options['href'] ?? '#';
        $logoPath = $options['logo_path'] ?? 'WhatsApp_Image_2025-08-16_at_09.16.01_9301e0c4-removebg-preview.png';
        $class = trim(($options['class'] ?? '') . ' brand-logo brand-logo--' . ($options['size'] ?? 'md'));
        $text = $options['text'] ?? 'StudySmart';
        $alt = $options['alt'] ?? 'StudySmart logo';

        echo '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';
        echo '<img src="' . htmlspecialchars($logoPath, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" class="brand-logo__image" loading="lazy" onerror="this.classList.add(\'is-hidden\'); this.nextElementSibling.classList.remove(\'is-hidden\');" />';
        echo '<span class="brand-logo__fallback is-hidden"><i class="fas fa-graduation-cap" aria-hidden="true"></i><span>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span></span>';
        echo '<span class="brand-logo__text">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span>';
        echo '</a>';
    }
}
