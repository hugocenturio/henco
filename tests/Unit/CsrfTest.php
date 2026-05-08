<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsGeneratedOnFirstCallAndStable(): void
    {
        $a = csrf_token();
        $b = csrf_token();
        self::assertNotEmpty($a);
        self::assertSame(64, strlen($a)); // 32 random bytes -> 64 hex chars
        self::assertSame($a, $b);
        self::assertSame($a, $_SESSION['csrf_token']);
    }

    public function testFieldEmitsHiddenInputWithEscapedToken(): void
    {
        $_SESSION['csrf_token'] = 'abc123';
        $html = csrf_field();
        self::assertStringContainsString('type="hidden"', $html);
        self::assertStringContainsString('name="csrf_token"', $html);
        self::assertStringContainsString('value="abc123"', $html);
    }

    public function testValidTokenPassesVerify(): void
    {
        $_SESSION['csrf_token'] = 'token-123';
        $_POST['csrf_token']    = 'token-123';

        // No exception, no exit() — verify is a no-op when token matches.
        csrf_verify();
        self::assertSame('token-123', $_SESSION['csrf_token']);
    }
}
