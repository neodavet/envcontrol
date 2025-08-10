# WP Environment Privacy Control

A WordPress plugin that automatically controls search engine indexing based on your environment (production vs development/staging).

## 🎯 Goal

This plugin prevents accidental search engine indexing of development, staging, or testing WordPress sites by automatically enforcing the "Discourage search engines from indexing this site" option when not in a production environment.

## 🚀 Features

### Core Functionality
- **Automatic Environment Detection**: Detects production vs non-production environments using two methods:
  - `WP_ENV` constant (takes priority if defined)
  - URL comparison between current site and configured production URL
- **Automatic Privacy Control**: Forces search engine discouragement on non-production environments
- **Production Override**: Allows search engine indexing only on production sites
- **Admin Dashboard**: Provides a clear status overview and configuration interface

### Configuration Options
- **Production URL Setting**: Define your production site URL for environment comparison
- **Plugin Deactivation Behavior**: Choose whether to restore search engine indexing when the plugin is deactivated

### User Interface
- **Status Dashboard**: Real-time environment status display under Tools menu
- **Visual Indicators**: Clear production/non-production status with color-coded indicators
- **Settings Integration**: Direct settings link from the plugins page
- **Admin Notices**: Warning notifications when running on non-production environments

### Safety Features
- **Database Protection**: Automatically updates the `blog_public` option based on environment
- **Activation/Deactivation Hooks**: Properly handles plugin lifecycle events
- **Backward Compatibility**: Maintains compatibility with existing installations

## 📋 Requirements

- WordPress 4.0 or higher
- PHP 5.6 or higher
- Administrator privileges for configuration

## 🛠️ Installation

### From WordPress.org (Coming Soon)
1. Go to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "WP Environment Privacy Control"
4. Install and activate the plugin

### Manual Installation
1. Download the plugin files
2. Upload the `env-privacy-control` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **Tools > WP Environment Privacy Control** to configure

## ⚙️ Configuration

### Setting Up Production URL
1. Navigate to **Tools > WP Environment Privacy Control**
2. Enter your production site URL (e.g., `https://www.yoursite.com/`)
3. Configure plugin deactivation behavior
4. Save settings

### Environment Detection Methods

#### Method 1: WP_ENV Constant (Recommended)
Add this to your `wp-config.php`:
```php
// For production
define('WP_ENV', 'production');

// For development/staging
define('WP_ENV', 'development');
// or
define('WP_ENV', 'staging');
```

#### Method 2: URL Comparison
If `WP_ENV` is not defined, the plugin compares:
- Current site URL: `home_url('/')`
- Configured production URL (from plugin settings)

## 🎨 How It Works

### Environment Logic
```
Is WP_ENV defined?
├── Yes: Is WP_ENV === 'production'?
│   ├── Yes: Allow search engine indexing ✅
│   └── No: Discourage search engine indexing ❌
└── No: Does current URL match production URL?
    ├── Yes: Allow search engine indexing ✅
    └── No: Discourage search engine indexing ❌
```

### Plugin Lifecycle
1. **Activation**: Immediately applies environment-based privacy settings
2. **Runtime**: Continuously monitors and enforces correct settings
3. **Deactivation**: Optionally restores search engine indexing (configurable)

## 🔧 Use Cases

- **Development Sites**: Prevent accidental indexing of localhost or development domains
- **Staging Sites**: Protect staging environments from search engines
- **Multi-Environment Workflows**: Seamlessly deploy across different environments
- **Client Sites**: Ensure development work doesn't appear in search results

## 📱 Screenshots

The plugin provides:
- Environment status dashboard with visual indicators
- Current vs production URL comparison
- Search engine indexing status
- Configuration options
- Real-time environment detection

## 🤝 Contributing

We welcome contributions! This plugin will be submitted to the WordPress.org repository, and we encourage community involvement.

### How to Contribute
1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup
1. Clone the repository
2. Set up a local WordPress development environment
3. Install the plugin in your WordPress plugins directory
4. Make your changes and test thoroughly

### Reporting Issues
- Use GitHub Issues to report bugs or request features
- Include WordPress version, PHP version, and steps to reproduce
- Check existing issues before creating new ones

## 📝 Changelog

### Version 2.0
- Complete rewrite with improved environment detection
- Added configurable plugin deactivation behavior
- Enhanced admin interface with status dashboard
- Improved URL handling and validation
- Added backward compatibility support

## 📄 License

This plugin is licensed under the GPL2+ license.

## 👨‍💻 Author

**neodavet** - [GitHub Profile](https://github.com/neodavet)

## 🆘 Support

- **Documentation**: This README file
- **Issues**: [GitHub Issues](https://github.com/neodavet/env-privacy-control/issues)
- **WordPress.org Support**: Coming soon when plugin is published

## 🔮 Roadmap

- [ ] Submit to WordPress.org repository
- [ ] Add WP-CLI support
- [ ] Multi-site (network) support
- [ ] Additional environment detection methods
- [ ] Integration with popular development tools
- [ ] Advanced notification options

---

**⚠️ Important**: Always test the plugin in a development environment before deploying to production. This plugin modifies core WordPress privacy settings.

**💡 Pro Tip**: Use the `WP_ENV` constant method for more reliable environment detection, especially in containerized or cloud environments.
