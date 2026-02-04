# Weather Compact WordPress Plugin

A lightweight WordPress plugin that displays weather in a compact one-line format with click-to-expand detail.

## Features

- Compact single-line display: "Albury: 8°C, light rain"
- Weather icon (emoji) for current conditions
- Click to expand full weather details
- OpenWeatherMap API integration
- Configurable caching (default 30 minutes)
- GPS coordinates support for precise location

## Installation

1. Copy the `weather-compact` folder to your WordPress `wp-content/plugins/` directory
2. Log in to WordPress admin
3. Go to **Plugins** and activate **Weather Compact**
4. Go to **Settings → Compact Weather** to configure

## Configuration

### Get an API Key

1. Sign up at [OpenWeatherMap](https://home.openweathermap.org/users/sign_up) (free)
2. Go to **API Keys** in your account
3. Copy your API key

### Settings

| Setting | Description | Default |
|---------|-------------|---------|
| API Key | Your OpenWeatherMap API key | - |
| Location Name | Display name for the location | Albury |
| Latitude | GPS latitude | 51.8614 |
| Longitude | GPS longitude | -0.6833 |
| Units | metric (°C) or imperial (°F) | metric |
| Cache Duration | Minutes to cache weather data | 30 |

## Usage

Add the shortcode anywhere in your pages, posts, or theme:

```
[weather_compact]
```

### Display

**Compact (default):**
```
☁️ Albury: 8°C, light rain ▼
```

**Expanded (on click):**
```
Feels like: 6°C
Humidity: 92%
Wind: 7 m/s SW
Pressure: 1007 hPa
Visibility: 10 km
Sunrise: 07:42 | Sunset: 16:28
```

## Weather Icons

| Condition | Icon |
|-----------|------|
| Clear | ☀️ |
| Clouds | ☁️ |
| Rain | 🌧️ |
| Drizzle | 🌦️ |
| Thunderstorm | ⛈️ |
| Snow | ❄️ |
| Mist/Fog | 🌫️ |

## API Usage

The plugin uses the OpenWeatherMap **Current Weather Data** API, which is included in the free tier:

- 1,000 API calls/day (free)
- With 30-minute caching: ~48 calls/day max

## Automatic Updates

The plugin checks GitHub for new releases and shows update notifications in WordPress admin.

## Requirements

- WordPress 5.0 or higher
- PHP 7.0 or higher
- OpenWeatherMap API key (free)

## Changelog

### 1.0.0
- Initial release
- Compact one-line weather display
- Click-to-expand detail panel
- OpenWeatherMap API integration
- Settings page for API key, coordinates, units, cache
- Weather icon mapping
- GitHub auto-updater

## License

Creative Commons Attribution-NonCommercial 4.0 International (CC BY-NC 4.0)
