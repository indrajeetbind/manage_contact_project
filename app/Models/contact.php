<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class contact extends Model
{
    protected $table = 'contacts';

    protected $fillable = ['name','email','phone','gender','profile_image','additional_file'];

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }
}