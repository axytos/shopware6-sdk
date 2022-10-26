<?php

declare(strict_types=1);

namespace Axytos\Shopware\Tests\Routing;

use Axytos\Shopware\Routing\Router;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\RouterInterface;

class RouterTest extends TestCase
{
    /** @var RouterInterface&MockObject $router */
    private RouterInterface $router;

    private Router $sut;

    public function setUp(): void
    {
        $this->router = $this->createMock(RouterInterface::class);

        $this->sut = new Router(
            $this->router
        );
    }

    public function test_redirectToCheckoutFailedPage_returns_RedirectResponse_with_correct_url(): void
    {
        $url = 'url';

        $this->router
            ->method('generate')
            ->with(Router::CHECKOUT_FAILED_PAGE)
            ->willReturn($url);

        $response = $this->sut->redirectToCheckoutFailedPage();

        $this->assertSame($url, $response->getTargetUrl());
    }

    public function test_redirectToEditOrderPage_returns_RedirectResponse_with_correct_url(): void
    {
        $orderId = 'orderId';
        $url = 'url';

        $this->router
            ->method('generate')
            ->with(Router::EDIT_ORDER_PAGE, [
                'orderId' => $orderId
            ])
            ->willReturn($url);

        $response = $this->sut->redirectToEditOrderPage($orderId);

        $this->assertSame($url, $response->getTargetUrl());
    }

    public function test_redirectToEditOrderPageWithError_returns_RedirectResponse_with_correct_url(): void
    {
        $orderId = 'orderId';
        $url = 'url';

        $this->router
            ->method('generate')
            ->with(Router::EDIT_ORDER_PAGE, [
                'orderId' => $orderId,
                'error-code' => 'AXYTOS-TECHNICAL-ERROR'
            ])
            ->willReturn($url);

        $response = $this->sut->redirectToEditOrderPageWithError($orderId);

        $this->assertSame($url, $response->getTargetUrl());
    }
}
