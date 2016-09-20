<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;
    require dirname(__FILE__) . '/vendor/autoload.php';

    spl_autoload_register(function($class_name){
        if(file_exists('Data/' . $class_name . '.php')){
            require_once('Data/' . $class_name . '.php');
        }elseif(file_exists('includes/' . $class_name . '.php')){
            require_once('includes/' . $class_name . '.php');
        }elseif(file_exists('Controllers/' . $class_name . '.php')){
            require_once('Controllers/' . $class_name . '.php');
        }elseif(file_exists($class_name . '.php')){
            require_once($class_name . '.php');
        }else if(file_exists('Model/' . $class_name . '.php')){
            require_once('Model/' . $class_name . '.php');
        }else{
            exit();
        }
    });

    function getData($controller, Request $request, Response $response, $args){
        $query_str = $request->getQueryParams();
        $body = !empty($request->getParsedBody) ? $request->getParsedBody() : array();
        $params = array_merge($args, $query_str, $body);

        $new_response = $response->withJson($controller->getData($params), http_response_code());
        return $new_response;
    }

    $config['db']['host']   = getenv('OPENSHIFT_MYSQL_DB_HOST');
    $config['db']['user']   = getenv('OPENSHIFT_MYSQL_DB_USERNAME');
    $config['db']['pass']   = getenv('OPENSHIFT_MYSQL_DB_PASSWORD');
    $config['db']['dbname'] = getenv('OPENSHIFT_APP_NAME');
    $app = new \Slim\App(["settings" => $config]);
    
    $container = $app->getContainer();
    $container['db'] = function ($c) {
        $db = $c['settings']['db'];
        try{
            $pdo = new PDO('mysql:host=' . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        }catch(PDOException $e){
            exit();
        }
    };
    $app->group('/api', function(){
        $this->group('/v1', function(){
            $this->get('/rooms[/[{id}[/]]]', function (Request $request, Response $response, $args) {
                $room_repo = new RoomPdoRepository($this->db);
                $controller = new RoomController($room_repo);

                return getData($controller, $request, $response, $args);
            });

            $this->patch('/rooms/{id}[/]', function (Request $request, Response $response, $args) {
                $room_repo = new RoomPdoRepository($this->db);
                $controller = new RoomController($room_repo);

                $query_str = $request->getQueryParams();
                $body = $request->getParsedBody();
                $params = array_merge($body, $args);

                if(!empty($query_str) || empty($body)){
                    $new_response = $response->withStatus(400);
                }else{
                    $room = new Room($params);
                    $new_response = $response->withJson($controller->patchData($room), http_response_code());
                }

                return $new_response;
            });

            $this->get('/branches[/[{id}[/]]]', function (Request $request, Response $response, $args) {
                $room_repo = new BranchPdoRepository($this->db);
                $controller = new BranchController($room_repo);

                return getData($controller, $request, $response, $args);
            });

            $this->get('/companies[/[{id}[/]]]', function (Request $request, Response $response, $args){
                $comp_repo = new CompanyPdoRepository($this->db);
                $controller = new CompanyController($room_repo);

                return getData($controller, $request, $response, $args);
            });
        });
    });
    $app->run();
?>
