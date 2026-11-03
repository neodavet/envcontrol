# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

"Env Control" is a single-file WordPress plugin (`env-control.php`, ~350 lines, procedural, no classes). It lives in a Local (LocalWP) site's `wp-content/plugins/envcontrol` directory. There is no build step, package manager, linter config, or test suite — edit the PHP and reload the WordPress admin. Requires WordPress 6.9+ / PHP 7.0+.

Text domain is `env-control`; function/option prefix is `env_control_` / `ENV_CONTROL_`. Menu slug is `env-control` (page under **Tools**, screen id `tools_page_env-control`).

## Architecture

- **Environment detection** — `env_control_is_production_environment()` is the core API. If the `WP_ENV` constant is defined, production iff `WP_ENV === 'production'`; otherwise compares `home_url('/')` for strict equality with the configured production URL (which is normalized to have a trailing slash in `env_control_sanitize_settings`). Anything new that depends on environment should call this function rather than re-implementing detection.
- **Settings** — single option `env_control_settings` (`ENV_CONTROL_OPTION`) with keys `production_url` and `disable_when_plugin_disabled`. Always read through `env_control_get_settings()` which merges defaults. Registered via the Settings API in `env_control_register_settings()`; the settings page is rendered by `env_control_admin_page()`.
- **Built-in control (search indexing)** — a `pre_option_blog_public` filter forces `0` on non-production and `1` on production, overriding the DB value at read time. Activation/deactivation hooks additionally write `blog_public` to the DB (activation: per environment; deactivation: always `1` as a safe default so a production site is never left blocked).
- **Admin notice** — shown only on the dashboard and the plugin's settings screen, only in non-production.
- The README presents the plugin as an extensible framework; new environment-based controls are meant to be added as further filters/hooks gated on `env_control_is_production_environment()`.

## Release / docs

Version appears in three places and must be kept in sync: plugin header and `ENV_CONTROL_VERSION` in `env-control.php`, and `Stable tag` in `readme.txt` (WordPress.org format). `README.md` (GitHub) and `RELEASE_NOTES.md` also describe features/requirements — update them when behavior or requirements change. Releases are tagged `vX.Y.Z`.
