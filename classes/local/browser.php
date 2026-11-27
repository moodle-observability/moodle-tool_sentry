<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace tool_sentry\local;

/**
 * Browser SDK configured by environment variables (no settings in the database).
 *
 * Variables:
 *  - SENTRY_JS_DSN: DSN of the browser project. Without it, this class does nothing.
 *  - SENTRY_JS_SRC: URL of the SDK bundle (bundle.min.js) or of a JavaScript Loader. It may be a file served by the site
 *    itself, so that no request goes to the Sentry CDN.
 *  - SENTRY_JS_SAMPLE_RATE: 0.0 to 1.0 (default 1.0).
 *  - SENTRY_ENVIRONMENT, SENTRY_RELEASE and SENTRY_SEND_USER_ID: the same used by the PHP SDK (prepend mode).
 *
 * @package    tool_sentry
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class browser {
    /**
     * Options of the browser SDK read from the environment.
     *
     * @return array|null ['src' => string, 'options' => array, 'userid' => int], or null when not configured.
     */
    public static function env_options(): ?array {
        global $CFG, $USER;

        $dsn = prepend::env('SENTRY_JS_DSN');
        $src = prepend::env('SENTRY_JS_SRC');
        if ($dsn === '' || $src === '') {
            return null;
        }
        $options = [
            'dsn' => $dsn,
            'environment' => prepend::env('SENTRY_ENVIRONMENT', 'production'),
            'sampleRate' => (float) prepend::env('SENTRY_JS_SAMPLE_RATE', '1.0'),
            'allowUrls' => [rtrim($CFG->wwwroot, '/')],
            'sendDefaultPii' => false,
        ];
        $release = prepend::env('SENTRY_RELEASE');
        if ($release !== '') {
            $options['release'] = $release;
        }
        // Trace id of the request that served the page: JavaScript errors of the page can be found with it.
        $traceid = prepend::trace_id($_SERVER);
        if ($traceid !== '') {
            $options['initialScope'] = ['tags' => ['trace_id' => $traceid]];
        }
        $userid = 0;
        if (prepend::env('SENTRY_SEND_USER_ID') === 'true' && isloggedin() && !isguestuser()) {
            $userid = (int) $USER->id;
        }
        return ['src' => $src, 'options' => $options, 'userid' => $userid];
    }

    /**
     * HTML for the page head: the script that loads and starts the browser SDK.
     *
     * @return string Empty when the browser SDK is not configured by environment variables.
     */
    public static function head_html(): string {
        $config = self::env_options();
        if ($config === null) {
            return '';
        }
        return \html_writer::script(
            \tool_sentry\helper::get_js_init_code($config['src'], $config['options'], $config['userid'])
        );
    }
}
