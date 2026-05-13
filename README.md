# CCL UK WordPress Theme

A responsive WordPress theme designed specifically for Citizens' Climate Lobby UK website.

## Theme Information

- **Theme Name**: CCL UK
- **Version**: 2.8.3
- **Author**: Paul Jenkins
- **Author URI**: https://github.com/turnpiece
- **License**: GNU General Public License v3 or later
- **License URI**: http://www.gnu.org/licenses/gpl-3.0.html
- **Requires at least**: WordPress 3.8
- **Tested up to**: WordPress 6.8

## Description

CCL UK is a responsive WordPress theme that focuses on simplicity and ease of use. It's specifically designed for the Citizens' Climate Lobby UK organization and includes custom post types, homepage sections, and integration with various plugins commonly used by climate advocacy organizations.

## Key Features

### Custom Post Types

- **News Posts** (`ccluk_news`): Custom post type for news articles with archive support
- **Events** (`incsub_event`): Integration with event management plugins

### Homepage Sections

The theme includes modular homepage sections that can be customized via the WordPress Customizer:

- **Banner Section**: Hero banner with customizable heading, text, image, and call-to-action buttons
- **About Section**: Information about the organization
- **News Section**: Displays latest news posts
- **Newsletter Section**: Newsletter signup form integration
- **Posts Section**: Latest blog posts
- **Events Section**: Upcoming events display

### Customization Options

- **WordPress Customizer Integration**: Extensive customization options for homepage sections
- **Custom Widgets**: Newsletter signup widget with privacy policy integration
- **Responsive Design**: Mobile-first approach with breakpoints for various screen sizes
- **Custom Image Sizes**:
  - `ccluk-medium`: 750x1000px
  - `ccluk-hero`: 1200x800px (cropped)
  - `ccluk-feature`: 580x387px (cropped)

### Theme Structure

```
wp-content/themes/ccluk/
├── assets/
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── scss/          # SCSS source files
├── inc/               # Theme functionality
│   ├── customizer.php # Customizer options
│   ├── theme-functions.php
│   └── widgets/       # Custom widgets
├── section-parts/     # Homepage section templates
├── template-parts/    # Reusable template components
└── page-templates/    # Custom page templates
```

### Custom Page Templates

- **Home Page Template**: Full-width template for homepage content
- **No Sidebar Template**: Page template without sidebar

### Plugin Integrations

- **Give Donations**: Styling for donation forms
- **Events Calendar**: Custom styling for event displays
- **MailChimp**: Newsletter signup integration
- **Related Posts**: Custom styling for related content

### Accessibility Features

- Screen reader text support
- Keyboard navigation friendly
- Semantic HTML structure
- ARIA labels and roles

### Performance Optimizations

- Minified CSS and JavaScript files
- Optimized image loading
- Efficient query handling
- Caching support

## Installation

1. Upload the theme folder to `/wp-content/themes/` directory
2. Activate the theme through the 'Appearance' menu in WordPress
3. Configure the homepage sections via Appearance > Customize

## Configuration

### Homepage Setup

1. Set a static front page in Settings > Reading
2. Use the Home Page Template for your front page
3. Configure homepage sections via Appearance > Customize

### Newsletter Integration

1. Set up your MailChimp form in Appearance > Customize > Newsletter
2. Add the Newsletter Signup widget to your sidebar or footer

### Custom Post Types

- News posts are automatically included in category and tag archives
- Events integration requires compatible event management plugin

## Customization

### Adding Custom Sections

The theme supports adding custom homepage sections through hooks:

```php
add_action('ccluk_frontpage_section_parts', function() {
    // Your custom section code here
});
```

### Custom Styling

- Main styles are in `/assets/css/main.css`
- SCSS source files are in `/assets/scss/`
- Custom styles can be added to the child theme

### Hooks and Filters

The theme provides various hooks for customization:

- `ccluk_frontpage_sections_order`: Modify homepage section order
- `ccluk_before_section_*`: Action before specific sections
- `ccluk_after_section_*`: Action after specific sections

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Development

### Building Assets

The theme uses SCSS for styling and includes a modern build process with npm and Sass.

#### Prerequisites

- Node.js (v16 or higher)
- npm

#### Setup

1. Navigate to the theme directory: `cd wp-content/themes/ccluk`
2. Install dependencies: `npm install`

#### Available Scripts

- `npm run build` - Build CSS for production (expanded + compressed)
- `npm run dev` - Start development mode with file watching
- `npm run build:css` - Compile SCSS to CSS only
- `npm run watch` - Alias for `npm run dev`

#### Development Workflow

```bash
# Start development with file watching
npm run dev

# Build for production
npm run build
```

The build process will:

- Compile SCSS from `/assets/scss/` to `/assets/css/main.css` (expanded)
- Generate compressed version at `/assets/css-compressed/main.css` (minified)
- Watch for changes during development
- Generate source maps for debugging

### Debugging

Enable debug mode by setting `CCLUK_DEBUGGING` to `true` in `functions.php`.

## Support

For support and questions, please contact the theme author or visit the [Citizens' Climate Lobby UK website](https://citizensclimatelobby.uk/).

## Changelog

### Version 2.8.3

- Current stable version
- WordPress 6.8 compatibility
- Various bug fixes and improvements

## License

This theme is licensed under the GPL v3 or later. See the [GNU General Public License](http://www.gnu.org/licenses/gpl-3.0.html) for details.

## Credits

- Based on WordPress theme development best practices
- Integrates with various third-party plugins
- Designed specifically for climate advocacy organizations
