<?php
/**
 * This file is part of the PositibeLabs Projects.
 *
 * (c) Pedro Carlos Abreu <pcabreus@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Positibe\Bundle\CmfBundle\Controller;

use Lunetics\LocaleBundle\Event\FilterLocaleSwitchEvent;
use Lunetics\LocaleBundle\LocaleBundleEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class LocaleController
 * @package Positibe\Bundle\CmfBundle\Controller
 *
 * @author Pedro Carlos Abreu <pcabreus@gmail.com>
 */
class LocaleController extends Controller
{

    /**
     * @param Request $request
     * @param null $newLocale
     * @return RedirectResponse
     */
    public function changeLocaleAction(Request $request, $newLocale = null)
    {
        $locale = $newLocale !== null ? $newLocale : $request->getLocale();

        $request->setLocale($locale);
        $request->attributes->set('_locale', $locale);

        $localeSwitchEvent = new FilterLocaleSwitchEvent($request, $locale);
        $this->get('event_dispatcher')->dispatch(LocaleBundleEvents::onLocaleChange, $localeSwitchEvent);

        $redirectRoute = $request->get('redirectRoute');
        if ($redirectRoute !== null && $route = $this->get('positibe_orm_routing.route.provider')->getRouteByName(
                $redirectRoute
            )
        ) {

            if ($content = $this->get('positibe.repository.page')->findOneByRoutes($route)) {
                $redirect = $this->get('router')->generate($content, array('_locale' => $locale));
            }
            else {
                $redirect = $this->get('router')->generate($route);
            }
        } else {
            $redirect = $request->server->get('HTTP_REFERER') ? $request->server->get('HTTP_REFERER') : '/';
        }


        $response = new RedirectResponse($redirect, Response::HTTP_FOUND);

        return $response;
    }
} 