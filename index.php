<?php 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

require 'vendor/autoload.php';
require 'Models/User.php';
require 'Models/Post.php';

function simple_encrypt($text,$salt){  
   return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
}
 
function simple_decrypt($text,$salt){  
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
}

$app = new \Slim\Slim();
$app->enc_key = '1234567891234567';

/*
$app->config('databases', [
    'default' => [
        'driver'    => 'mysql',
        'host'      => 'us-cdbr-iron-east-03.cleardb.net',
        'database'  => 'heroku_1b8d86b68669138',
        'username'  => 'beaffd044d7f83',
        'password'  => '7ef59720',
        'charset'   => 'utf8',
        'collation' => 'utf8_general_ci',
        'prefix'    => ''
    ]
]);
*/

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
$app->add(new \Slim\Middleware\ContentTypes());

$app->options('/(:name+)', function() use ($app) {
    $app->render(200,array('msg' => 'API PROG4'));
});

$app->get('/', function () use ($app) {
	$app->render(200,array('msg' => 'API PROG4'));
});

$app->post('/login', function () use ($app) {
	$input = $app->request->getBody();

	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}

	$db = $app->db->getConnection();
	$user = $db->table('usuarios')->select()->where('email', $email)->first();

	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'user not exist',
        ));
	}

	if($user->password != $password){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password dont match',
        ));
	}

	$token = simple_encrypt($user->id, $app->enc_key);	

	$app->render(200,array('token' => $token));
});

$app->get('/me', function () use ($app) {

	$token = $app->request->headers->get('auth-token');

	if(empty($token)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}

	$id_user_token = simple_decrypt($token, $app->enc_key);

	$user = User::find($id_user_token);
	if(empty($user)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'Not logged',
        ));
	}
	$app->render(200,array('data' => $user->toArray()));
});

$app->get('/usuario', function () use ($app) {
	$db = $app->db->getConnection();
	$users = $db->table('usuarios')->select('id', 'name')->get();

	$app->render(200,array('data' => $users));
});

$app->post('/usuario', function () use ($app) {
	$input = $app->request->getBody();

	$name = $input['name'];
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $input['email'];
	if(empty($email)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'email is required',
        ));
	}

    $user = new User();
    $user->name = $name;
    $user->password = $password;
    $user->email = $email;
    $user->save();

    $app->render(200,array('data' => $user->toArray()));
});

$app->put('/usuario/:id', function ($id) use ($app) {
	$input = $app->request->getBody();
	
	$name = $input['name'];
	if(empty($name)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'name is required',
        ));
	}
	$password = $input['password'];
	if(empty($password)){
		$app->render(500,array(
			'error' => TRUE,
            'msg'   => 'password is required',
        ));
	}
	$email = $input['email'];
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
    $user->name = $name;
    $user->password = $password;
    $user->email = $email;
    $user->save();
    $app->render(200,array('data' => $user->toArray()));
});

$app->get('/usuario/:id', function ($id) use ($app) {
	$db = $app->db->getConnection();
	$user = User::find($id);
	if(empty($user)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'user not found',
        ));
	}
	unset($user->password);
	unset($user->email);

	$user->posts = $db->table('posts')->select('title')->where('id_usuario', $user->id)->get();

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

$app->get('/post/:id', function ($id) use ($app) {
	$db = $app->db->getConnection();
	$post = Post::find($id);
	if(empty($post)){
		$app->render(404,array(
			'error' => TRUE,
            'msg'   => 'post not found',
        ));
	}

	/*
	$post->user = User::find($post->id_usuario);
	*/

	$post->user = $db->table('usuarios')->select('id','name', 'email')->where('id', $post->id_usuario)->get();

	unset($post->id_usuario);
	
	$app->render(200,array('data' => $post->toArray()));
});

$app->run();
?>