<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverCompany extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_delivery_company';
    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';
    /**
     * @var array
     */
    protected $fillable = ['name', 'guid', 'created_at', 'updated_at'];
}
