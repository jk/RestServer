<?php


namespace JK\RestServer\ApiDescription\Swagger;

use JK\RestServer\RestServer;

/**
 * @property RestServer server
 */
class SwaggerController
{
    public static $info_version = '1.0.0';
    public static $info_title = null;
    public static $info_contact_name = null;
    public static $info_contact_email = null;
    public static $info_contact_url = null;

    /**
     * @noAuth
     * @url GET /
     */
    public function swagger()
    {
        $paths = array_merge(
            $this->parseMapToSwaggerPathsForVerb('get'),
            $this->parseMapToSwaggerPathsForVerb('post'),
            $this->parseMapToSwaggerPathsForVerb('put'),
            $this->parseMapToSwaggerPathsForVerb('delete')
        );

        $swagger = [
            'swagger' => '2.0',
            'info' => [
                'version' => self::$info_version,
                'title' => self::$info_title,
                'contact' => [
                    'name' => self::$info_contact_name,
                    'email' => self::$info_contact_email,
                    'url' => self::$info_contact_url
                ]
            ],
            'host' => $_SERVER['HTTP_HOST'],
            'basePath' => $this->getBasePath(),
            'schemes' => [
                'http',
                'https'
            ],
            'consumes' => [
                'application/json',
                'application/xml',
                'application/x-www-form-urlencoded'
            ],
            'produces' => [
                'application/json',
                'application/xml'
            ],
            'paths' => $paths
        ];

        return $swagger;
    }

    /**
     * @noAuth
     * @url GET /debug
     * @url POST /debug
     */
    public function debug()
    {
//        return $_SERVER;
        return $this->server->getMap();
    }

    protected function filterPath($path) {
        $basePath = ltrim($this->getBasePath(), '/');

        $path = substr($path, strlen($basePath));

        $pattern = '/\$([a-z0-9_-]+)/i';
        $replacement = '{$1}$2';
        $path = preg_Replace($pattern, $replacement, $path);

        return $path;
    }

    /**
     * @return string API base path
     */
    protected function getBasePath() {
        return dirname($_SERVER['SCRIPT_NAME']);
    }

    protected function parseMapToSwaggerPathsForVerb($verb)
    {
        $map = $this->server->getMap();

        $paths = [];
        $routes = $map[strtoupper($verb)];
        foreach ($routes as $path => $detail) {
            $path = $this->filterPath($path);

            $paths[$path][strtolower($verb)]['description'] = $detail['docblock'];
            $paths[$path][strtolower($verb)]['operationId'] = 'operationId';
            $paths[$path][strtolower($verb)]['tags'] = ['swagger', 'debug'];

            foreach ($detail[2] as $paramaeter_name => $default_value) {
                if ($paramaeter_name == 'language' || $paramaeter_name == 'data') {
                    continue;
                }

                $param = [];
                $param['name'] = $paramaeter_name;
                $param['in'] = 'path';
                $param['required'] = true;
                $param['type'] = (substr($paramaeter_name, -3) == '_id') ? 'integer' : 'string';
                $paths[$path][strtolower($verb)]['parameters'][] = $param;
            }
        }

        return $paths;
    }
}
