<?php

namespace Tests\Unit;

use App\Core\Router;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RouterTest extends TestCase
{
    public function testStaticPathCompilesToAnchoredRegex(): void
    {
        $regex = $this->compile('/dashboard');
        self::assertSame(1, preg_match($regex, '/dashboard'));
        self::assertSame(1, preg_match($regex, '/dashboard/')); // optional trailing slash
        self::assertSame(0, preg_match($regex, '/dashboard/extra'));
        self::assertSame(0, preg_match($regex, '/other'));
    }

    public function testDynamicSegmentsAreCaptured(): void
    {
        $regex = $this->compile('/orders/{id}');
        self::assertSame(1, preg_match($regex, '/orders/42', $m));
        self::assertSame('42', $m[1]);
        self::assertSame(0, preg_match($regex, '/orders/'));
        self::assertSame(0, preg_match($regex, '/orders/42/items')); // no nested
    }

    public function testParamNamesAreExtractedInOrder(): void
    {
        $names = $this->paramNames('/clients/{client_id}/orders/{order_id}');
        self::assertSame(['client_id', 'order_id'], $names);
    }

    public function testMultipleRoutesCanBeRegisteredWithoutConflict(): void
    {
        $r = new Router();
        $r->get('/a',          'A@get');
        $r->post('/a',         'A@post');
        $r->any('/b/{id}',     'B@any');

        $ref = new ReflectionClass($r);
        $prop = $ref->getProperty('routes');
        $prop->setAccessible(true);
        $routes = $prop->getValue($r);

        self::assertCount(3, $routes);
        self::assertSame('GET',  $routes[0]['method']);
        self::assertSame('POST', $routes[1]['method']);
        self::assertSame('ANY',  $routes[2]['method']);
        self::assertSame(['id'], $routes[2]['params']);
    }

    private function compile(string $path): string
    {
        $r = new Router();
        $ref = new ReflectionClass($r);
        $m = $ref->getMethod('compile');
        $m->setAccessible(true);
        return $m->invoke($r, $path);
    }

    /**
     * @return list<string>
     */
    private function paramNames(string $path): array
    {
        $r = new Router();
        $ref = new ReflectionClass($r);
        $m = $ref->getMethod('paramNames');
        $m->setAccessible(true);
        return $m->invoke($r, $path);
    }
}
