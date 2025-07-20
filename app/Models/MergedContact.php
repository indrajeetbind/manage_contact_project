<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MergedContact extends Model
{
    protected $table= 'merged_contacts';
    protected $fillable = ['master_contact_id','to_contact_id','contact_id','merged_at'];

    // who was merged
    public function mergedContact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    // merged with which contact
    public function destinationContact()
    {
        return $this->belongsTo(Contact::class, 'to_contact_id');
    }

    public function masterContact()
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

}
