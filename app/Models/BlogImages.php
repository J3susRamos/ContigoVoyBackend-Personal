<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BlogImages extends Model
{
    use HasFactory;

    protected $table = 'blog_images';
    protected $primaryKey = 'id';

    protected $fillable = [
        'blog_id',
        'src',
        'title',
        'alt',
    ];

    public $timestamps = false;

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id', 'idBlog');
    }
}
