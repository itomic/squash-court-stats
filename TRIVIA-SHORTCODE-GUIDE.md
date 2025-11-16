# Squash Trivia Shortcode Guide

## Overview

The `[squash_trivia]` shortcode provides a native WordPress integration for the Squash Trivia page from stats.squashplayers.app. Unlike an iframe embed, this implementation fetches data directly from your Laravel API and renders it natively in WordPress, providing better performance, SEO, and user experience.

## Basic Usage

To display the entire trivia page with all sections:

```
[squash_trivia]
```

## Shortcode Attributes

### `section` (optional)

Display a specific trivia section instead of all sections.

**Options:**
- `all` (default) - Display all trivia sections
- `countries-without-venues` - Countries without squash venues
- `high-altitude` - High altitude venues (2000m+)
- `extreme-latitude` - Most northerly and southerly venues
- `hotels-resorts` - Hotels and resorts with squash courts
- `population-area` - Venues and courts by population and land area
- `unknown-courts` - Venues with unknown number of courts
- `country-club` - The 100% Country Club
- `word-cloud` - Countries by number of venues (word cloud)
- `loneliest` - Loneliest squash courts
- `graveyard` - Squash court graveyard (closed/deleted venues)

**Examples:**

Display only the high altitude venues section:
```
[squash_trivia section="high-altitude"]
```

Display only the graveyard section:
```
[squash_trivia section="graveyard"]
```

### `filter` (optional)

Apply geographic filtering to the data (future enhancement).

**Example:**
```
[squash_trivia section="hotels-resorts" filter="continent:3"]
```

## Features

### Interactive Maps

All geographic sections include interactive Leaflet maps with:
- Clickable markers showing venue details
- Color-coded markers based on data (elevation, distance, etc.)
- Automatic zoom to fit all markers
- Popup information on click

### Sortable Tables

Tables in the trivia page support:
- Click column headers to sort
- Ascending/descending sort indicators
- Smart sorting (numeric vs. alphabetic)
- Responsive design for mobile devices

### Filters and Tabs

Some sections include:
- Continent filters (Hotels, Unknown Courts, Country Club)
- Tabbed views (Northerly/Southerly venues)
- Country and reason filters (Graveyard)

### Data Visualization

- **Word Cloud**: Visual representation of countries by venue count
- **Maps**: Leaflet-based interactive maps
- **Statistics Boxes**: Key metrics displayed prominently
- **Color-Coded Badges**: Visual indicators for elevation, distance, etc.

## Styling

The shortcode includes comprehensive CSS styling that:
- Matches modern web design standards
- Provides responsive layouts for mobile devices
- Uses a purple gradient theme consistent with the stats dashboard
- Includes hover effects and smooth transitions
- Supports dark/light theme compatibility

### Custom Styling

You can override the default styles by adding custom CSS to your WordPress theme:

```css
/* Example: Change the header gradient */
.squash-trivia-header {
    background: linear-gradient(135deg, #your-color-1 0%, #your-color-2 100%);
}

/* Example: Change stat box colors */
.stat-box {
    background: #your-color;
}
```

## API Integration

The shortcode fetches data from these API endpoints:

- `/api/countries-without-venues`
- `/api/venues-with-elevation`
- `/api/extreme-latitude-venues`
- `/api/hotels-and-resorts`
- `/api/countries-with-venues-stats`
- `/api/venues-with-unknown-courts`
- `/api/country-club-100-percent`
- `/api/countries-wordcloud`
- `/api/loneliest-courts`
- `/api/court-graveyard`
- `/api/deletion-reasons`

All API calls are cached for 3 hours to improve performance.

## Dependencies

The shortcode automatically loads these external libraries:

1. **Leaflet** (v1.9.4) - For interactive maps
2. **Chart.js** (v4.4.0) - For charts (future use)
3. **WordCloud2.js** (v1.2.2) - For word cloud visualization
4. **jQuery** - For DOM manipulation and AJAX

These are loaded only on pages that contain the `[squash_trivia]` shortcode.

## Browser Compatibility

The trivia page is compatible with:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

### Caching

- API responses are cached for 3 hours on the server
- Browser caching for CSS and JavaScript assets
- Lazy loading of maps and visualizations

### Optimization

- Minimal DOM manipulation
- Efficient data rendering
- Responsive images and maps
- Gzip compression for assets

## Examples

### Full Page

Create a WordPress page with just:
```
[squash_trivia]
```

This will render all trivia sections in a beautiful, interactive format.

### Dedicated Sections

Create separate pages for different trivia topics:

**Page: "High Altitude Squash Venues"**
```
[squash_trivia section="high-altitude"]
```

**Page: "Squash Court Graveyard"**
```
[squash_trivia section="graveyard"]
```

**Page: "Hotels with Squash Courts"**
```
[squash_trivia section="hotels-resorts"]
```

### Sidebar Widget

You can also use the shortcode in sidebar widgets (if your theme supports it):

```
[squash_trivia section="unknown-courts"]
```

## Troubleshooting

### Maps Not Loading

If maps don't appear:
1. Check browser console for JavaScript errors
2. Ensure Leaflet is loading (check Network tab)
3. Verify API endpoints are accessible
4. Check for conflicts with other map plugins

### Data Not Displaying

If data shows "Loading..." indefinitely:
1. Check API endpoint URLs in browser console
2. Verify CORS settings on your Laravel API
3. Check for JavaScript errors in console
4. Ensure jQuery is loaded

### Styling Issues

If styling looks broken:
1. Check for CSS conflicts with your theme
2. Verify `trivia.css` is loading
3. Check browser console for 404 errors
4. Clear WordPress and browser cache

## Support

For issues or questions:
- Check the browser console for errors
- Review the API response in Network tab
- Contact support with specific error messages

## Changelog

### Version 1.5.0
- Added `[squash_trivia]` shortcode
- Native WordPress integration for trivia page
- Interactive maps with Leaflet
- Sortable tables
- Word cloud visualization
- Responsive design
- API-driven data fetching

## Future Enhancements

Planned features:
- Geographic filtering by continent/country
- Export data to CSV/PDF
- Social sharing buttons
- Venue comparison tools
- Historical data trends
- Custom color themes
- Admin settings page for API configuration

