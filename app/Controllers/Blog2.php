<?php namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Blog2 extends ResourceController
{
	protected $modelName = 'App\Models\Blog2Model';
	protected $format = 'json';

	public function index(){
		$posts = $this->model->findAll();
		return $this->respond($posts);
	}

	public function create(){
		helper(['form']);
		$mover = false;
		//Para casos que no se pase un formulario sino un json
		if(!is_null($this->request->getJson())){
			$_REQUEST = (array)($this->request->getJson());
			if(file_exists($_REQUEST['featured_image'])){
				//En rigor lo se que necesita es mover ese archivo(copiándolo) dentro de CI
				$mover = true;
			}
		}

		$rules = [
			'title' => 'required|min_length[6]',
			'description' => 'required'

		];
		if(!$mover)
			$rules['featured_image']='uploaded[featured_image]|max_size[featured_image, 2048]|is_image[featured_image]';

		if(!$this->validate($rules)){
			return $this->fail($this->validator->getErrors());
		}else{
			//Get the file if dont move
			if(!$mover){
				$file = $this->request->getFile('featured_image');
				if(! $file->isValid())
					return $this->fail($file->getErrorString());

				$file->move('./assets/uploads');

				$data = [
				'post_title' => $this->request->getVar('title'),
				'post_description' => $this->request->getVar('description'),
				'post_featured_image' => $file->getName()
				];

			}else{
				if(!file_exists($_REQUEST['featured_image'])){
					return $this->fail(['error'=>'Ruta ingresada indica que arhcivo no existe']);
				}else{
					//En realidad no se carga el archivo, se copia
					copy($_REQUEST['featured_image'], './assets/uploads/'.trim(basename($_REQUEST['featured_image'].PHP_EOL)));

					$data = [
					'post_title' => $this->request->getVar('title'),
					'post_description' => $this->request->getVar('description'),
					'post_featured_image' => trim(basename($_REQUEST['featured_image'].PHP_EOL))
					];

				}
			}

			$post_id = $this->model->insert($data);
			$data['post_id'] = $post_id;
			return $this->respondCreated($data);
		}
	}

	public function show($id = null){
		$data = $this->model->find($id);
		return $this->respond($data);
	}
	//Se accede por POST blog2/update/{id}
	public function update($id = null){

		helper(['form', 'array']);
		$mover = false; //Si no se actualiza de formulario, es que es un archivo desde url local
		//Para casos que no se pase un formulario sino un json
		if(!is_null($this->request->getJson())){
			$_REQUEST = (array)($this->request->getJson());
			if(isset($_REQUEST['featured_image'])){
				if(file_exists($_REQUEST['featured_image'])){
					//En rigor lo se que necesita es mover ese archivo(copiándolo) dentro de CI
					$mover = true;
				}
			}
		}

		$rules=[
			'title' => 'required|min_length[6]',
			'description' => 'required',
		];

		if(!$mover){
			$fileName = dot_array_search('featured_image.name', $_FILES);

			if($fileName != ''){
				$img = ['featured_images' => 'uploaded[featured_image]|max_size[featured_image, 1024]|is_image[featured_image]'];
				$rules = array_merge($rules, $img);
			}
		}

		if(!$this->validate($rules)){
			return $this->fail($validation->getErrors());
		}else{

			$data = [
				'post_id' => $id,
				'post_title' => $this->request->getVar('title'),
				'post_description' => $this->request->getVar('description'),
			];

			if(!$mover){ //Si es un update esde formulario
				if($fileName != ''){
					$file = $this->request->getFile('featured_image');
					if(! $file->isValid())
						return $this->fail($file->getErrorString());
					$file->move('./assets/uploads');
					$data['post_featured_image'] = $file->getName();
				}
			}else{ //Si es un movimiento desde un url ingresado
				if(!file_exists($_REQUEST['featured_image'])){
					return $this->fail(['error'=>'Ruta ingresada indica que arhcivo no existe']);
				}else{
					//En realidad no se carga el archivo, se copia
					copy($_REQUEST['featured_image'], './assets/uploads/'.trim(basename($_REQUEST['featured_image'].PHP_EOL)));
					$data['post_featured_image']=trim(basename($_REQUEST['featured_image'].PHP_EOL));
				}
			}


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



	//--------------------------------------------------------------------

}