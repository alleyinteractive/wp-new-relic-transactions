# Changelog

All notable changes to `WP New Relic Transactions` will be documented in this file.

## 0.3.0 - 2026-11-06

- Require PHP 8.3 or later and WordPress 6.5 or later; tested up to WordPress 7.1.
- Fix the `rest_dispatch_request` callback being registered with the wrong argument count.
- Skip non-scalar custom parameters instead of throwing a type error.
- Name post type archives from the queried post type object.

## 0.2.2

- Bump version for plugin updates.

## 0.2.1

- Adjust REST route parameter regex to handle nested parentheses.

## 0.2.0

- Add redirect transaction naming.
- Add cron and wp-cli transaction naming.
- Include WP-CLI command arguments as a custom parameter.

## 0.1.0 - 2023-03-08

- Initial release
