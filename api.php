<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

Class API {
	public $result;
	public $db;
	public $app;
	public $collections;
	//Database object.
	public $col;
	//Current Collection object.
	function __construct($conf =  null) {
		
		if (!is_null($conf)) {
			if (isset($conf->collections)) {
				$this->collections = $conf->collections;
			}
		}
		/**Setup result schema**/
		$result = new StdClass();
		$result->error = 0;
		$result->message = 'Success';
		$result->data = array();
		$this->result = $result;

		/**Connect to Mongodb**/
		$m = new MongoClient();
		$db = $m->any;
		$this->db = $db;

		/**Setup routing**/
		$this->routing();
	}

	private function routing() {
		$app =  new \Slim\Slim();
		$api = $this;
		$this->req = $app->request();
	
		$app->get('/api/:col', function($col)  use ($api) {
			$api->setCollection($col);
			$api->find();
		});

		$app->post('/api/add/:col', function($col) use ($api) {
			$api->setCollection($col);
			$api->add();
		});

		$app->post('/api/update/:col', function($col) use ($api) {
			$api->setCollection($col);
			$api->updateWhere();
		});

		$app->get('/api/remove/:col', function($col) use ($api) {
			$api->setCollection($col);
			$api->removeWhere();
		});

		$app->get('/api/remove/:col/:id', function($col, $id) use ($api) {
			$api->setCollection($col);
			$api->deleteById($id);
		});

		$app->get('/api/:col/:id', function($col, $id) use ($api) {
			$api->setCollection($col);
			$api->getById($id);
		});

		$app->post('/api/update/:col/:id', function($col, $id) use ($api) {
			$api->setCollection($col);
			$api->updateById($id);
		});

		$this->app = $app;
	}

	public function setCollection($col) {
		$this->allowedCollection($col);
		$this->col = $this->db->{$col};
	}

	private function resp() {
		return json_encode($this->result);
	}

	private function allowedCollection($col){
		if (is_array($this->collections)){
			if (!array_key_exists($col, $this->collections) && !in_array($col, $this->collections)){
				header('HTTP/1.0 403 Forbidden');
				exit();
			}
		}
	}

	/**BEGIN DB WORK**/
	public function find (){
		$params = $this->req->get();
		$res = $this->col->find($params);
		$results = array();
		foreach ($res as $x) {
			$results[] = $x;
		}
		returnJSON($results);
	}

	public function add(){
		$params = $this->req->get();
		$data = json_decode($this->req->getBody());
		$res = $this->col->insert($data);
		if ($res) {
			returnJSON($data);
		} else
		{
			returnJSON ($res);
		}
	}
	public function update (){
		$params = $this->req->get();
		$data = $this->req->put();
		returnJSON($this->col->update($params), $data);
	}

	public function delete (){
		$params = $this->req->delete();
		returnJSON($this->col->remove($params, array("justOne" => true)));
	}
	public function getByID ($id){
		returnJSON($this->col->find(array('_id'=>new MongoId($id) )));
	}

	public function updateByID ($id){
		$params = $this->req->get();
		$data = $this->req->put();
		returnJSON($this->col->update(array('_id'=>new MongoId($id)), $data));
	}

	public function deleteByID ($id){
		$mID = new MongoId((String)$id);
		$criterea = array('_id' => $mID);
		returnJSON($this->col->remove($criterea));
	}

	public function run() {
		$this->app->run();
	}
}

function returnJSON ($data) {
	header('Content-Type: application/json');
	echo json_encode($data);
	exit;
}
