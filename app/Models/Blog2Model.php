<?php
namespace App\Models;

use CodeIgniter\Model;

class Blog2Model extends Model{
  protected $table = 'blog2';
  protected $primaryKey = 'post_id';
  protected $allowedFields = ['post_title','post_description','post_featured_image'];
}