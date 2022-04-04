<?php

namespace JeffersonSimaoGoncalves\Multitenancy\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use JeffersonSimaoGoncalves\Multitenancy\Traits\BelongsToTenant;

class Product extends Model
{
    use BelongsToTenant;

    public $timestamps = false;
    protected $fillable = ['name', 'tenant_id'];
}
