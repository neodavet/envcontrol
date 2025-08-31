# Environment Control

A flexible WordPress plugin that automatically controls settings based on environment detection (production vs non-production). **Use this as a foundation to implement custom environment-based controls for any WordPress setting.**

## 🎯 Overview

Environment Control provides intelligent environment detection and serves as a framework for implementing environment-specific WordPress configurations. The plugin includes search engine indexing control as a practical example, but can be extended to manage any WordPress setting based on your environment.

## 🚀 Key Features

### Framework Capabilities
- **Environment Detection Foundation**: Robust detection using `WP_ENV` constant or URL comparison
- **Extensible Architecture**: Easy to extend for custom environment-based controls
- **Developer-Friendly Functions**: Clean API for building custom environment logic
- **Safe Defaults**: Fail-safe behavior prevents accidental production issues

### Built-in Example: Search Engine Control
- **Automatic Indexing Control**: Prevents search engines from indexing non-production sites
- **Production Override**: Allows indexing only on production environments
- **Configurable Behavior**: Control what happens when plugin is deactivated

### User Interface
- **Status Dashboard**: Clear environment status under Tools menu
- **Visual Indicators**: Color-coded production/non-production status
- **Configuration Panel**: Easy setup and management
- **Admin Notices**: Helpful notifications for non-production environments

## 📋 Requirements

- **WordPress**: 6.8 or higher
- **PHP**: 5.6 or higher
- **Permissions**: Administrator privileges for configuration
- **Tested up to**: WordPress 6.8

## 🛠️ Installation

### From WordPress.org (Coming Soon)
1. Go to your WordPress admin dashboard
2. Navigate to **Plugins > Add New**
3. Search for "Environment Control"
4. Install and activate the plugin

### Manual Installation
1. Download the plugin files
2. Upload the `envcontrol` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **Tools > Environment Control** to configure

## ⚙️ Quick Setup

### 1. Configure Production URL
1. Navigate to **Tools > Environment Control**
2. Enter your production site URL (e.g., `https://www.yoursite.com/`)
3. Save settings

### 2. Environment Detection Setup

#### Method A: WP_ENV Constant (Recommended)
Add to your `wp-config.php`:
```php
// Production environment
define('WP_ENV', 'production');

// Non-production environments
define('WP_ENV', 'development');
define('WP_ENV', 'staging');
define('WP_ENV', 'testing');
```

#### Method B: URL Comparison
If `WP_ENV` is not defined, the plugin automatically compares:
- Current site URL: `home_url('/')`
- Configured production URL (from settings)

## 🔧 How It Works

### Environment Detection Logic
```
Is WP_ENV defined?
├── Yes: Is WP_ENV === 'production'?
│   ├── Yes: Production Mode ✅
│   └── No: Non-Production Mode ⚠️
└── No: Does current URL match production URL?
    ├── Yes: Production Mode ✅
    └── No: Non-Production Mode ⚠️
```

### Built-in Search Engine Control
- **Production**: Search engines allowed to index
- **Non-Production**: Search engines automatically blocked
- **Plugin Deactivation**: Safe default (allows indexing)

## 🛡️ Extensibility Framework

### For Developers

Use Environment Control as a foundation for custom environment-based features:

```php
// Check environment in your custom code
if (env_control_is_production_environment()) {
    // Production-only functionality
    enable_analytics();
    enable_caching();
} else {
    // Development/staging functionality
    enable_debug_mode();
    disable_email_sending();
}

// Get plugin settings
$settings = env_control_get_settings();
$production_url = env_control_get_production_url();
```

### Extend for Custom Controls

The plugin architecture makes it easy to add environment-based controls for:
- **Email Settings**: Disable email sending on non-production
- **Analytics**: Enable/disable tracking codes
- **Caching**: Environment-specific caching strategies
- **Debug Settings**: Automatic debug mode on development
- **Payment Gateways**: Sandbox vs live payment processing
- **CDN Settings**: Different CDN configurations per environment
- **Social Media**: Prevent social sharing on staging sites

### Example Custom Extension

```php
// Add custom environment-based email control
add_filter('pre_option_admin_email', function($default) {
    if (!env_control_is_production_environment()) {
        return 'dev@yoursite.com'; // Use dev email on non-production
    }
    return $default; // Use configured email on production
});
```

## 📝 Use Cases

### Base Framework
- **Multi-Environment Workflows**: Consistent behavior across environments
- **Custom Setting Control**: Environment-specific WordPress configurations
- **Development Safety**: Prevent production issues during development
- **Staging Protection**: Isolate staging environments from production behaviors

### Search Engine Control (Included)
- **Development Sites**: Prevent localhost indexing
- **Staging Sites**: Protect staging URLs from search engines
- **Client Sites**: Ensure development work stays private
- **Testing Environments**: Block accidental search engine discovery

## 🔌 Developer Functions

### Available Functions
- `env_control_is_production_environment()` - Check if current environment is production
- `env_control_get_settings()` - Get all plugin settings
- `env_control_get_production_url()` - Get configured production URL

### Hooks and Filters
- Use WordPress standard hooks to extend functionality
- Filter `pre_option_{option_name}` to control any WordPress option
- Add admin notices with `admin_notices` action

## 🤝 Contributing

We welcome contributions! This plugin is designed to be a community-driven framework.

### Development
1. Fork the repository
2. Create a feature branch
3. Make your changes and test thoroughly
4. Submit a Pull Request

### Ideas for Contributions
- Additional environment detection methods
- New example implementations
- Documentation improvements
- Performance optimizations
- Security enhancements

## 📊 Roadmap

### Framework Enhancements
- [ ] WP-CLI commands for environment management
- [ ] Multi-site (network) support
- [ ] Advanced environment detection methods
- [ ] Built-in logging and monitoring

### Example Implementations
- [ ] Email control module
- [ ] Analytics control module
- [ ] Debug settings module
- [ ] Payment gateway control module

### Integration
- [ ] Popular development tool integrations
- [ ] Docker environment detection
- [ ] Cloud platform integrations

## 📄 License

**GPL2+ License** - Free and open source

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

## 👨‍💻 Author

**neodavet** - [Portfolio](https://neodavet.github.io/davetportfolio/)

## 🆘 Support

- **Documentation**: This README and inline code documentation
- **Issues**: GitHub Issues (coming soon)
- **WordPress.org Support**: Available after plugin publication

---

## 💡 Getting Started Tips

1. **Start Simple**: Use the built-in search engine control to understand the plugin
2. **Extend Gradually**: Add custom environment controls one at a time
3. **Test Thoroughly**: Always test environment detection in your specific setup
4. **Use WP_ENV**: More reliable than URL comparison for most hosting environments
5. **Safe Defaults**: The plugin is designed to fail safely - when in doubt, it assumes production

**⚠️ Important**: Always test in development before deploying to production. This plugin can modify WordPress core settings based on environment detection.

**🎯 Perfect For**: Developers, agencies, and teams managing WordPress sites across multiple environments who want intelligent, automatic environment-based configuration management.
