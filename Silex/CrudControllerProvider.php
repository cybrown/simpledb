<?php
namespace Cy\SimpleDB\Silex;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class CrudControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/{collection}/', function (Application $app, $collection) {
            return $app->json($app['db'][$collection]->select());
        });
        
        $controllers->get('/{collection}/lastupdate', function (Application $app, $collection) {
            return $app->json($app['db'][$collection]->getLastUpdate());
        });
        
        $controllers->get('/{collection}/{id}/', function (Application $app, $collection, $id) {
            return $app->json($app['db'][$collection]->selectOne($id));
        })
        ->assert('id', '\d+');
        
        $controllers->post('/{collection}/', function (Application $app, $collection) {
            $hash = array();
            foreach ($app['request']->request as $k => $v) {
                $hash[$k] = $v;
            }
            return $app->json($app['db'][$collection]->insert($hash));
        })
        ->before(function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace($data);
            }
        });
        
        $controllers->put('/{collection}/id}/', function (Application $app, $collection) {
            $hash = array();
            foreach ($app['request']->request as $k => $v) {
                $hash[$k] = $v;
            }
            return $app->json($app['db'][$collection]->insert($hash));
        })
        ->before(function (Request $request) {
            if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
                $data = json_decode($request->getContent(), true);
                $request->request->replace($data);
            }
        })
        ->assert('id', '\d+');
        
        $controllers->delete('/{collection}/{id}/', function (Application $app, $collection, $id) {
            return $app->json($app['db'][$collection]->delete($id));
        })
        ->assert('id', '\d+');
        
        $controllers->get('/', function (Application $app) {
            return "test get";
        });
        
        $controllers->post('/', function (Application $app) {
            return "test post";
        });
        
        return $controllers;
    }
}
