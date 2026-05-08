<?php

namespace Tests\Unit;

use App\Core\Request;
use PHPUnit\Framework\TestCase;

final class RequestTest extends TestCase
{
    private array $serverBackup;
    private array $getBackup;
    private array $postBackup;
    private array $filesBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->getBackup    = $_GET;
        $this->postBackup   = $_POST;
        $this->filesBackup  = $_FILES;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        $_GET    = $this->getBackup;
        $_POST   = $this->postBackup;
        $_FILES  = $this->filesBackup;
    }

    public function testParsesMethodPathAndQueryAtRoot(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI']    = '/dashboard?x=1';
        $_SERVER['SCRIPT_NAME']    = '/index.php';
        $_GET  = ['x' => '1'];
        $_POST = ['name' => 'Hugo'];

        $r = new Request();
        self::assertSame('POST', $r->method);
        self::assertSame('/dashboard', $r->path);
        self::assertSame(['x' => '1'], $r->query);
        self::assertSame('Hugo', $r->input('name'));
        self::assertTrue($r->isPost());
    }

    public function testStripsBasePathWhenAppLivesInSubfolder(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/henco/products?cat=2';
        $_SERVER['SCRIPT_NAME']    = '/henco/index.php';

        $r = new Request();
        self::assertSame('/products', $r->path);
    }

    public function testInputFallsBackToQueryThenDefault(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/?id=42';
        $_SERVER['SCRIPT_NAME']    = '/index.php';
        $_GET  = ['id' => '42'];
        $_POST = [];

        $r = new Request();
        self::assertSame('42', $r->input('id'));
        self::assertSame('default', $r->input('missing', 'default'));
    }

    public function testDetectBasePathReturnsEmptyStringForRoot(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        self::assertSame('', Request::detectBasePath());
    }

    public function testDetectBasePathReturnsSubfolder(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/henco/index.php';
        self::assertSame('/henco', Request::detectBasePath());
    }
}
