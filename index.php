<?php 
require 'vendor/autoload.php';
require 'Models/User.php';

$app = new \Slim\Slim();

$app->config('databases', [
    'default' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'api-prog4',
        'username'  => 'api-prog4',
        'password'  => 'api-prog4',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => ''
    ]
]);
$app->add(new Zeuxisoo\Laravel\Database\Eloquent\ModelMiddleware);

$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());

$app->get('/usuario', function () use ($app) {
	$db = $app->db->getConnection();
	$users = $db->table('users')->select('id', 'name')->get();

	$app->render(200,array('data' => $users));
});

$app->put('/usuario', function () use ($app) {
	$name = $app->request->params('name');
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $app->request->params('password');
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $app->request->params('email');
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}

    $user = new User();
    $user->name = $app->request->params('name');
    $user->password = $app->request->params('password');
    $user->email = $app->request->params('email');
    $user->save();

    $app->render(200,array('data' => $user->toArray()));
});

$app->post('/usuario/:id', function ($id) use ($app) {
	$name = $app->request->params('name');
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $app->request->params('password');
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $app->request->params('email');
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}

	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
    $user->name = $app->request->params('name');
    $user->password = $app->request->params('password');
    $user->email = $app->request->params('email');
    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});

$app->get('/usuario/:id', function ($id) use ($app) {
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	$app->render(200,array('data' => $user->toArray()));
});

$app->delete('/usuario/:id', function ($id) use ($app) {
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}

	$user->delete();
	$app->render(200);
});

$app->run();
?>