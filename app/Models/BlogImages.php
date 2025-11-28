<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogImages extends Model
{
    use HasFactory;

    protected $table = 'blog_images';

    protected $fillable = ['blog_id', 'src', 'title', 'alt'];

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id', 'idBlog');
    }
}
