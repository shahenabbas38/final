<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabResult extends Model
{
    use HasFactory;

    protected $table = 'lab_results';
    public $timestamps = false;

    protected $fillable = [
        'lab_test_id',
        'result_date',
        'value_numeric',
        'unit',
        'ref_range',
        'attachment_url'
    ];

    public function labTest()
    {
        return $this->belongsTo(\App\Models\LabTest::class, 'lab_test_id');
    }
}
