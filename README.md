# Ad Network Link Plugin for WordPress

A lightweight WordPress plugin to create shareable ad links with hosted images that redirect to specified URLs — ideal for ad networks, affiliate marketing, and multi-site campaigns.

## Features

- Create ad links with custom images (PNG, GIF, AVIF, JPG, WebP)
- Set default redirect URLs and override per site
- Track click statistics
- Simple shortcode implementation
- Multi-site network support
- Responsive output and easy media library integration
- Admin UI for managing links and settings

## Installation

1. Upload the `ad-network-plugin` folder to `/wp-content/plugins/`
   - Or install via WordPress admin: Plugins → Add New → Upload Plugin
2. Activate the plugin from the Plugins screen.
3. The plugin will create the necessary database tables automatically.

## File structure

```
ad-network-plugin/
├── ad-network-plugin.php
├── includes/
│   ├── class-ad-network-core.php
│   ├── class-ad-network-admin.php
│   ├── class-ad-network-frontend.php
│   └── class-ad-network-shortcode.php
├── templates/
│   ├── admin-all-links.php
│   ├── admin-add-new.php
│   └── admin-settings.php
├── assets/
│   ├── css/
│   │   ├── admin-style.css
│   │   └── frontend-style.css
│   └── js/
│       └── admin-script.js
└── README.md
```

## Usage

### For Main Site (Ad Creator)

1. Go to **Ad Network → Add New** in the admin.
2. Enter a name, upload/select an image, and provide the default redirect URL.
3. Mark the link as the main site if applicable and create the ad link.
4. Copy the generated shortcode from **Ad Network → All Links**.

Example shortcode:
```
[ad_network_link id="adnl_67328c4ab1d5b3.45678901"]
```

### For Partner Sites (Ad Displayers)

1. Install and activate the plugin on the partner site.
2. Insert the shortcode into any post, page, or widget.
3. Optionally override the redirect using the `redirect` parameter.

Example with override:
```
[ad_network_link id="adnl_67328c4ab1d5b3.45678901" redirect="https://your-site.com/landing"]
```

## Shortcode parameters

| Parameter  | Required | Description                 | Default                      |
|------------|----------|-----------------------------|------------------------------|
| id         | Yes      | The unique ad link ID       | —                            |
| redirect   | No       | Custom redirect URL         | Default URL from main site   |
| width      | No       | Image width (CSS value)     | 100%                         |
| height     | No       | Image height (CSS value)    | auto                         |
| class      | No       | Custom CSS class            | —                            |

## Examples

Basic:
```
[ad_network_link id="adnl_67328c4ab1d5b3.45678901"]
```

With custom redirect:
```
[ad_network_link id="adnl_67328c4ab1d5b3.45678901" redirect="https://partner-site.com/offer"]
```

With custom width and class:
```
[ad_network_link id="adnl_67328c4ab1d5b3.45678901" width="300px" class="my-custom-ad"]
```

## How it works

1. Main site creates an ad link and hosts the ad image.
2. Partner sites install the plugin and include the provided shortcode.
3. When a user clicks the ad, the click is tracked and the user is redirected to the configured URL.

## Database

The plugin creates a table named `wp_ad_network_links`. Example schema:
```sql
CREATE TABLE wp_ad_network_links (
    id mediumint(9) AUTO_INCREMENT,
    link_id varchar(50) NOT NULL UNIQUE,
    name varchar(255) NOT NULL,
    image_url varchar(500) NOT NULL,
    redirect_url varchar(500) NOT NULL,
    is_main_site tinyint(1) DEFAULT 0,
    clicks int(11) DEFAULT 0,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

## Settings

Admin → Ad Network → Settings:
- Main Site URL — URL of the main ad host site
- Default Redirect URL — fallback redirect if none specified

## Security

- Nonce verification for admin forms
- Capability checks (managed via `manage_options`)
- SQL queries use $wpdb->prepare for protection against injection
- Output is escaped to prevent XSS
- URL validation and sanitization on input

## Compatibility

- WordPress 5.0+
- PHP 7.2+
- MySQL 5.6+
- Modern browsers (Chrome, Firefox, Safari, Edge, mobile)

## Support

For support, feature requests, or bug reports, contact the plugin developer (see plugin header for contact details).

## Changelog

### v1.0.0
- Initial release: ad creation, shortcode rendering, click tracking, multi-site support, admin UI

## License

GPL v2 or later

## Credits

Developed by Michael Tallada

---
Need help or want to contribute? Open an issue or pull request on the plugin repository.
