<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactCustomFieldValue extends Model
{
    protected $table = 'contact_custom_field_values';

    protected $fillable = ['contact_id','custom_field_id','value'];

    public function field()
    {
        return $this->belongsTo(ContactCustomField::class, 'custom_field_id');
    }

}
