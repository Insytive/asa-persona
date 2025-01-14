<?php

namespace App\Models;

use App\Traits\Auditable;
use betterapp\LaravelDbEncrypter\Traits\EncryptableDbAttribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \DateTimeInterface;
use Illuminate\Support\Facades\DB;

class Lead extends Model
{
    use Auditable, EncryptableDbAttribute, HasFactory;

    public $table = 'leads';

    public static $searchable = [
        'first_name',
        'last_name',
    ];

    protected $encryptable = [
//        'address',
//        'building',
//        'lead_email'
    ];

    const GENDER_SELECT = [
        '1' => 'Male',
        '2' => 'Female',
    ];

    const FIRST_TIME_VOTER_RADIO = [
        '1' => 'Yes',
        '0' => 'No',
    ];

    protected $dates = [
        'birthdate',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    const IS_MEMBER = [
        '0' => 'false',
        '1' => 'true',
    ];

    const LEAD_STATUS_SELECT = [

        '0' => 'Inactive',
        '1' => 'Active',

    ];

    protected $fillable = [
        'first_name',
        'last_name',
        'birthdate',
        'gender',
        'lead_email',
        'address',
        'building',
        'town',
        'city',
        'reported_station',
        'first_time_voter',
        'is_active',
        'is_member',
        'id_number',
        'phone',
        'created_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'province_id',
        'station_id',
        'user_id',
        'member_id',
        'employee_id',
        'volunteer_id',
    ];

       /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id_number',
        'user_id',
        'member_id',
        'employee_id',
        'volunteer_id'
    ];

    public static function totalLeadsByProvince()
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as lead_count, provinces.name, provinces.id' ))
            ->join('stations', 'stations.id', '=', 'leads.station_id')
            ->join('wards', 'wards.id', '=', 'stations.ward_id')
            ->join('areas', 'areas.id', '=', 'wards.area_id')
            ->join('municipalities', 'municipalities.id', '=', 'areas.municipality_id')
            ->join('provinces', 'provinces.id', '=', 'municipalities.province_id')
            ->groupBy('provinces.id')
            ->get();
    }

    public static function totalSelfRegistered()
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as lead_count' ))
            ->whereNull('created_by')
            ->get();
    }

    public static function totalRecruited()
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as lead_count' ))
            ->whereNotNull('created_by')
            ->get();
    }

    public static function totalRecruitedByPeriod($period_start, $period_end)
    {
        return DB::table('leads')
            ->select(DB::raw('count(*) as lead_count' ))
            ->whereNotNull('created_by')
            ->where('created_at', '>', $period_start)
            ->where('created_at', '<', $period_end)
            ->get();
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getBirthdateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format(config('panel.date_format')) : null;
    }

    public function setBirthdateAttribute($value)
    {
        $this->attributes['birthdate'] = $value ? Carbon::createFromFormat(config('panel.date_format'), $value)->format('Y-m-d') : null;
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id');
    }

    public function volunteer()
    {
        return $this->belongsTo(Volunteer::class, 'volunteer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
