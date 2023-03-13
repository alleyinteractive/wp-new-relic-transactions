# WP New Relic Transactions

Contributors: mboynes

Tags: alleyinteractive, wp-new-relic-transactions

Stable tag: 0.1.0

Requires at least: 5.9

Tested up to: 6.1

Requires PHP: 8.0

License: GPL v2 or later

[![Coding Standards](https://github.com/alleyinteractive/wp-new-relic-transactions/actions/workflows/coding-standards.yml/badge.svg)](https://github.com/alleyinteractive/wp-new-relic-transactions/actions/workflows/coding-standards.yml)
[![Testing Suite](https://github.com/alleyinteractive/wp-new-relic-transactions/actions/workflows/unit-test.yml/badge.svg)](https://github.com/alleyinteractive/wp-new-relic-transactions/actions/workflows/unit-test.yml)

A companion plugin when using New Relic with WordPress, to improve the recorded transaction data.

## Installation

You can install the package via composer:

```bash
composer require alleyinteractive/wp-new-relic-transactions
```

## Usage

Simply activate the plugin in WordPress, no further action is necessary.

## Transaction Names and Params

This plugin will name the requests, and apply the given custom attributes/parameters:

| Request type            | Transaction Name                                                  |
|-------------------------|-------------------------------------------------------------------|
| homepage and front page | `homepage`                                                        |
| feed                    | `feed` or `feed.{feed type}`                                      |
| embed                   | `embed`<br/>Param: `embed`                                        |
| 404                     | `error.404`                                                       |
| search                  | `search`<br/>Param: `s`                                           |
| privacy policy          | `privacy_policy`                                                  |
| post type archive       | `archive.post_type.{post type}`                                   |
| taxonomy archive        | `taxonomy` or `taxonomy.{taxonomy}`<br/>Params: `term_id`, `slug` |
| attachment              | `attachment`                                                      |
| single post             | `post` or `post.{post type}`<br/>Param: `post_id`                 |
| author archive          | `archive.author`                                                  |
| date archive            | `archive.date`                                                    |
| misc archive            | `archive`                                                         |
| REST API request        | `{VERB} {Route}`<br/>Params: `wp-api = true`, `wp-api-route`      |

### REST API Request Names

REST API requests get a special formatting. When possible, dynamic portions of the
URL are replaced with angle brackets surrounding the name of the parameter. Further,
the HTTP verb is included in the name.

For instance: `GET /wp/v2/posts/\<id\>`

### Additional Custom Attributes

In addition to the above, requests should also include the following attributes/parameters for non-REST requests:

* `logged-in`: Is the user logged in or not
* `paged`: `true` if the request is paged
* `page`: The page number if the request is paged

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

This project is actively maintained by [Alley
Interactive](https://github.com/alleyinteractive). Like what you see? [Come work
with us](https://alley.co/careers/).

- [Matthew Boynes](https://github.com/mboynes)
- [All Contributors](../../contributors)

## License

The GNU General Public License (GPL) license. Please see [License File](LICENSE) for more information.
