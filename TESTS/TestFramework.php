<?php
/**
 * BeCoffee Test Suite Framework
 * Native PHP test runner with cURL session client, assertions, and dual CLI/HTML reporters.
 */

class TestFramework {
    private static array $suites = [];
    private static array $results = [];
    private static float $startTime = 0;

    public static function registerSuite(string $name, callable $callable): void {
        self::$suites[$name] = $callable;
    }

    public static function runAll(): void {
        self::$startTime = microtime(true);
        self::$results = [];

        foreach (self::$suites as $name => $suite) {
            $suiteResult = [
                'name' => $name,
                'tests' => [],
                'passed' => 0,
                'failed' => 0,
                'duration' => 0
            ];

            $suiteStart = microtime(true);
            $reporter = new SuiteReporter($name);

            try {
                $suite($reporter);
            } catch (Throwable $e) {
                $reporter->fail('Suite Exception', 'Uncaught exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            }

            $suiteResult['tests'] = $reporter->getTests();
            $suiteResult['passed'] = $reporter->getPassedCount();
            $suiteResult['failed'] = $reporter->getFailedCount();
            $suiteResult['duration'] = round((microtime(true) - $suiteStart) * 1000, 2);

            self::$results[] = $suiteResult;
        }

        self::renderOutput();
    }

    private static function renderOutput(): void {
        $totalPassed = 0;
        $totalFailed = 0;
        $totalTests = 0;
        $totalDuration = round((microtime(true) - self::$startTime) * 1000, 2);

        foreach (self::$results as $res) {
            $totalPassed += $res['passed'];
            $totalFailed += $res['failed'];
            $totalTests += count($res['tests']);
        }

        if (php_sapi_name() === 'cli') {
            self::renderCli($totalTests, $totalPassed, $totalFailed, $totalDuration);
        } else {
            self::renderHtml($totalTests, $totalPassed, $totalFailed, $totalDuration);
        }
    }

    private static function renderCli(int $total, int $passed, int $failed, float $duration): void {
        $green = "\033[32m";
        $red = "\033[31m";
        $yellow = "\033[33m";
        $cyan = "\033[36m";
        $bold = "\033[1m";
        $reset = "\033[0m";

        echo "\n" . $bold . $cyan . "=======================================================" . $reset . "\n";
        echo $bold . $cyan . "       BECOFFEE BACKEND INTEGRATION TEST RUNNER         " . $reset . "\n";
        echo $bold . $cyan . "=======================================================" . $reset . "\n\n";

        foreach (self::$results as $suite) {
            $suiteStatus = ($suite['failed'] === 0) ? "{$green}PASS{$reset}" : "{$red}FAIL{$reset}";
            echo "{$bold}[{$suiteStatus}{$bold}] {$suite['name']}{$reset} ({$suite['duration']} ms)\n";

            foreach ($suite['tests'] as $test) {
                if ($test['passed']) {
                    echo "  {$green}✓{$reset} {$test['name']}\n";
                } else {
                    echo "  {$red}✗ {$test['name']}{$reset}\n";
                    echo "    {$yellow}↳ Error: {$test['error']}{$reset}\n";
                }
            }
            echo "\n";
        }

        echo $bold . "-------------------------------------------------------" . $reset . "\n";
        echo "Total Suites:  " . count(self::$results) . "\n";
        echo "Total Tests:   {$total}\n";
        echo "Passed:        " . ($passed > 0 ? "{$green}{$passed}{$reset}" : "0") . "\n";
        echo "Failed:        " . ($failed > 0 ? "{$red}{$failed}{$reset}" : "0") . "\n";
        echo "Execution Time: {$duration} ms\n";
        echo $bold . "-------------------------------------------------------" . $reset . "\n\n";

        if ($failed > 0) {
            exit(1);
        }
    }

    private static function renderHtml(int $total, int $passed, int $failed, float $duration): void {
        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>BeCoffee — Backend Test Suite</title>
          <style>
            :root {
              --bg: #0F172A;
              --card: #1E293B;
              --border: #334155;
              --text: #F8FAFC;
              --muted: #94A3B8;
              --pass: #10B981;
              --fail: #EF4444;
              --accent: #E28743;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
            body { background: var(--bg); color: var(--text); padding: 2rem 1rem; line-height: 1.5; }
            .container { max-width: 960px; margin: 0 auto; }
            header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem; }
            h1 { font-size: 1.5rem; color: var(--text); display: flex; align-items: center; gap: 0.5rem; }
            .badge { padding: 0.25rem 0.6rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
            .badge-pass { background: rgba(16, 185, 129, 0.2); color: var(--pass); border: 1px solid var(--pass); }
            .badge-fail { background: rgba(239, 68, 68, 0.2); color: var(--fail); border: 1px solid var(--fail); }
            .stats-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
            .stat-card { background: var(--card); border: 1px solid var(--border); padding: 1rem; border-radius: 8px; text-align: center; }
            .stat-value { font-size: 1.75rem; font-weight: 700; }
            .stat-label { font-size: 0.8rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; }
            .suite-card { background: var(--card); border: 1px solid var(--border); border-radius: 8px; margin-bottom: 1.25rem; overflow: hidden; }
            .suite-header { padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); border-bottom: 1px solid var(--border); cursor: pointer; }
            .suite-title { font-weight: 600; font-size: 1.05rem; }
            .test-list { list-style: none; }
            .test-item { padding: 0.75rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; }
            .test-item:last-child { border-bottom: none; }
            .test-name { display: flex; align-items: center; gap: 0.5rem; }
            .test-error { background: rgba(239, 68, 68, 0.1); color: #FCA5A5; padding: 0.5rem 0.75rem; border-radius: 6px; font-family: monospace; font-size: 0.8rem; margin-top: 0.4rem; }
            .btn-refresh { background: var(--accent); color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; }
          </style>
        </head>
        <body>
          <div class="container">
            <header>
              <div>
                <h1>☕ BeCoffee Backend Test Suite</h1>
                <p style="color: var(--muted); font-size: 0.85rem; margin-top: 0.25rem;">Automated Integration &amp; Role-Based Routing Audits</p>
              </div>
              <a href="run_all_tests.php" class="btn-refresh">↻ Re-run All Tests</a>
            </header>

            <div class="stats-bar">
              <div class="stat-card">
                <div class="stat-value" style="color: <?= $failed === 0 ? 'var(--pass)' : 'var(--fail)' ?>"><?= $failed === 0 ? 'PASS' : 'FAIL' ?></div>
                <div class="stat-label">Status</div>
              </div>
              <div class="stat-card">
                <div class="stat-value"><?= $total ?></div>
                <div class="stat-label">Total Tests</div>
              </div>
              <div class="stat-card">
                <div class="stat-value" style="color: var(--pass)"><?= $passed ?></div>
                <div class="stat-label">Passed</div>
              </div>
              <div class="stat-card">
                <div class="stat-value" style="color: var(--fail)"><?= $failed ?></div>
                <div class="stat-label">Failed</div>
              </div>
              <div class="stat-card">
                <div class="stat-value"><?= $duration ?> ms</div>
                <div class="stat-label">Duration</div>
              </div>
            </div>

            <?php foreach (self::$results as $suite): ?>
              <div class="suite-card">
                <div class="suite-header">
                  <div class="suite-title"><?= htmlspecialchars($suite['name']) ?></div>
                  <div>
                    <span class="badge <?= $suite['failed'] === 0 ? 'badge-pass' : 'badge-fail' ?>">
                      <?= $suite['passed'] ?> / <?= count($suite['tests']) ?> Passed (<?= $suite['duration'] ?> ms)
                    </span>
                  </div>
                </div>
                <ul class="test-list">
                  <?php foreach ($suite['tests'] as $test): ?>
                    <li class="test-item">
                      <div style="flex: 1;">
                        <div class="test-name">
                          <span><?= $test['passed'] ? '✅' : '❌' ?></span>
                          <span style="<?= $test['passed'] ? '' : 'color: #FCA5A5; font-weight: 600;' ?>">
                            <?= htmlspecialchars($test['name']) ?>
                          </span>
                        </div>
                        <?php if (!$test['passed']): ?>
                          <div class="test-error"><?= htmlspecialchars($test['error']) ?></div>
                        <?php endif; ?>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
          </div>
        </body>
        </html>
        <?php
    }
}

class SuiteReporter {
    private string $suiteName;
    private array $tests = [];
    private int $passed = 0;
    private int $failed = 0;

    public function __construct(string $suiteName) {
        $this->suiteName = $suiteName;
    }

    public function assert(string $testName, bool $condition, string $failureMessage = ''): void {
        if ($condition) {
            $this->passed++;
            $this->tests[] = ['name' => $testName, 'passed' => true, 'error' => null];
        } else {
            $this->failed++;
            $this->tests[] = ['name' => $testName, 'passed' => false, 'error' => $failureMessage ?: 'Assertion failed'];
        }
    }

    public function assertEquals(string $testName, $expected, $actual): void {
        $passed = ($expected === $actual);
        $error = $passed ? null : "Expected: " . json_encode($expected) . ", Got: " . json_encode($actual);
        $this->assert($testName, $passed, $error ?: '');
    }

    public function pass(string $testName): void {
        $this->assert($testName, true);
    }

    public function fail(string $testName, string $error): void {
        $this->assert($testName, false, $error);
    }

    public function assertTrue(string $testName, bool $condition, string $failureMessage = ''): void {
        $this->assert($testName, $condition === true, $failureMessage ?: 'Expected true');
    }

    public function assertFalse(string $testName, bool $condition, string $failureMessage = ''): void {
        $this->assert($testName, $condition === false, $failureMessage ?: 'Expected false');
    }

    public function assertContains(string $testName, string $needle, string $haystack): void {
        $this->assert($testName, strpos($haystack, $needle) !== false, "String does not contain '{$needle}'");
    }

    public function info(string $msg): void {
        // Log info or stage separator
    }

    public function getTests(): array { return $this->tests; }
    public function getPassedCount(): int { return $this->passed; }
    public function getFailedCount(): int { return $this->failed; }
}

class TestClient {
    private string $baseUrl;
    private string $cookieFile;

    public function __construct(string $baseUrl = '') {
        $defaultUrl = getenv('TEST_BASE_URL') ?: 'http://localhost/BeCoffee/';
        $this->baseUrl = rtrim(!empty($baseUrl) ? $baseUrl : $defaultUrl, '/') . '/';
        $scratchDir = dirname(__DIR__) . '/scratch';
        if (!is_dir($scratchDir)) {
            @mkdir($scratchDir, 0777, true);
        }
        $this->cookieFile = $scratchDir . '/test_cookie_' . uniqid() . '.txt';
    }

    public function __destruct() {
        if (file_exists($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    public function loginAs(string $role): bool {
        $credentials = [
            'superadmin' => ['email' => 'superadmin', 'password' => 'superadmin123'],
            'admin'      => ['email' => 'admin',      'password' => 'admin123'],
            'staff'      => ['email' => 'staff',      'password' => 'staff123'],
            'customer'   => ['email' => 'customer@becoffee.ph', 'password' => 'customer123']
        ];

        if (!isset($credentials[$role])) {
            return false;
        }

        $res = $this->post('api/auth.php?action=login', $credentials[$role]);
        return ($res['status'] === 200 && !empty($res['json']['success']));
    }

    public function logout(): void {
        $this->post('api/auth.php?action=logout', []);
        if (file_exists($this->cookieFile)) {
            @unlink($this->cookieFile);
        }
    }

    public function get(string $path, array $queryParams = [], bool $followRedirects = false): array {
        $url = $this->baseUrl . ltrim($path, '/');
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        return $this->request('GET', $url, null, $followRedirects);
    }

    public function post(string $path, $data = null, bool $asJson = true, bool $followRedirects = false): array {
        $url = $this->baseUrl . ltrim($path, '/');
        return $this->request('POST', $url, $data, $followRedirects, $asJson);
    }

    private function request(string $method, string $url, $data = null, bool $followRedirects = false, bool $asJson = true): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followRedirects);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $headers = [];
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($asJson && is_array($data)) {
                $payload = json_encode($data);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                $headers[] = 'Content-Type: application/json';
                $headers[] = 'Content-Length: ' . strlen($payload);
            } elseif (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

        $rawHeaders = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        $json = json_decode($body, true);

        // Parse Location header if present
        $location = null;
        if (preg_match('/^Location:\s*(.+)$/mi', $rawHeaders, $matches)) {
            $location = trim($matches[1]);
        }

        return [
            'status'      => $httpCode,
            'body'        => $body,
            'json'        => $json,
            'location'    => $location ?: $redirectUrl,
            'raw_headers' => $rawHeaders
        ];
    }
}
