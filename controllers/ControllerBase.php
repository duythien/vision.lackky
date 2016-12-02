<?php

namespace Lackky\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Phalcon\Paginator\Adapter\NativeArray as PaginatorNativeArray;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Pagination\Cursor;
use Lackky\Models\Api\ModelBase;
use League\Fractal\Pagination\PhalconFrameworkPaginatorAdapter;


/**
 * Class ControllerBase
 *
 * @package Lackky\Controllers
 */
class ControllerBase extends Controller
{
    /**
     * @constant string name of api with the 400 client error
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     */
    const CODE_WRONG_ARGS       = 'GEN-FUBARGS';

    /**
     * @constant string name of api with the 404 client error
     */
    const CODE_NOT_FOUND        = 'GEN-LIKETHEWIND';

    /**
     * @constant string name of api with the 500 server error
     */
    const CODE_INTERNAL_ERROR   = 'GEN-AAAGGH';

    /**
     * @constant string name of api with the 401 client error
     */
    const CODE_UNAUTHORIZED     = 'GEN-MAYBGTFO';

    /**
     * @constant string name of api with the 403 client error
     */
    const CODE_FORBIDDEN        = 'GEN-GTFO';

    const CODE_WRONG_DATA       = 'GEN-DATA';

    /**
     *
     * @var integer
     */
    protected $statusCode = 200;
    /**
     * @var int
     */
    protected $perPage = 10;

    protected $userId = null;
    /**
     * @var null
     */
    protected $tenantId = null;
    /**
     * @var string
     */
    protected $userDateFormat = 'd/m/Y';

    protected $customField;

    /**
     * Getter for statusCode
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $item
     * @param $callback
     * @return JsonResponse
     */
    protected function respondWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);

        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * @param $collection
     * @param $callback
     * @return JsonResponse
     */
    protected function respondWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);
        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * @param $collection
     * @param $callback
     * @return JsonResponse
     */
    protected function respondWithPagination($paginator, $callback)
    {
        $pagination = $paginator->getPaginate();
        $resource = new Collection($pagination->items, $callback);
        $resource->setPaginator(new PhalconFrameworkPaginatorAdapter($pagination));

        $rootScope = $this->fractal->createData($resource);
        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * @param $paginator
     * @param $callback
     * @return JsonResponse
     */
    protected function respondWithCursor($paginator, $callback)
    {
        $pagination = $paginator->getPaginate();
        $resource = new Collection($pagination->items, $callback);
        $cursor = new Cursor(
            $pagination->current,
            $pagination->before,
            $pagination->next,
            $pagination->total_items
        );
        $resource->setCursor($cursor);
        $rootScope = $this->fractal->createData($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * @param array $array
     * @param array $headers
     * @return JsonResponse
     */
    protected function respondWithArray(array $array, array $headers = [])
    {

        return $this->makeJson($array, $headers);
    }

    /**
     * @param $message
     * @param $errorCode
     * @return JsonResponse
     */
    protected function respondWithError($message, $errorCode)
    {
        return $this->respondWithArray([
            'error' => [
                'code' => $errorCode,
                'http_code' => $this->statusCode,
                'message' => $message,
            ]
        ]);
    }

    public function respondWithSuccess($message = 'ok')
    {
        return $this->respondWithArray(
            [
                'success' => [
                    'message' => $message,
                ]
            ]
        );
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)->respondWithError($message, self::CODE_FORBIDDEN);
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)->respondWithError($message, self::CODE_INTERNAL_ERROR);
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)->respondWithError($message, self::CODE_NOT_FOUND);
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorUnauthorized($message = 'Unauthorized')
    {

        return $this->setStatusCode(401)->respondWithError($message, self::CODE_UNAUTHORIZED);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)->respondWithError($message, self::CODE_WRONG_ARGS);
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @return Response
     */
    public function errorWrongData($message = 'Wrong Data')
    {
        return $this->setStatusCode(409)->respondWithError($message, self::CODE_WRONG_DATA);
    }

    /**
     * @param $query
     * @return PaginatorQueryBuilder
     */
    public function pagination($query)
    {
        $page     = $this->request->getQuery('page') ?  : 1;
        $perPage  = $this->request->getQuery('limit') ? : $this->perPage;
        if (is_object($query)) {
            $paginator = new PaginatorModel([
                'data' => $query,
                'limit' => $perPage,
                'page' => $page
            ]);
        } elseif(isset($query['model'])) {
            $builder  = ModelBase::modelQuery($query);
            $paginator  = new PaginatorQueryBuilder(
                [
                    'builder'   => $builder,
                    'limit'     => $perPage,
                    'page'      => $page
                ]
            );
        } else {

            $paginator = new PaginatorNativeArray([
                'data' => $query,
                'limit' => $perPage,
                'page' => $page
            ]);
        }
        return $paginator;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function getOne($query)
    {
        $builder  = ModelBase::modelQuery($query);
        return $builder
            ->getQuery()
            ->setUniqueRow(true)
            ->execute();
    }

    /**
     * @return array
     */
    public function getParameter()
    {
        $query  = $this->request->getQuery();
        $query  = array_filter($query, function($val){
            return !empty($val);
        });
        //define the fields required for a partial response.
        if (isset($query['fields'])) {
            $fields = explode(',', $query['fields']);
            $query['fields'] = $fields;
        }

        return $query;
    }

    /**
     * @return array
     */
    public function parserDataRequest()
    {
        $posts = $this->request->getJsonRawBody(true);

        if (0 == count($posts)) {
            if ($this->request->isPut()) {
                //@todo paser data;
                $posts = $this->request->getPut();
            }
            if ($this->request->isPost()) {
                $posts = $this->request->getPost();
            }
        }
        return $posts;
    }

    public function makeJson($data = [], $header = [])
    {
        //$mimeTypeRaw = $this->getHeaders($header)->get('Content-Type');

        $mimeTypeRaw = $this->request->getServer('HTTP_ACCEPT');

        // If its empty or has */* then default to JSON
        if ($mimeTypeRaw === '*/*' || !$mimeTypeRaw) {
            $mimeType = 'application/json';
        } else {
            // You'll probably want to do something intelligent with charset if provided
            $mimeParts = (array) explode(';', $mimeTypeRaw);
            $mimeType = strtolower($mimeParts[0]);
        }
        $content = json_encode($data);
        switch ($mimeType) {
            case 'application/json':
                $contentType = 'application/json';
                break;
            case 'application/x-yaml':
                $contentType = 'application/x-yaml';
                break;
            default:
                $contentType = 'application/json';

        }
        $this->response->setContentType($contentType, 'UTF-8');
        return $this->response->setContent($content);
    }

    /**
     * @param Dispatcher $dispatcher
     * @return Response|void
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {

    }

    public function afterExecuteRoute(Dispatcher $dispatcher)
    {
        //Only response json right now
        header('Content-Type: application/json');
    }
}
