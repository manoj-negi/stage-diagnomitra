<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    use HasFactory;
    protected $table = 'labs_tests';
    protected $fillable = [
        'test_name', 
        'hospital_id', 
        'admin_status', 
        'amount',
        'description',
        'created_at',
        'updated_at'
    ];



 public function getTitleAttribute($value)
    {
        return ucfirst($value);
    }
    public function getDescriptionAttribute($value)
    {
        return ucfirst($value);
    }

    public function labname()
    {
        return $this->hasOne(User::class, 'id', 'hospital_id');
    }

}