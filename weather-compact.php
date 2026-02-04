<?php
/**
 * Plugin Name: Weather Compact
 * Plugin URI: https://github.com/nonatech-uk/wp-weather-compact
 * Description: Compact one-line weather display with click-to-expand detail
 * Version: 1.0.2
 * Author: NonaTech Services Ltd
 * License: CC BY-NC 4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WEATHER_COMPACT_VERSION', '1.0.2');

// Initialize GitHub updater
require_once plugin_dir_path(__FILE__) . 'includes/class-github-updater.php';
new WeatherCompact_GitHub_Updater(
    __FILE__,
    'nonatech-uk/wp-weather-compact',
    WEATHER_COMPACT_VERSION
);

class Weather_Compact {
    private static $instance = null;
    private $option_name = 'weather_compact_settings';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_shortcode('weather_compact', array($this, 'shortcode_handler'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Add settings page under Settings menu
     */
    public function add_settings_page() {
        add_options_page(
            'Parish Weather Settings',
            'Parish Weather',
            'manage_options',
            'weather-compact',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting($this->option_name, $this->option_name, array($this, 'sanitize_settings'));

        add_settings_section(
            'weather_compact_main',
            'Weather Settings',
            null,
            'weather-compact'
        );

        add_settings_field(
            'api_key',
            'OpenWeatherMap API Key',
            array($this, 'render_text_field'),
            'weather-compact',
            'weather_compact_main',
            array('field' => 'api_key', 'type' => 'password')
        );

        add_settings_field(
            'location_name',
            'Location Name',
            array($this, 'render_text_field'),
            'weather-compact',
            'weather_compact_main',
            array('field' => 'location_name', 'default' => 'Albury')
        );

        add_settings_field(
            'latitude',
            'Latitude',
            array($this, 'render_text_field'),
            'weather-compact',
            'weather_compact_main',
            array('field' => 'latitude', 'default' => '51.8614')
        );

        add_settings_field(
            'longitude',
            'Longitude',
            array($this, 'render_text_field'),
            'weather-compact',
            'weather_compact_main',
            array('field' => 'longitude', 'default' => '-0.6833')
        );

        add_settings_field(
            'units',
            'Units',
            array($this, 'render_units_field'),
            'weather-compact',
            'weather_compact_main'
        );

        add_settings_field(
            'cache_duration',
            'Cache Duration (minutes)',
            array($this, 'render_text_field'),
            'weather-compact',
            'weather_compact_main',
            array('field' => 'cache_duration', 'default' => '30', 'type' => 'number')
        );
    }

    /**
     * Render text field
     */
    public function render_text_field($args) {
        $options = get_option($this->option_name, array());
        $field = $args['field'];
        $default = isset($args['default']) ? $args['default'] : '';
        $type = isset($args['type']) ? $args['type'] : 'text';
        $value = isset($options[$field]) ? $options[$field] : $default;
        echo '<input type="' . esc_attr($type) . '" name="' . $this->option_name . '[' . esc_attr($field) . ']" value="' . esc_attr($value) . '" class="regular-text">';
    }

    /**
     * Render units dropdown
     */
    public function render_units_field() {
        $options = get_option($this->option_name, array());
        $value = isset($options['units']) ? $options['units'] : 'metric';
        ?>
        <select name="<?php echo $this->option_name; ?>[units]">
            <option value="metric" <?php selected($value, 'metric'); ?>>Metric (°C, m/s)</option>
            <option value="imperial" <?php selected($value, 'imperial'); ?>>Imperial (°F, mph)</option>
        </select>
        <?php
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        $sanitized['api_key'] = sanitize_text_field($input['api_key'] ?? '');
        $sanitized['location_name'] = sanitize_text_field($input['location_name'] ?? 'Albury');
        $sanitized['latitude'] = floatval($input['latitude'] ?? 51.8614);
        $sanitized['longitude'] = floatval($input['longitude'] ?? -0.6833);
        $sanitized['units'] = in_array($input['units'] ?? '', ['metric', 'imperial']) ? $input['units'] : 'metric';
        $sanitized['cache_duration'] = max(1, intval($input['cache_duration'] ?? 30));
        return $sanitized;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Compact Weather Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->option_name);
                do_settings_sections('weather-compact');
                submit_button();
                ?>
            </form>
            <hr>
            <h2>Usage</h2>
            <p>Use the shortcode <code>[weather_compact]</code> to display the weather widget.</p>
            <p>Get your free API key from <a href="https://home.openweathermap.org/api_keys" target="_blank">OpenWeatherMap</a>.</p>
        </div>
        <?php
    }

    /**
     * Enqueue CSS and JS
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'weather-compact',
            plugin_dir_url(__FILE__) . 'css/weather-compact.css',
            array(),
            WEATHER_COMPACT_VERSION
        );
        wp_enqueue_script(
            'weather-compact',
            plugin_dir_url(__FILE__) . 'js/weather-compact.js',
            array(),
            WEATHER_COMPACT_VERSION,
            true
        );
    }

    /**
     * Handle shortcode
     */
    public function shortcode_handler($atts) {
        $options = get_option($this->option_name, array());

        if (empty($options['api_key'])) {
            return '<span class="weather-compact-error">Weather: API key not configured</span>';
        }

        $weather = $this->get_weather_data();

        if (is_wp_error($weather)) {
            return '<span class="weather-compact-error">Weather unavailable</span>';
        }

        $location = $options['location_name'] ?? 'Albury';
        $units = $options['units'] ?? 'metric';
        $temp_unit = $units === 'metric' ? '°C' : '°F';
        $speed_unit = $units === 'metric' ? 'm/s' : 'mph';

        $icon = $this->get_weather_icon($weather['condition']);
        $temp = round($weather['temp']);
        $feels_like = round($weather['feels_like']);
        $description = ucfirst($weather['description']);

        $output = '<div class="weather-compact">';
        $output .= '<span class="weather-compact-icon">' . $icon . '</span>';
        $output .= '<span class="weather-compact-summary">' . esc_html($location) . ': ' . $temp . $temp_unit . ', ' . esc_html($description) . '</span>';
        $output .= '<span class="weather-compact-toggle">&#9660;</span>';

        $output .= '<div class="weather-compact-detail">';
        $output .= '<div>Feels like: ' . $feels_like . $temp_unit . '</div>';
        $output .= '<div>Humidity: ' . $weather['humidity'] . '%</div>';
        $output .= '<div>Wind: ' . round($weather['wind_speed']) . ' ' . $speed_unit . ' ' . $this->degrees_to_direction($weather['wind_deg']) . '</div>';
        $output .= '<div>Pressure: ' . $weather['pressure'] . ' hPa</div>';
        $output .= '<div>Visibility: ' . round($weather['visibility'] / 1000) . ' km</div>';
        $output .= '<div>Sunrise: ' . $weather['sunrise'] . ' | Sunset: ' . $weather['sunset'] . '</div>';
        $output .= '</div>';

        $output .= '</div>';

        return $output;
    }

    /**
     * Fetch weather data from API (with caching)
     */
    private function get_weather_data() {
        $options = get_option($this->option_name, array());
        $cache_key = 'weather_compact_data';
        $cache_duration = ($options['cache_duration'] ?? 30) * MINUTE_IN_SECONDS;

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $api_key = $options['api_key'] ?? '';
        $lat = $options['latitude'] ?? 51.8614;
        $lon = $options['longitude'] ?? -0.6833;
        $units = $options['units'] ?? 'metric';

        $url = add_query_arg(array(
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $api_key,
            'units' => $units
        ), 'https://api.openweathermap.org/data/2.5/weather');

        $response = wp_remote_get($url, array('timeout' => 10));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['main'])) {
            return new WP_Error('api_error', 'Invalid API response');
        }

        $weather = array(
            'temp' => $data['main']['temp'],
            'feels_like' => $data['main']['feels_like'],
            'humidity' => $data['main']['humidity'],
            'pressure' => $data['main']['pressure'],
            'wind_speed' => $data['wind']['speed'] ?? 0,
            'wind_deg' => $data['wind']['deg'] ?? 0,
            'visibility' => $data['visibility'] ?? 10000,
            'condition' => $data['weather'][0]['main'] ?? 'Clear',
            'description' => $data['weather'][0]['description'] ?? 'clear sky',
            'sunrise' => date('H:i', $data['sys']['sunrise']),
            'sunset' => date('H:i', $data['sys']['sunset'])
        );

        set_transient($cache_key, $weather, $cache_duration);

        return $weather;
    }

    /**
     * Map weather condition to emoji icon
     */
    private function get_weather_icon($condition) {
        $icons = array(
            'Clear' => '☀️',
            'Clouds' => '☁️',
            'Rain' => '🌧️',
            'Drizzle' => '🌦️',
            'Thunderstorm' => '⛈️',
            'Snow' => '❄️',
            'Mist' => '🌫️',
            'Fog' => '🌫️',
            'Haze' => '🌫️',
            'Smoke' => '🌫️',
            'Dust' => '🌫️',
            'Sand' => '🌫️',
            'Ash' => '🌫️',
            'Squall' => '💨',
            'Tornado' => '🌪️'
        );
        return $icons[$condition] ?? '🌡️';
    }

    /**
     * Convert wind degrees to compass direction
     */
    private function degrees_to_direction($degrees) {
        $directions = array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW');
        $index = round($degrees / 45) % 8;
        return $directions[$index];
    }
}

// Initialize the plugin
Weather_Compact::get_instance();
