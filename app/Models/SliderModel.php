<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Str;
class SliderModel extends Model
{
    public $timestamps=false;
    protected $table='slider';
    private $folderUpload = 'slider';
    protected $primaryKey='id';
    protected $fieldSearchAccepted=[
        'id','name','description','link'
    ];
    protected $crudNotAccepted=[
        '_token','thumb_current'
    ];
    const UPDATED_AT = 'modified';
    public function listItems($params,$options){
        $re= null;
        if ($options['task']=='admin-list-items'){
            $query= $this->select('id','name','description','link','thumb','created','created_by','modified','modified_by','status');
                
            if( $params['filter']['status']!=='all'){
                $query->where('status','=',$params['filter']['status']);
            }
            if ($params['search']['value']!==''){
                if($params['search']['field']=='all'){
                    $query->where(function($qr) use ($params){
                        foreach($this->fieldSearchAccepted as $col){
                            $qr->orwhere($col,'LIKE',"%{$params['search']['value']}%");
                        }
                    });
                }else if (in_array($params['search']['field'],$this->fieldSearchAccepted)){
                    $query->where($params['search']['field'],'LIKE',"%{$params['search']['value']}%");
                }
            }
            $re=$query->orderBy('id')->paginate($params['pagination']['totalItemPerPage']);
        }
        return $re;
    }
    public function getItem($params,$options){
        $result=null;
        if ($options['task']=='get-item'){
            $result=self::select('id','name','description','link','thumb','status')->where('id',$params['id'])
            ->first()
            ->toArray();
        }
        
        return $result;
    }
    public function countItems($params,$options){
        $re= null;
        if ($options['task']=='admin-count-items-group-by-status'){
            $query= $this->select('status', DB::raw('count(*) as count'));
            if ($params['search']['value']!==''){
                if($params['search']['field']=='all'){
                    $query->where(function($qr) use ($params){
                        foreach($this->fieldSearchAccepted as $col){
                            $qr->orwhere($col,'LIKE',"%{$params['search']['value']}%");
                        }
                    });
                }else if (in_array($params['search']['field'],$this->fieldSearchAccepted)){
                    $query->where($params['search']['field'],'LIKE',"%{$params['search']['value']}%");
                }
            }

            $re=$query->groupBy('status')->get()->toArray();
        }
        return $re;
    }
    public function saveItem($params,$options){
        
        if($options['task']=='change-status'){
            $status=$params['currentStatus']=='active'?'inactive':'active';
            self::where('id', $params['id'])
            ->update(['status' => $status]);
        }
        if($options['task']=='edit-item'){
            
        }
        if($options['task']=='add-item'){
            $thumb = $params['thumb'];
            $params['thumb'] =Str::random(10).'.'.$thumb->clientExtension();
            $thumb->storeAs($this->folderUpload, $params['thumb'],'zvn_storage_img');
            $params= array_diff_key($params,array_flip($this->crudNotAccepted));
            self::insert($params);
        }
    }
    public function deleteItem($params,$options){
        if($options['task']=='delete-item'){
            self::where('id', $params['id'])
            ->delete();
        }
    }
}
