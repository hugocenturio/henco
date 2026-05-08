<?php

namespace Tests\Unit;

use App\Core\Env;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = tempnam(sys_get_temp_dir(), 'henco-env');
    }

    protected function tearDown(): void
    {
        @unlink($this->tmp);
        foreach (['HENCO_ENV_PLAIN', 'HENCO_ENV_QUOTED', 'HENCO_ENV_BOOL', 'HENCO_ENV_BLANK'] as $k) {
            putenv($k);
            unset($_ENV[$k], $_SERVER[$k]);
        }
    }

    public function testLoadsPlainAndQuotedValues(): void
    {
        file_put_contents($this->tmp,
            "# comment line\n"
            . "HENCO_ENV_PLAIN=hello\n"
            . "HENCO_ENV_QUOTED=\"with spaces\"\n"
            . "HENCO_ENV_BLANK=\n"
        );
        Env::load($this->tmp);

        self::assertSame('hello', getenv('HENCO_ENV_PLAIN'));
        self::assertSame('with spaces', getenv('HENCO_ENV_QUOTED'));
        self::assertSame('', getenv('HENCO_ENV_BLANK'));
    }

    public function testGetCoercesBooleanLiterals(): void
    {
        putenv('HENCO_ENV_BOOL=true');
        self::assertTrue(Env::get('HENCO_ENV_BOOL'));

        putenv('HENCO_ENV_BOOL=false');
        self::assertFalse(Env::get('HENCO_ENV_BOOL'));
    }

    public function testGetReturnsDefaultWhenMissing(): void
    {
        self::assertSame('fallback', Env::get('NEVER_SET_HENCO_KEY', 'fallback'));
    }

    public function testDoesNotOverrideExistingEnv(): void
    {
        putenv('HENCO_ENV_PLAIN=preset');
        file_put_contents($this->tmp, "HENCO_ENV_PLAIN=fromfile\n");
        Env::load($this->tmp);

        self::assertSame('preset', getenv('HENCO_ENV_PLAIN'));
    }

    public function testMissingFileIsHandledSilently(): void
    {
        Env::load('/definitely/not/a/path/.env');
        self::assertTrue(true); // no exception
    }
}
