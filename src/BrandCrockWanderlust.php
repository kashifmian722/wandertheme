<?php declare(strict_types = 1);

/**
 * To Install Wanderlust theme and its Shopping experience elements for the layout design.
 *
 * Copyright (C) BrandCrock GmbH. All rights reserved.
 *
 * If you have found this script useful, a small
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

namespace Bc\BrandCrockWanderlust;

use Shopware\Core\Framework\Plugin;
use Shopware\Storefront\Framework\ThemeInterface;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Defaults;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Framework\Context;
use Symfony\Component\Config\FileLocator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class BrandCrockWanderlust extends Plugin implements ThemeInterface
{
	/**
	* @var FileSaver
	*/
    private $fileSaver;
	/**
	* @var Context
	*/
    private $context;

    // Landing page media files name
    private $bc_media_files_ary = [
        'bc_wl_main_banner',
        'bc_wl_category1',
        'bc_wl_category2',
        'bc_wl_category3',
        'bc_wl_side_banner1',
        'bc_wl_promotion_banner',
        'bc_wl_side_banner2'
    ];

    // Theme media files name
    private $bc_theme_media_files_ary = [
        'bc_wl_logo',
        'bc_wl_icon',
        'bc_wl_favicon',
        'bc_wl_app_store',
        'bc_wl_google_play',
        'bc_wl_preview',
        'bc_wl_promotion_img1',
        'bc_wl_promotion_img2',
        'bc_wl_promotion_img3',
        'bc_wl_promotion_img4',
        'bc_wl_spinner',
        'bc_wl_order_now'
    ];

	/**
	* Get the theme config path
	* @return string
	*/
    public function getThemeConfigPath(): string
    {
        return 'theme.json';
    }

    /**
     * Install the theme plugin and create the cms page
     * @param InstallContext $installContext
     * @return void
     */
    public function install(InstallContext $installContext): void
    {
        $this->fileSaver = $this->container->get(FileSaver::class);
        $this->context = Context::createDefaultContext();
        $bcCmsPageId = $this->getBcCmsPageId();
        if ($bcCmsPageId === null) {
            $this->createBcCmsPage();
        }
    }
    
	/**
     * Uninstall the theme plugin and delete the cms page
     * @param UninstallContext $uninstallContext
     * @return void
     */
    public function uninstall(UninstallContext $uninstallContext): void
    {
        $this->removeBcCmsPage();
        if ($uninstallContext->keepUserData()) {
            parent::uninstall($uninstallContext);
            return;
        }

        // Remove plugin configuration
        $this->deleteConfiguration($uninstallContext->getContext(), 'system_config.repository', 'configurationKey', 'BrandCrockWanderlust.config');
    }
    
	/**
     * Get german language id
     * @return string|null $result
     */
    private function getLanguageDeId(): ?string
    {
        $criteria = new Criteria();
		$criteria->addFilter(new EqualsFilter('locale.code', 'de-DE'));
		$result = $this->container->get('language.repository')->searchIds($criteria, Context::createDefaultContext());
        return $result ? $result->firstId() : null;
    }

    /**
     * Create cms page elements and configuration
     * @return void
     */
    private function createBcCmsPage(): void
    {
        $languageDe = $this->getLanguageDeId();
        $cmsFolder = $this->getDefaultFolderIdForEntity();

        // Create dynamic media folders
        $bcMediaFiles = [
            [
                'id' => '2a6ec27b55704a43b1f0966379abc070',
                'mediaFolderId' => $cmsFolder
            ],
            [
                'id' => '7c6250cd229e41bfa73993b11fd8dedc',
                'mediaFolderId' => $cmsFolder
            ],
            [
                'id' => '8faa3d0c8a204149a58ef2613be952b5',
                'mediaFolderId' => $cmsFolder
            ],
            [
                'id' => '68bb9e84e0c04bfaabe296eb07f48d9f',
                'mediaFolderId' => $cmsFolder
            ],
            [
                'id' => '40632390b63f40a798b27fb52f1653e6',
                'mediaFolderId' => $cmsFolder
            ],
            [
                'id' => 'b0c0d82d60904f69aa5204babbb1af66',
                'mediaFolderId' => $cmsFolder
            ],
            [
                'id' => 'c08e710f96f242598267b67e02adcd40',
                'mediaFolderId' => $cmsFolder
            ],
        ];

        // Insert theme media files into the media repository
		$result = $this->container->get('media.repository')->create($bcMediaFiles, $this->context);

        // To map media folder images to the inserted media entries
        if ($result) {
            foreach (glob(__DIR__ . '/Resources/media/*/*.jpg') as $file) {
                $this->fileSaver->persistFileToMedia(
                    new MediaFile(
                        $file,
                        mime_content_type($file),
                        pathinfo($file, PATHINFO_EXTENSION),
                        filesize($file)
                    ),
                    pathinfo($file, PATHINFO_FILENAME),
                    basename(dirname($file)),
                    $this->context
                );
            }
        }

        // Get default demo product ids
        $productIds = $this->getShopProductIds(8);

        // Creating cms page
        $page = [
            'type' => 'landingpage',
            'locked' => 0,
            'translations' => [
                Defaults::LANGUAGE_SYSTEM => ['name' => 'Wanderlust Home Page'],
                $languageDe => ['name' => 'Wanderlust Home Page'],
            ],
            'sections' => [
				[
					'position' => 0,
					'type' => 'default',
					'name' => 'bc-wl-top-banner-block',
					'sizingMode' => 'full_width',
					'cssClass' => 'bc-wl-top-banner',
                    'blocks' => [
						[
							'position' => 0,
							'sectionPosition' => 'main',
							'type' => 'text-on-image',
							'name' => 'bc-wl-top-banner-bk',
							'backgroundMediaId' => $bcMediaFiles[0]['id'],
							'backgroundMediaMode' => 'cover',
							'marginTop' => '20px',
							'marginBottom' => '20px',
							'marginLeft' => '20px',
							'marginRight' => '20px',
							'slots' => [
								[
									'type' => 'text',
									'slot' => 'content',
									'translations' => [
										Defaults::LANGUAGE_SYSTEM => ['config' => 
											[
												'content' => [
													'value' => "<p style=\"text-align: center; color:#ffffff;\"><span class=\"bc_main_banner_title\">Up to 30% Discount</span><span class=\"bc_main_banner_dec\" style=\"font-size: 28px; font-weight: 600; letter-spacing: 0px;\">Fresh Grocery Collection</span></p>\n <p style=\"text-align: center;\"><a class=\"btn btn-primary\" href=\"#\" rel=\"noopener\" target=\"_blank\" title=\"Shop Now\">Shop Now</a><br></p>",
													'source' => 'static'
												], 
												'verticalAlign' => ['value' => 'center', 'source' => 'static']
											]
										],
										$languageDe => ['config' => 
											[
												'content' => [
													'value' => "<p style=\"text-align: center; color:#ffffff;\"><span class=\"bc_main_banner_title\">Up to 30% Discount</span><span class=\"bc_main_banner_dec\" style=\"font-size: 28px; font-weight: 600; letter-spacing: 0px;\">Fresh Grocery Collection</span></p>\n <p style=\"text-align: center;\"><a class=\"btn btn-primary\" href=\"#\" rel=\"noopener\" target=\"_blank\" title=\"Shop Now\">Shop Now</a><br></p>",
													'source' => 'static'
												], 
												'verticalAlign' => ['value' => 'center', 'source' => 'static']
											]
										]
									]
								]
							]
						]
					]
				],
				[
					'position' => 1,
					'type' => 'default',
					'name' => 'bc-wl-category-heading-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-category-heading',
                    'blocks' => [
						[
							'position' => 0,
							'sectionPosition' => 'main',
							'type' => 'text-teaser',
							'name' => 'bc-wl-category-heading-bk',
							'backgroundMediaMode' => 'cover',
							'marginTop' => '20px',
							'marginLeft' => '20px',
							'marginBottom' => '20px',
							'marginRight' => '20px',
							'slots' => [
								[
									'type' => 'text',
									'slot' => 'content',
									'translations' => [
										Defaults::LANGUAGE_SYSTEM => ['config' => 
											[
												'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">100% Organic Products<br></span>\n<p style=\"text-align: center;\"><i>Experience nature as it was meant to be<br></i></p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => 'center', 'source' => 'static']
											]
										],
										$languageDe => ['config' => 
											[
												'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">100% Organic Products<br></span>\n<p style=\"text-align: center;\"><i>Experience nature as it was meant to be<br></i></p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => 'center', 'source' => 'static']
											]
										]
									]
								]
							]
						]
					]
				],
				[
					'position' => 2,
					'type' => 'default',
					'name' => 'bc-wl-category-list-img-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-category-list-img',
                    'blocks' => [
                        [
                            'position' => 0,
                            'sectionPosition' => 'main',
                            'type' => 'image-text-bubble',
                            'name' => 'bc-wl-category-category-img-bk',
                            'backgroundMediaMode' => 'cover',
							'marginTop' => '20px',
							'marginBottom' => '20px',
							'marginLeft' => '20px',
							'marginRight' => '20px',
                            'slots' => [
                                [
                                    'type' => 'image',
                                    'slot' => 'left-image',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'url' => ['value' => null, 'source' => 'static'],
                                                'newTab' => ['value' => false, 'source' => 'static'],
                                                'media' => ['value' => $bcMediaFiles[1]['id'], 'source' => 'static'],
                                                'minHeight' => ['value' => '340px', 'source' => 'static'],
                                                'displayMode' => ['value' => 'cover', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'url' => ['value' => null, 'source' => 'static'],
                                                'newTab' => ['value' => false, 'source' => 'static'],
                                                'media' => ['value' => $bcMediaFiles[1]['id'], 'source' => 'static'],
                                                'minHeight' => ['value' => '340px', 'source' => 'static'],
                                                'displayMode' => ['value' => 'cover', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'text',
                                    'slot' => 'left-text',
                                    'translations' => [
										Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                               'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">Fresh Veggies<br></span>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
												'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">Fresh Veggies<br></span>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'image',
                                    'slot' => 'center-image',
                                    'translations' => [
										Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                               'url' => ['value' => null, 'source' => 'static'],
												'newTab' => ['value' => false, 'source' => 'static'],
												'media' => ['value' => $bcMediaFiles[2]['id'], 'source' => 'static'],
												'minHeight' => ['value' => '340px', 'source' => 'static'],
												'displayMode' => ['value' => 'cover', 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
											'url' => ['value' => null, 'source' => 'static'],
                                            'newTab' => ['value' => false, 'source' => 'static'],
                                            'media' => ['value' => $bcMediaFiles[2]['id'], 'source' => 'static'],
                                            'minHeight' => ['value' => '340px', 'source' => 'static'],
                                            'displayMode' => ['value' => 'cover', 'source' => 'static'],
                                            'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'text',
                                    'slot' => 'center-text',
                                    'translations' => [
										Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
												'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">Sparkly Fruits<br></span>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
												'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">Sparkly Fruits<br></span>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'image',
                                    'slot' => 'right-image',
                                    'translations' => [
										Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
												'url' => ['value' => null, 'source' => 'static'],
												'newTab' => ['value' => false, 'source' => 'static'],
												'media' => ['value' => $bcMediaFiles[3]['id'], 'source' => 'static'],
												'minHeight' => ['value' => '340px', 'source' => 'static'],
												'displayMode' => ['value' => 'cover', 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
												'url' => ['value' => null, 'source' => 'static'],
												'newTab' => ['value' => false, 'source' => 'static'],
												'media' => ['value' => $bcMediaFiles[3]['id'], 'source' => 'static'],
												'minHeight' => ['value' => '340px', 'source' => 'static'],
												'displayMode' => ['value' => 'cover', 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'text',
                                    'slot' => 'right-text',
                                    'translations' => [
										Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
												'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">Leafy Greens<br></span>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
												'color' => ['value' => null, 'source' => 'static'],
												'content' => [
													'value' => "<span style=\"text-align: center;\" class=\"h2\">Leafy Greens<br></span>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p>",
													'source' => 'static'
												],
												'padding' => ['value' => null, 'source' => 'static'],
												'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
					]
				],
				[
					'position' => 3,
					'type' => 'default',
					'name' => 'bc-wl-promotion-slide1-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-promotion-slide1',
                    'blocks' => [
                        [
                            'position' => 0,
                            'sectionPosition' => 'main',
                            'type' => 'image-text-cover',
                            'name' => 'bc-wl-promotion-slide1-bk',
                            'backgroundMediaMode' => 'cover',
                            'slots' => [
                                [
                                    'type' => 'image',
                                    'slot' => 'left',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'url' => ['value' => null, 'source' => 'static'],
                                                'newTab' => ['value' => false, 'source' => 'static'],
                                                'media' => ['value' => $bcMediaFiles[4]['id'], 'source' => 'static'],
                                                'minHeight' => ['value' => '400px', 'source' => 'static'],
                                                'displayMode' => ['value' => 'cover', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'url' => ['value' => null, 'source' => 'static'],
                                                'newTab' => ['value' => false, 'source' => 'static'],
                                                'media' => ['value' => $bcMediaFiles[4]['id'], 'source' => 'static'],
                                                'minHeight' => ['value' => '400px', 'source' => 'static'],
                                                'displayMode' => ['value' => 'cover', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'text',
                                    'slot' => 'right',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                               'color' => ['value' => null, 'source' => 'static'],
                                                'content' => [
                                                    'value' => "<span style=\"text-align: center;\" class=\"h2\">Why is Organic Good for You? <br></span>\n <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,\n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.\n Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>",
                                                    'source' => 'static'
                                                ],
                                                'padding' => ['value' => null, 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'color' => ['value' => null, 'source' => 'static'],
                                                'content' => [
                                                    'value' => "<span style=\"text-align: center;\" class=\"h2\">Why is Organic Good for You? <br></span>\n <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,\n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.\n Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>",
                                                    'source' => 'static'
                                                ],
                                                'padding' => ['value' => null, 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
					]
				],
				[
					'position' => 4,
					'type' => 'default',
					'name' => 'bc-wl-product-slider1-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-product-slider1',
                    'blocks' => [
                        [
                            'position' => 0,
                            'sectionPosition' => 'main',
                            'type' => 'product-slider',
                            'name' => 'bc-wl-product-slider1-bk',
                            'backgroundMediaMode' => 'cover',
							'marginTop' => '20px',
							'marginBottom' => '20px',
							'marginLeft' => '20px',
							'marginRight' => '20px',
                            'slots' => [
                                [
                                    'type' => 'product-slider',
                                    'slot' => 'productSlider',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'title' => ['source' => 'static', 'value' => 'New Arrivals'],
                                                'border' => ['source' => 'static', 'value' => false],
                                                'rotate' => ['value' => false, 'source' => 'static'],
                                                'products' => [
                                                    'value' => [$productIds[0]['id'], $productIds[1]['id'], $productIds[2]['id'], $productIds[3]['id']],
                                                    'source' => 'static'
                                                ],
                                                'boxLayout' => ['value' => 'standard', 'source' => 'static'],
                                                'elMinWidth' => ['value' => '350px', 'source' => 'static'],
                                                'navigation' => ['value' => true, 'source' => 'static'],
                                                'displayMode' => ['value' => 'standard', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'title' => ['source' => 'static', 'value' => 'New Arrivals'],
                                                'border' => ['source' => 'static', 'value' => false],
                                                'rotate' => ['value' => false, 'source' => 'static'],
                                                'products' => [
                                                    'value' => [$productIds[0]['id'], $productIds[1]['id'], $productIds[2]['id'], $productIds[3]['id']],
                                                    'source' => 'static'
                                                ],
                                                'boxLayout' => ['value' => 'standard', 'source' => 'static'],
                                                'elMinWidth' => ['value' => '350px', 'source' => 'static'],
                                                'navigation' => ['value' => true, 'source' => 'static'],
                                                'displayMode' => ['value' => 'standard', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
					]
				],
				[
					'position' => 5,
					'type' => 'default',
					'name' => 'bc-wl-company-desc-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-company-desc',
                    'blocks' => [
                        [
                            'position' => 0,
                            'sectionPosition' => 'main',
                            'type' => 'text-hero',
                            'name' => 'bc-wl-company-desc-bk',
                            'backgroundMediaMode' => 'cover',
							'marginTop' => '20px',
							'marginBottom' => '20px',
							'marginLeft' => '20px',
							'marginRight' => '20px',
                            'slots' => [
                                [
                                    'type' => 'text',
                                    'slot' => 'content',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'color' => ['value' => null, 'source' => 'static'],
                                                'content' => [
                                                    'value' => "<span style=\"text-align: center;\" class=\"h2\">About our company</span>\n <hr>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. \n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>",
                                                    'source' => 'static'
                                                ],
                                                'padding' => ['value' => null, 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'color' => ['value' => null, 'source' => 'static'],
                                                'content' => [
                                                    'value' => "<span style=\"text-align: center;\" class=\"h2\">About our company</span>\n <hr>\n <p style=\"text-align: center;\">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, \n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, \n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. \n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>",
                                                    'source' => 'static'
                                                ],
                                                'padding' => ['value' => null, 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
					]
				],
				[
					'position' => 6,
					'type' => 'default',
					'name' => 'bc-wl-promotion-banner-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-promotion-banner',
                    'blocks' => [
                        [
                            'position' => 0,
                            'sectionPosition' => 'main',
                            'type' => 'text-on-image',
                            'name' => 'bc-wl-promotion-banner-bk',
                            'backgroundMediaId' => $bcMediaFiles[5]['id'],
                            'backgroundMediaMode' => 'cover',
							'marginTop' => '20px',
							'marginBottom' => '20px',
							'marginLeft' => '20px',
							'marginRight' => '20px',
                            'slots' => [
                                [
                                    'type' => 'text',
                                    'slot' => 'content',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'content' => [
                                                    'value' => "<span class=\"h2 bc_promotion_banner_text\" style=\"text-align: center; color:#fff;\">Can You Only Eat Vegetables &amp; Still Stay Healthy?</span>\n <p style=\"text-align: center;\"><a class=\"btn btn-primary\" href=\"#\" rel=\"noopener\" target=\"_self\" title=\"READ MORE\">READ MORE</a><br></p>",
                                                    'source' => 'static'
                                                ],
                                                'verticalAlign' => ['value' => 'center', 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'content' => [
                                                    'value' => "<span class=\"h2 bc_promotion_banner_text\" style=\"text-align: center; color:#fff;\">Can You Only Eat Vegetables &amp; Still Stay Healthy?</span>\n <p style=\"text-align: center;\"><a class=\"btn btn-primary\" href=\"#\" rel=\"noopener\" target=\"_self\" title=\"READ MORE\">READ MORE</a><br></p>",
                                                    'source' => 'static'
                                                ],
                                                'verticalAlign' => ['value' => 'center', 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
					]
				],
				[
					'position' => 7,
					'type' => 'default',
					'name' => 'bc-wl-product-slider2-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-product-slider2',
                    'blocks' => [
                        [
                            'position' => 0,
                            'sectionPosition' => 'main',
                            'type' => 'product-slider',
                            'name' => 'bc-wl-product-slider2-bk',
                            'backgroundMediaMode' => 'cover',
							'marginTop' => '20px',
							'marginBottom' => '20px',
							'marginLeft' => '20px',
							'marginRight' => '20px',
                            'slots' => [
                                [
                                    'type' => 'product-slider',
                                    'slot' => 'productSlider',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'title' => ['source' => 'static', 'value' => 'Best Sellers'],
                                                'border' => ['source' => 'static', 'value' => false],
                                                'rotate' => ['value' => false, 'source' => 'static'],
                                                'products' => [
                                                    'value' => [$productIds[3]['id'], $productIds[0]['id'], $productIds[2]['id'], $productIds[1]['id']],
                                                    'source' => 'static'
                                                ],
                                                'boxLayout' => ['value' => 'standard', 'source' => 'static'],
                                                'elMinWidth' => ['value' => '350px', 'source' => 'static'],
                                                'navigation' => ['value' => true, 'source' => 'static'],
                                                'displayMode' => ['value' => 'standard', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'title' => ['source' => 'static', 'value' => 'Best Sellers'],
                                                'border' => ['source' => 'static', 'value' => false],
                                                'rotate' => ['value' => false, 'source' => 'static'],
                                                'products' => [
                                                    'value' => [$productIds[3]['id'], $productIds[0]['id'], $productIds[2]['id'], $productIds[1]['id']],
                                                    'source' => 'static'
                                                ],
                                                'boxLayout' => ['value' => 'standard', 'source' => 'static'],
                                                'elMinWidth' => ['value' => '350px', 'source' => 'static'],
                                                'navigation' => ['value' => true, 'source' => 'static'],
                                                'displayMode' => ['value' => 'standard', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
					]
				],
				[
					'position' => 8,
					'type' => 'default',
					'name' => 'bc-wl-promotion-slide2-block',
					'sizingMode' => 'full_width',
					'backgroundMediaMode' => 'cover',
					'cssClass' => 'bc-wl-promotion-slide2',
                    'blocks' => [
                        [
                            'position' => 0,
                            'sectionPosition' => 'main',
                            'type' => 'image-text-cover',
                            'name' => 'bc-wl-promotion-slide2-bk',
                            'backgroundMediaMode' => 'cover',
                            'slots' => [
                                [
                                    'type' => 'image',
                                    'slot' => 'left',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'url' => ['value' => null, 'source' => 'static'],
                                                'newTab' => ['value' => false, 'source' => 'static'],
                                                'media' => ['value' => $bcMediaFiles[6]['id'], 'source' => 'static'],
                                                'minHeight' => ['value' => '400px', 'source' => 'static'],
                                                'displayMode' => ['value' => 'cover', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'url' => ['value' => null, 'source' => 'static'],
                                                'newTab' => ['value' => false, 'source' => 'static'],
                                                'media' => ['value' => $bcMediaFiles[6]['id'], 'source' => 'static'],
                                                'minHeight' => ['value' => '400px', 'source' => 'static'],
                                                'displayMode' => ['value' => 'cover', 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'type' => 'text',
                                    'slot' => 'right',
                                    'translations' => [
                                        Defaults::LANGUAGE_SYSTEM => ['config' => 
                                            [
                                                'color' => ['value' => null, 'source' => 'static'],
                                                'content' => [
                                                    'value' => "<span style=\"text-align: center;\" class=\"h2\">Try a Shake Every Day! <br></span>\n <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,\n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.\n Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>",
                                                    'source' => 'static'
                                                ],
                                                'padding' => ['value' => null, 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ],
                                        $languageDe => ['config' => 
                                            [
                                                'color' => ['value' => null, 'source' => 'static'],
                                                'content' => [
                                                    'value' => "<span style=\"text-align: center;\" class=\"h2\">Try a Shake Every Day! <br></span>\n <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,\n sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.\n Lorem ipsum dolor sit amet, consetetur sadipscing elitr,\n sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.\n At vero eos et accusam et justo duo dolores et ea rebum.\n Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>",
                                                    'source' => 'static'
                                                ],
                                                'padding' => ['value' => null, 'source' => 'static'],
                                                'verticalAlign' => ['value' => null, 'source' => 'static']
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
					]
				]
            ]
        ];
		$cmsRepository = $this->container->get('cms_page.repository')->create([$page], $this->context);
    }

	/**
     * Remove cms page entries and configuration
     * @return void
     */
    private function removeBcCmsPage(): void
    {
        $bcCmsPageId = $this->getBcCmsPageId();
        $context = Context::createDefaultContext();
        
        if ($bcCmsPageId === null) {
            return;
        }
	
        if ($bcCmsPageId !== null && $this->isCmsPageActive($bcCmsPageId) === 1) {
            return;
        }
        
        $this->deleteConfiguration($context, 'cms_page.repository', 'id', $bcCmsPageId, false, true);
        $this->deleteConfiguration($context, 'cms_section.repository', 'pageId', $bcCmsPageId, true, true);

        foreach ($this->bc_media_files_ary as $bcMedianame) {
			$this->deleteConfiguration($context, 'media.repository', 'fileName', $bcMedianame);
        }
        
        $this->deleteConfiguration($context, 'theme.repository', 'technicalName', 'BrandCrockWanderlust');

        foreach ($this->bc_theme_media_files_ary as $bcThemeMediaName) {
			$this->deleteConfiguration($context, 'media.repository', 'fileName', $bcThemeMediaName);
        }
    }
    
	/**
     * Get cms page id
     * @return string|null $cmsPageTranslation
     */
    private function getBcCmsPageId(): ?string
    {
		$context = Context::createDefaultContext();
		$cmsRepository = $this->container->get('cms_page_translation.repository');
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('name', 'Wanderlust Home Page'));
        $cmsPageTranslation = $cmsRepository->search($criteria, $context)->first();
        return $cmsPageTranslation === null ? null : $cmsPageTranslation->getCmsPageId();
    }

    /**
     * Get default media folder ids
     * @return string|null $result
     */
    private function getDefaultFolderIdForEntity(): ?string
    {
		$criteria = new Criteria();
		$criteria->addFilter(new EqualsFilter('defaultFolder.entity', 'cms_page'));
		$result = $this->container->get('media_folder.repository')->searchIds($criteria, Context::createDefaultContext());
        return $result ? $result->firstId() : null;
    }

	/**
	* Get product ids
	* @param int $limit
	* @return array $ids
	*/
    private function getShopProductIds($limit): array
    {
		$criteria = new Criteria();
        $criteria->addAssociation('product');
        $criteria->setLimit($limit);
		$productIds = $this->container->get('product.repository')->searchIds($criteria, Context::createDefaultContext());
		$ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $productIds->getIds());
        return $ids;
    }
    
    /**
     * Check cms page is active
     * @param string $pageId
     * @return int $result
     */
    private function isCmsPageActive($pageId)
    {  
		$criteria = new Criteria();
        $criteria->addAssociation('category');
        $criteria->addFilter(new EqualsFilter('cmsPageId', $pageId));
		$cmsRepository = $this->container->get('category.repository')->search($criteria, Context::createDefaultContext())->first();
        return $cmsRepository ? $cmsRepository->getActive() : null;
    }

	/**
     * Delete plugin related configurations
     * @param Context $context
     * @param string $repository
     * @param string $whereCol
     * @param string $value
     * @param boolean $addAssociation
     * @param boolean $isId
     * @return void
     */
	public function deleteConfiguration(Context $context, string $repository, string  $whereCol, string $value, bool $addAssociation = false, bool $isId = false)
    {
        $repository = $this->container->get($repository);
		$bcFilter = (!empty($isId)) ? new EqualsFilter($whereCol, $value) : new ContainsFilter($whereCol, $value);			
        $criteria = (new Criteria())
			->addFilter($bcFilter);

        if(!empty($addAssociation))
        {
			$criteria->addAssociation('page');
			$criteria->addAssociation('blocks');
			$criteria->addAssociation('blocks.slots');
		}

        $idSearchResult = $repository->searchIds($criteria, $context);
        $ids = array_map(static function ($id) {
            return ['id' => $id];
        }, $idSearchResult->getIds());

		if(!empty($ids)) {
			$repository->delete($ids, $context);
		}
    }
}
