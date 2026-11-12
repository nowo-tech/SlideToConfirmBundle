<?php

declare(strict_types=1);

namespace Nowo\SlideToConfirmBundle\Tests\Integration\Worker;

use Nowo\SlideToConfirmBundle\Tests\Fixtures\Worker\WorkerHostKernel;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\TerminableInterface;

use function gc_collect_cycles;
use function memory_get_usage;
use function sprintf;

/**
 * REQ-RUNTIME-002: ONE kept-alive kernel (FrankenPHP worker mode, kernel reset false). The `services_resetter` and
 * `kernel.reset` are never called. A host form with a `SlideToConfirmType` field is rendered and submitted through
 * `http_kernel` (handle + terminate) N times, alternating two inputs with different options (text, variant, threshold, CSS class), locales and outcomes (confirmed / not confirmed).
 * Each response must contain only its own data and memory must stay flat.
 */
#[CoversNothing]
final class WorkerKeptAliveKernelTest extends TestCase
{
    private const ITERATIONS = 1000;

    private const WARM_UP = 100;

    private const MEMORY_MARGIN_BYTES = 4 * 1024 * 1024;

    public function testFormRenderAndSubmitInOneKernelWithoutResetCarriesNoStateAndKeepsMemoryFlat(): void
    {
        $kernel = new WorkerHostKernel();
        $kernel->boot();
        $http = $kernel->getContainer()->get('http_kernel');
        self::assertInstanceOf(HttpKernelInterface::class, $http);
        self::assertInstanceOf(TerminableInterface::class, $http);

        $inputs = [
            'alpha' => ['locale' => 'en', 'confirm' => true, 'text' => 'Slide alpha', 'variant' => 'danger', 'threshold' => '0.8', 'box' => 'alpha-box', 'state' => 'valid'],
            'beta'  => ['locale' => 'es', 'confirm' => false, 'text' => 'Slide beta', 'variant' => 'success', 'threshold' => '0.9', 'box' => 'beta-box', 'state' => 'invalid'],
        ];

        $baseline = 0;
        for ($i = 0; $i < self::ITERATIONS; ++$i) {
            if ($i === self::WARM_UP) {
                gc_collect_cycles();
                $baseline = memory_get_usage();
            }

            $who   = $i % 2 === 0 ? 'alpha' : 'beta';
            $other = $who === 'alpha' ? 'beta' : 'alpha';
            $in    = $inputs[$who];

            // Render (GET) and submit (POST) the same host form.
            foreach ([Request::METHOD_GET, Request::METHOD_POST] as $method) {
                $request = Request::create('/host/' . $who, $method, $method === Request::METHOD_POST ? ($in['confirm'] ? ['f' => ['ok' => '1']] : ['f' => []]) : []);
                $request->setLocale($in['locale']);
                $response = $http->handle($request);
                $body     = (string) $response->getContent();
                $http->terminate($request, $response);

                $ctx = sprintf('iteration %d, %s %s', $i, $who, $method);
                self::assertSame(Response::HTTP_OK, $response->getStatusCode(), $ctx);
                self::assertStringContainsString('who=' . $who . ';', $body, $ctx);
                self::assertStringNotContainsString('who=' . $other . ';', $body, $ctx);
                foreach (['text', 'variant', 'threshold', 'box'] as $key) {
                    self::assertStringContainsString($in[$key], $body, $ctx);
                    self::assertStringNotContainsString($inputs[$other][$key], $body, $ctx);
                }
                // Alpha submits the confirmation (valid), beta never does (invalid): the verdict never crosses over.
                self::assertStringContainsString('submitted=[' . ($method === Request::METHOD_POST ? $in['state'] : 'none') . '];', $body, $ctx);
                self::assertSame($method === Request::METHOD_POST && $in['confirm'] ? 1 : 0, substr_count($body, 'checked="checked"'), $ctx);
            }
        }

        gc_collect_cycles();
        $growth = memory_get_usage() - $baseline;
        self::assertLessThan(self::MEMORY_MARGIN_BYTES, $growth, sprintf('memory grew by %d bytes over %d iterations', $growth, self::ITERATIONS - self::WARM_UP));

        $kernel->shutdown();
    }
}
