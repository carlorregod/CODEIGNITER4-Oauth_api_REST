<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Blog extends ResourceController
{
	protected $modelName = 'App\Models\BlogModel';
	protected $format = 'json';

	public function index(){
		$posts = $this->model->findAll();
		return $this->respond($posts);
	}

	public function create(){
		helper(['form']);
		//Para casos que no se pase un formulario sino un json
		if(!is_null($this->request->getJson()))
			$_REQUEST = (array)($this->request->getJson());

		$rules = [
			'title' => 'required|min_length[6]',
			'description' => 'required',
		];

		if(!$this->validate($rules)){
			return $this->fail($this->validator->getErrors());
		}else{

			$data = [
				'post_title' => $this->request->getVar('title'),
				'post_description' => $this->request->getVar('description')
			];

			$post_id = $this->model->insert($data);
			$data['post_id'] = $post_id;
			return $this->respondCreated($data);
		}
	}

	public function show($id = null){
		$data = $this->model->find($id);
		return $this->respond($data);
	}

	public function update($id = null){


		helper(['form', 'array']);
		if(!is_null($this->request->getJson()))
			$input  = (array)$this->request->getJson(); //Desde json
		elseif(!is_null($this->request->getRawInput()))
			$input  = $this->request->getRawInput(); //Desde formulario
		else
			$input=['title' => '','description' => ''];

		$validation =  \Config\Services::validation();

		$validation->setRules([
			'title' => 'required|min_length[6]',
			'description' => 'required',
		]);

		if(!$validation->run($input)){
			return $this->fail($validation->getErrors());
		}else{
			$input = $this->request->getRawInput();

			$data = [
				'post_id' => $id,
				'post_title' => $input['title'],
				'post_description' => $input['description'],
			];

			$this->model->save($data);
			return $this->respond($data);
		}

	}

	public function delete($id = null){
		$data = $this->model->find($id);
		if($data){
			$this->model->delete($id);
			return $this->respondDeleted($data);
		}else{
			return $this->failNotFound('Item not found');
		}
	}

}