<?php

namespace Slim3\Annotation;


use Slim\App;

class Slim3Annotation
{

    public static $cache_path;


    /**
     * @param App $application
     * @param array $arrayController
     * @param string $pathCache
     * @throws \Exception
     */
    public static function create(App $application, array $arrayController, $pathCache) {

        self::createAutoloadCache($pathCache);

        foreach ($arrayController as $pathController) {
            $collector = new CollectorRoute();
            $arrayRoute = $collector->getControllers($pathController);

            $arrayRouteObject = $collector->castRoute($arrayRoute);
            self::injectRoute($application, $arrayRouteObject, $arrayRoute, $pathCache);
        }
    }

    public static function createAutoloadCache($pathCache) {
        self::$cache_path = $pathCache;

        spl_autoload_register([ 'Slim3\Annotation\Slim3Annotation', 'loadClassAutoload' ]);
    }

    public static function loadClassAutoload($class) {

        $extension = ".php";
        $class = str_replace("Cache\\", "", $class);
        $file = str_replace("\\", DIRECTORY_SEPARATOR, self::$cache_path . DIRECTORY_SEPARATOR . $class . $extension);

        if (file_exists($file)) {
            include $file;
        }
    }

    private static function injectRoute(App $application, array $arrayRouteObject, array $arrayRoute, $pathCache) {

        $di = new \ReflectionClass($application->getContainer());
        $di = $di->getName();

        $validate = new CacheAnnotation($pathCache, $application);

        if ($validate->updatedCache($arrayRoute, $arrayRouteObject)) {
            $validate->loadLastCache();
        } else {
            foreach ($arrayRouteObject as $routeModel) {
                if ($di === 'DI\Container') {
                    $route = $application->map([$routeModel->getVerb()], $routeModel->getRoute(), ['\\'.$routeModel->getClassName(), $routeModel->getMethodName()]);
                } else {
                    $route = $application->map([$routeModel->getVerb()], $routeModel->getRoute(), $routeModel->getClassName() . ':' . $routeModel->getMethodName());
                }

                if ($routeModel->getAlias() != null) {
                    $route->setName($routeModel->getAlias());
                }

                if ($routeModel->getClassMiddleware() != null) {
                    $classMiddleware = $routeModel->getClassMiddleware();
                    foreach ($classMiddleware as $middleware) {
                        if ($di === 'DI\Container') {
                            $route->add("$middleware::class");
                        } else {
                            $route->add(new $middleware());
                        }
                    }
                }
            }

            $validate->write($arrayRouteObject);
        }
    }

}