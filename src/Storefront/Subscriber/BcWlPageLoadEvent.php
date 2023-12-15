<?php
/**
 * For getting core configuration in twig file by extension
 *
 * Copyright (C) BrandCrock GmbH. All rights reserved.
 *
 * If you have found this script useful a small
 * recommendation as well as a comment on our
 * home page(https://brandcrock.com/)
 * would be greatly appreciated.
 *
 * @author  BrandCrock GmbH
 * @package BrandCrockWanderlust
 * @support support@brandcrock.com
 *
 * License proprietary.
 */
declare(strict_types=1);

namespace Bc\BrandCrockWanderlust\Storefront\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Storefront\Page\GenericPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Twig extension relate to PHP code and used by the profiler and the default exception templates.
 */
class BcWlPageLoadEvent implements EventSubscriberInterface
{
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;
    
    public function __construct(
        SystemConfigService $systemConfigService
    ) {
        $this->systemConfigService = $systemConfigService;
    }

	public static function getSubscribedEvents(): array
    {
        return [
            GenericPageLoadedEvent::class => 'pageLoadEvent',
        ];
    }

	/**
	 * For getting shop core configurations
	 * @param string $name
	 * @param string $salesChannelId
	 * @return string
	 */
	private function getBcWlCoreConfig(string $name, string $salesChannelId)
    {
        return $this->systemConfigService->get($name, $salesChannelId, true);
    }

	/**
	 * For getting shop core configurations
	 * @param PageLoadedEvent $event
	 * @return void
	 */
	public function pageLoadEvent(PageLoadedEvent $event): void
    {
		$extension = $event->getPage()->getExtension('BrandCrockWanderlust');
		if(empty($extension))
		{
			$salesChannel = $event->getSalesChannelContext()->getSalesChannel();
			$previousData = $event->getPage()->getExtensions();
			$data = ['BrandCrockWanderlust' => new ArrayStruct([
				'bcWlShopName' => $this->getBcWlCoreConfig('core.basicInformation.shopName', $salesChannel->getId()),
				'bcWlShowReview' => $this->getBcWlCoreConfig('core.listing.showReview', $salesChannel->getId()),
				'bcWlNewsletterPage' => $this->getBcWlCoreConfig('core.basicInformation.newsletterPage', $salesChannel->getId()),
				'bcWlDoubleOptIn' => $this->getBcWlCoreConfig('core.newsletter.doubleOptIn', $salesChannel->getId()),
				'bcWlWishlistEnabled' => $this->getBcWlCoreConfig('core.cart.wishlistEnabled', $salesChannel->getId())
			])];
			$data = array_merge($data, $previousData);
			$event->getPage()->setExtensions($data);
		}
    }
}
