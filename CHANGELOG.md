# Changelog

## 5.3.3 - 2026-11-25

### Changed
- Browser script: the `User-Agent` header is kept in the request data (the other headers and the cookies are still
  removed), so Sentry shows the browser and the operating system of each error (issue #43).

## 5.3.2 - 2026-11-24

### Added
- Browser SDK from environment variables: `SENTRY_JS_DSN` and `SENTRY_JS_SRC` (bundle or Loader URL, which may be
  served by the site), plus `SENTRY_JS_SAMPLE_RATE`; `SENTRY_ENVIRONMENT`, `SENTRY_RELEASE` and `SENTRY_SEND_USER_ID`
  are shared with PHP. Added to the page head by the `before_standard_head_html_generation` hook; when set, the
  `javascriptloader` setting is not used (issue #41).
- Documentation: "Browser SDK from environment variables" section in the configuration page.

### Changed
- Browser script (both modes): sensitive URL parameters are filtered in events and breadcrumbs, cookies and headers are
  removed, personal data stays off, and default `ignoreErrors`/`denyUrls` drop network failures, extensions, tracking
  scripts and webviews (issue #41).

## 5.3.1 - 2026-11-24

### Changed
- Browser errors: the `beforeSend` of the injected `Sentry.init()` removes the JavaScript/theme revision from the
  stack frame URLs (`lib/requirejs.php/<rev>/`, `lib/javascript.php/<rev>/`, `r.php/core/esm/<rev>/`,
  `theme/yui_combo.php?rollup/<rev>/`) and from the error message. The same error no longer opens a new Sentry issue
  after every purge of caches or upgrade (issue #39).
- PHP errors: session lock timeouts (`sessioncannotobtainlock`, `sessionwaiterr`), whose message has the session id, get
  one fingerprint (exception class and error code) in the prepend and plugin modes (issue #39).

### Added
- Documentation: "Issue grouping" page, with recommended Sentry fingerprinting rules for Moodle.
- PHPUnit tests for the browser code and the session lock fingerprint.

## 5.3.0 - 2026-11-19

New version line for **Moodle 5.3** only (`requires` 5.3, `supported` [503, 503], PHP 8.3+). Sites on Moodle 4.x keep
using the 1.0.x line (branch `TOOL_SENTRY_1_0`).

### Added
- Prepend mode: `admin/tool/sentry/prepend.php` (`public/admin/tool/sentry/prepend.php` in the Moodle tree), set in
  `auto_prepend_file`, starts Sentry before Moodle with the options of the `SENTRY_*` environment variables
  (`SENTRY_DSN`, `SENTRY_ENVIRONMENT`, `SENTRY_RELEASE`, `SENTRY_SAMPLE_RATE`, `SENTRY_IGNORE_EXCEPTIONS`,
  `SENTRY_SEND_USER_ID`). Errors that happen before the database and before the first Moodle event are now sent too.
- In prepend mode the plugin does not start the SDK again and chains Sentry's handlers after `lib/setup.php`
  (`core\hook\after_config`).
- Notice on the settings page when Sentry was started by the prepend file.
- PHPUnit tests for the choice of the mode.
- Automatic release on `vX.Y.Z` tags: GitHub Release with the ZIP and upload to the Moodle Marketplace.

### Changed
- Sentry PHP SDK 4.27.0 → 4.38.0. `composer.json` sets `config.platform.php` to 8.3.0, the minimum of Moodle 5.3.
- CI: Moodle 5.3 with PHP 8.3 and 8.4, PostgreSQL 17 and MariaDB 11.4.

### Unchanged
- Without `auto_prepend_file` pointing to the plugin or without `SENTRY_DSN`, the plugin works as before, with the
  settings saved in the administration pages.

## 1.0.3 - 2026-06-06

- Fix the origin of errors sent to Sentry and add the context of scheduled and adhoc tasks.
- Avoid the PHP 8.4 `E_STRICT` deprecation.
