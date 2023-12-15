<?php
/**
 * For getting plugin and theme configuration in twig file
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

namespace Bc\BrandCrockWanderlust\Storefront\TwigFilter;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * Twig extension relate to PHP code and used by the profiler and the default exception templates.
 */
class BcWlFilter extends AbstractExtension
{
    /**
     * @var SystemConfigService
     * @var ContainerInterface
     */
    private $containerinterface;
    private $systemConfigService;

    public function __construct(
		SystemConfigService $systemConfigService,
		ContainerInterface $containerinterface
    ) {
		$this->containerinterface = $containerinterface;
		$this->systemConfigService = $systemConfigService;
    }

	/**
	 * Used to get theme and plugin configurations, and add the configurations into twig filter.
	 */
    public function getFilters()
    {
        return [
           new TwigFilter('getWlPluginConfig', [$this, 'getBcWlPluginConfig']),
           new TwigFilter('getWlThemeSettings', [$this, 'getBcWlThemeSettings']),
        ];
    }

   /**
     * For getting plugin configurations
     * @param string $salesChannelId
     * @return array
     */
	public function getBcWlPluginConfig(string $salesChannelId): array
    {
        return $this->systemConfigService->get(
            'BrandCrockWanderlust.config',
            $salesChannelId,
            true
        );
    }

	/**
     * For getting theme configurations
     * @param string $salesChannelId
     * @return array
     */
	public function getBcWlThemeSettings(string $salesChannelId)
    {
		$themeId = "";
		$criteriaSalesChannel = new Criteria();
		$criteriaSalesChannel->addFilter(new EqualsFilter('salesChannels.id', $salesChannelId));
		$currentThemeRep = $this->containerinterface->get('theme.repository')->search($criteriaSalesChannel, Context::createDefaultContext());
		if (!is_null($currentThemeRep)) {
			$currentThemeId = $currentThemeRep->first();
			$themeId = $currentThemeId ? Uuid::fromHexToBytes($currentThemeId->getId()) : null;
		}
        if(!empty($themeId)) {
			$themeId = Uuid::fromBytestoHex($themeId);
		}
		$criteria = new Criteria();
		$criteria->addFilter(new EqualsFilter('technicalName', 'BrandCrockWanderlust'));
		$themeSettings = $this->containerinterface->get('theme.repository')->search($criteria, Context::createDefaultContext())->first();
        $childThemeSettings = '';
        if($themeId)
        {
			$criteria = new Criteria([$themeId]);
			$childThemeSettings = $this->containerinterface->get('theme.repository')->search($criteria, Context::createDefaultContext())->first();
		}
		$baseConfig = (!empty($childThemeSettings) && $childThemeSettings->getBaseConfig()) ? $childThemeSettings->getBaseConfig()['fields'] : $themeSettings->getBaseConfig()['fields'];
		$configValue = (!empty($childThemeSettings) && $childThemeSettings->getConfigValues()) ? $childThemeSettings->getConfigValues() : $themeSettings->getConfigValues();
		if(!empty($baseConfig) && !empty($configValue))
		{
			foreach ($configValue as $key => $value)
			{
				$baseConfig[$key]['value'] = $value['value'];
			}
		}
		else if(!empty($configValue)) {
			$baseConfig = $configValue;
		}
		return $baseConfig;
    }
}
