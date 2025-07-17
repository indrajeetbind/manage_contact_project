<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactCustomField extends Model
{
    protected $table = 'contact_custom_fields';

    protected $fillable = ['label', 'type'];

    public function values()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }
}
