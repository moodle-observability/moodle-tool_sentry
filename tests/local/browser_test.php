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
 * Tests for the browser SDK configured by environment variables.
 *
 * @package    tool_sentry
 * @category   test
 * @copyright  2026 Kelson da Costa Medeiros
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(browser::class)]
final class browser_test extends \advanced_testcase {
    /** @var string[] Variables changed by the tests. */
    private const VARIABLES = ['SENTRY_JS_DSN', 'SENTRY_JS_SRC', 'SENTRY_JS_SAMPLE_RATE', 'SENTRY_RELEASE', 'SENTRY_SEND_USER_ID'];

    /** @var array Values of the variables before the test. */
    private $old = [];

    protected function setUp(): void {
        parent::setUp();
        foreach (self::VARIABLES as $name) {
            $this->old[$name] = getenv($name);
            putenv($name);
        }
    }

    protected function tearDown(): void {
        foreach ($this->old as $name => $value) {
            putenv($value === false ? $name : "{$name}={$value}");
        }
        parent::tearDown();
    }

    /**
     * Without SENTRY_JS_DSN and SENTRY_JS_SRC nothing is added to the page.
     */
    public function test_not_configured(): void {
        $this->assertNull(browser::env_options());
        $this->assertSame('', browser::head_html());

        putenv('SENTRY_JS_DSN=https://key@sentry.example/2');
        $this->assertNull(browser::env_options(), 'SENTRY_JS_SRC is also required');
    }

    /**
     * The options come from the environment, and the user id only with SENTRY_SEND_USER_ID=true.
     */
    public function test_env_options(): void {
        global $CFG;
        $this->resetAfterTest();
        putenv('SENTRY_JS_DSN=https://key@sentry.example/2');
        putenv('SENTRY_JS_SRC=https://moodle.example/sentry/bundle.min.js');
        putenv('SENTRY_JS_SAMPLE_RATE=0.5');
        putenv('SENTRY_RELEASE=site@1.2.3');
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $config = browser::env_options();
        $this->assertSame('https://moodle.example/sentry/bundle.min.js', $config['src']);
        $this->assertSame('https://key@sentry.example/2', $config['options']['dsn']);
        $this->assertSame(0.5, $config['options']['sampleRate']);
        $this->assertSame('site@1.2.3', $config['options']['release']);
        $this->assertSame([rtrim($CFG->wwwroot, '/')], $config['options']['allowUrls']);
        $this->assertFalse($config['options']['sendDefaultPii']);
        $this->assertSame(0, $config['userid']);

        putenv('SENTRY_SEND_USER_ID=true');
        $this->assertSame((int) $user->id, browser::env_options()['userid']);
    }

    /**
     * The head HTML has the script with the bundle, the filters and the user.
     */
    public function test_head_html(): void {
        putenv('SENTRY_JS_DSN=https://key@sentry.example/2');
        putenv('SENTRY_JS_SRC=https://moodle.example/sentry/bundle.min.js');

        $html = browser::head_html();
        $this->assertStringStartsWith('<script', $html);
        $this->assertStringContainsString('script.src = "https://moodle.example/sentry/bundle.min.js";', $html);
        $this->assertStringContainsString('"dsn":"https://key@sentry.example/2"', $html);
        $this->assertStringContainsString('sesskey', $html);
        $this->assertStringContainsString('delete event.request.cookies;', $html);
        $this->assertStringContainsString('config.beforeBreadcrumb = beforebreadcrumb;', $html);
    }
}
