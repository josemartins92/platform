<?php

namespace Oro\Bundle\ApiBundle\ApiDoc;

use Symfony\Component\Routing\Route;

use Oro\Component\Routing\Resolver\EnhancedRouteCollection;
use Oro\Component\Routing\Resolver\RouteCollectionAccessor;
use Oro\Component\Routing\Resolver\RouteOptionsResolverInterface;
use Oro\Component\Routing\RouteCollectionUtil;

trait ApiDocExtractorTrait
{
    /** @var RouteOptionsResolverInterface */
    protected $routeOptionsResolver;

    /** @var RestDocViewDetector */
    protected $docViewDetector;

    /**
     * Sets the RouteOptionsResolver.
     *
     * @param RouteOptionsResolverInterface $routeOptionsResolver
     */
    public function setRouteOptionsResolver(RouteOptionsResolverInterface $routeOptionsResolver)
    {
        $this->routeOptionsResolver = $routeOptionsResolver;
    }

    /**
     * Sets the RestDocViewDetector.
     *
     * @param RestDocViewDetector $docViewDetector
     */
    public function setRestDocViewDetector(RestDocViewDetector $docViewDetector)
    {
        $this->docViewDetector = $docViewDetector;
    }

    /**
     * @param Route[] $routes
     *
     * @return Route[]
     */
    protected function processRoutes(array $routes)
    {
        $routeCollection = new EnhancedRouteCollection($routes);
        $routeCollectionAccessor = new RouteCollectionAccessor($routeCollection);
        /** @var Route $route */
        foreach ($routeCollection as $route) {
            $this->routeOptionsResolver->resolve($route, $routeCollectionAccessor);
        }
        $routes = $routeCollection->all();

        return RouteCollectionUtil::filterHidden($routes);
    }

    /**
     * @param string $view
     *
     * @return string
     */
    protected function resolveView($view)
    {
        $detectedView = $this->docViewDetector->getView();
        if ($detectedView) {
            $view = $detectedView;
        }

        return $view;
    }
}
