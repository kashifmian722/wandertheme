<?php declare(strict_types=1);
/**
 * Controller for session handling.
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
 */
 
namespace Bc\BrandCrockWanderlust\Storefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Shopware\Storefront\Page\GenericPageLoader;
use Shopware\Core\Framework\Context;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @RouteScope(scopes={"storefront"})
 */
class BcWlSessionHandleController extends StorefrontController
{

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $productRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $merchantRepository;

    /**
     * @var GenericPageLoader
     */
    private $genericPageLoader;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        SalesChannelRepositoryInterface $productRepository,
        EntityRepositoryInterface $merchantRepository,
        GenericPageLoader $genericPageLoader,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productRepository = $productRepository;
        $this->merchantRepository = $merchantRepository;
        $this->genericPageLoader = $genericPageLoader;
        $this->eventDispatcher = $eventDispatcher;
    }
    /**
     * @Route("/wanderlust/setpopup", name="wanderlust.set.popup", defaults={"type" = "revealpopup"}, methods={"GET"}, defaults={"XmlHttpRequest": true}))
     */
    public function setRevealPopup(Request $request, Context $context): JsonResponse
    {
        $session = $request->getSession();
        if (!empty($request->get('type')) && $request->get('type') == 'revealpopup') {
            $session->set('disableBcWlPopup', 1);
        }
        return new JsonResponse(true);
    }
    
    /**
     * @Route("/wanderlust/setnotificationpopup", name="wanderlust.set.promotionpopup", defaults={"type" = "promotionpopup"}, methods={"GET"}, defaults={"XmlHttpRequest": true}))
     */
    public function setPromotionPopup(Request $request, Context $context) : JsonResponse
    {
        $session = $request->getSession();
        if (!empty($request->get('type')) && $request->get('type') == 'promotionpopup') {
            $session->set('disableBcWlPromotionpopup', 1);
        }
        return new JsonResponse(true);
    }
    
    /**
    * @Route("/wanderlust/wlsession", name="wanderlust.get.wlsession", methods={"GET"}, defaults={"XmlHttpRequest": true}))
    */
    public function getWlSession(Request $request, Context $context): JsonResponse
    {
        $session = $request->getSession();
        $sessionBcWlList = [];
        if ($session->has('disableBcWlPopup')) {
            $sessionBcWlList['disableBcWlPopup'] = 1;
        }
        
        if ($session->has('disableBcWlPromotionpopup')) {
            $sessionBcWlList['disableBcWlPromotionPopup'] = 1;
        }
        
        return new JsonResponse($sessionBcWlList);
    }

    /**
     * @Route("/merchant/search", name="merchant.search", methods={"GET"}, defaults={"XmlHttpRequest": true}))
     */
    public function searchMerchant(Request $request, SalesChannelContext $context): Response
    {
        $search_value = $request->get('search');

        if (strpos($search_value, "#") !== false) {
            $tag_search_value = explode("#", $search_value)[1];
            $criteria = new Criteria();
            $criteria->addFilter(new ContainsFilter('customFields.tags', $tag_search_value));
            $criteria->addAssociation('media.thumbnails');
            $criteria->addAssociation('cover');
            $criteria->addAssociation('services');
        }
       elseif (true) {
    $criteria = new Criteria();
    $criteria->addFilter(new ContainsFilter('city', $search_value));
    $criteria->addAssociation('media.thumbnails');
    $criteria->addAssociation('cover');
    $criteria->addAssociation('services');
}
        else{
            $criteria = new Criteria();
            $criteria->addFilter(new ContainsFilter('publicCompanyName', $search_value));
            $criteria->addAssociation('media.thumbnails');
            $criteria->addAssociation('cover');
            $criteria->addAssociation('services');
        }

            

        $merchants = $this->merchantRepository->search($criteria, Context::createDefaultContext());

        $page = $this->genericPageLoader->load($request, $context);

        $vars = [
            'page' => $page,
            'searched' => $search_value,
            'merchants' => $merchants,
            'results_count' => count($merchants),
        ];

        return $this->renderStorefront('@BrandCrockWanderlust/storefront/page/merchant/search_merchant.html.twig',  $vars);
    }

    /**
     * @Route("/merchant/suggest/", name="merchant.search.suggest", methods={"GET"}, defaults={"XmlHttpRequest": true}))
     */
    public function suggestMerchant(Request $request, SalesChannelContext $context): Response
    {
        $search_value = $request->get('search');

        if (strpos($search_value, "#") !== false) {
            $search_value = explode("#", $search_value)[1];
            $criteria = new Criteria();
            $criteria->addFilter(new ContainsFilter('customFields.tags', $search_value));
            $criteria->addAssociation('media.thumbnails');
            $criteria->addAssociation('cover');
        }
       elseif (true) {
            $criteria = new Criteria();
            $criteria->addFilter(new ContainsFilter('city', $search_value));
            $criteria->addAssociation('media.thumbnails');
            $criteria->addAssociation('cover');
            $criteria->addAssociation('services');
}
        else{
            $criteria = new Criteria();
            $criteria->addFilter(new ContainsFilter('publicCompanyName', $search_value));
            $criteria->addAssociation('media.thumbnails');
            $criteria->addAssociation('cover');
        }

            

        $merchants = $this->merchantRepository->search($criteria, Context::createDefaultContext());

        $page = $this->genericPageLoader->load($request, $context);

        $vars = [
            'page' => $page,
            'searched' => $search_value,
            'merchants' => $merchants,
            'results_count' => count($merchants),
        ];

        return $this->render('@BrandCrockWanderlust/storefront/component/merchant/suggest.html.twig',  $vars);
    }
}
