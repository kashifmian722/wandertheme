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
use Shopware\Core\Framework\Context;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @RouteScope(scopes={"storefront"})
 */
class MerchantSearchController extends StorefrontController
{
    /**
     * @Route("/wanderlust/search/setpopup", name="wanderlust.search.popup", methods={"GET"}, defaults={"XmlHttpRequest": true}))
     */
    public function setRevealPopup(Request $request, Context $context): JsonResponse
    {
        $session = $request->getSession();
        if (!empty($request->get('type')) && $request->get('type') == 'revealpopup') {
            $session->set('disableBcWlPopup', 1);
        }
        return new JsonResponse(true);
    }
}
