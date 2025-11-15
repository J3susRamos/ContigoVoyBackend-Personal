<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogMetadata extends Model
{
    use HasFactory;

    protected $table = 'blog_metadata';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
    'blog_id',
    'metaTitle',
    'metaDescription',
    'keywords',
    ];


    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class, 'blog_id', 'idBlog');
    }
}
