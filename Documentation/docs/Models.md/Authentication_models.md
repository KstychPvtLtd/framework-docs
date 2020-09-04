
## KstychUser

This model Extend Properties from Builtin Laravel User Model
```
<?php namespace App\Models;

use App\Kstych\Models\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

use Crypt;
use Config;
use App\Models\ACL;
use App\Models\Role;
use App\Models\Sipid;
use App\Kstych\Models\KModelTrait;

class KstychUser extends Authenticatable
{

  protected $table = 'users';
  protected $hidden = ['password','remember_token'];
  //protected $fillable = array('username','password','email','status','organization','group','data');

  use SoftDeletes;
  protected $dates = ['deleted_at'];

  use KModelTrait;

  public $dataarr=[];
  public $metaarr=[];
  protected $roleobj=[];
  public $sipid=null;
  protected $objArr=[];
  public $__acllock=true;
  protected $user=null;
  protected $gates=[];

  public function user()
  {
    if($this->user==null)$this->user=User::find($this->id);
    return $this->user;
  }

  public function setDebug()
  {
    if($this->moduleACL('Admin',true,true,true))
    {
      Config::set('app.debug',Config::get('kstych.config.admindebug'));
    }
  }

  public function roleobj()
  {
    if(empty($this->roleobj))
    {
      $this->__acllock=false;
      $this->roleobj=$this->roles()->with('modules','groups')->get();

      foreach($this->roleobj as $role)
      {
        $role->roleAccess('module','','_');
        foreach($role->modules as $module)
        {
          if(!isset($this->gates[$module->module]))$this->gates[$module->module]=[];
          $this->gates[$module->module]=array_filter(array_merge($this->gates[$module->module],explode(',',$module->getData('gates',''))));
        }
      }
      $this->__acllock=true;
    }

    return $this->roleobj;
  }

  public function getMeta($key,$value=null)
  {
    if(empty($this->metaarr))$this->metaarr=json_decode($this->meta,true);
    return $this->getObj('\App\Kstych\Helper\KHelper')->getArr($this->metaarr,$key,$value);
  }
  public function setMeta($key,$value)
  {
    if(empty($this->metaarr))$this->metaarr=json_decode($this->meta,true);
    $this->metaarr=$this->getObj('\App\Kstych\Helper\KHelper')->setArr($this->metaarr,$key,$value);
    $this->meta=json_encode($this->metaarr);
  }


  public function moduleACL($module,$read,$write,$admin)
  {
    return $this->loadACL('module',$module,$read,$write,$admin);
  }
  public function groupACL($group,$read,$write,$admin)
  {
    return $this->loadACL('group',$group,$read,$write,$admin);
  }
  protected function loadACL($type,$val,$read,$write,$admin)
  {
    $roles = $this->roleobj();

    foreach($roles as $role)
    {
      $nread=($role->roleAccess($type,'Read',$val))&&$read;
      $nwrite=($role->roleAccess($type,'Write',$val))&&$write;
      $nadmin=($role->roleAccess($type,'Admin',$val))&&$admin;

      if($nread==$read&&$nwrite==$write&&$nadmin==$admin)return true;
    }

    return false;
  }

  public function getAccessList($type,$read,$write,$admin,$empty=false)
  {
    $roles = $this->roleobj();

    $qstr=[];
    foreach($roles as $role)
    {
      $qstr=array_merge($qstr,array_keys($role->getAcl($type)));
    }

    if(empty($qstr)&&!$empty)$qstr[]='__undefined__';
    return $qstr;
  }

  public function modelAcls($model,$action='')
  {
    $res=Config::get("runtime.ACL.Model-$model.".$this->id,null);
    if($res===null)
    {
      $res=[];
      $acls=Config::get("runtime.ACL.Model-$model",null);
      if($acls===null)
      {
        $acls=ACL::where("model","=",$model)->where('status','=','Active')->get();
        Config::set("runtime.ACL.Model-$model",$acls);
      }

      foreach($acls as $acl)
      {
        $matrix=$acl->getData('matrix',[]);
        foreach($matrix as $i=>$line)
        {
          if(empty($line[0]))continue;

          $key=explode('.',$line[0]);
          $actions=array_filter(explode(',',$line[3]??''));
          foreach($actions as $act)
          {
            if(!isset($res[$act]))$res[$act]=['blacklist'=>[],'whitelist'=>[]];

            //User,Profile,Subscription,Role,Module,SubModule,Gate
                if($key[1]=='User')$uservalue=[$this->getData($key[2])];
            elseif($key[1]=='Profile')$uservalue=[optional($this->profile)->getData($key[2],'')??''];
            elseif($key[1]=='Subscription')$uservalue=$this->subscriptions->pluck($key[2])->toArray();
            elseif($key[1]=='Role')$uservalue=$this->roleobj()->pluck($key[2])->toArray();
            elseif($key[1]=='Module')$uservalue=$this->getAccessList('module',($key[2]=='Read')?true:false,($key[2]=='Write')?true:false,($key[2]=='Admin')?true:false);
            elseif($key[1]=='SubModule')$uservalue=[];
            elseif($key[1]=='Gate')$uservalue=$this->gates[$key[2]]??[];

            if($line[1]=='__VAL__')$line[1]=implode(',',$uservalue);
            if($line[5]=='__VAL__')$line[5]=$line[1];

            $pass=false;
            if($key[0]=='IN'&&!empty(array_intersect(json_decode($line[1],true),$uservalue)))$pass=true;
            if($key[0]=='NotIN'&&empty(array_intersect(json_decode($line[1],true),$uservalue)))$pass=true;

            if($pass)
            {
              $modelkey=explode('.',$line[4]);
              if(empty($modelkey[1]))$modelkey[1]='id';
              if(empty($line[5]))$line[5]='["*"]';
              $res[$act][$line[2]][$acl->id.'.'.$i]=[$modelkey[1],json_decode($line[5],true),$modelkey[0]];
            }
          }
        }
      }
      Config::set("runtime.ACL.Model-$model.".$this->id,$res);
    }

    if($action=='')return $res;
    else if(isset($res[$action]))return $res[$action];
    else return ['blacklist'=>[],'whitelist'=>[]];
  }
  public function modelAclsCheck($model,$action,$obj)
  {
    $aclfail=[];
    $acls=$this->modelAcls($model,$action);
    if(!empty($acls['whitelist']))
    {
      $wfail=[];$wpass=[];
      foreach($acls['whitelist'] as $aclid=>$l)
      {
        if($l[1][0]=='*')$l[1][0]=$obj->{$l[0]};

        if($l[2]=='IN')if(in_array($obj->{$l[0]},$l[1]))$wpass[]=$aclid;else $wfail[]=$aclid;
        if($l[2]=='NotIN')if(!in_array($obj->{$l[0]},$l[1]))$wpass[]=$aclid;else $wfail[]=$aclid;
      }
      if(empty($wpass))$aclfail=array_merge($aclfail,$wfail);//Any of the whitelist
    }
    if(!empty($acls['blacklist']))
    {
      foreach($acls['blacklist'] as $aclid=>$l)
      {
        if($l[1][0]=='*')$l[1][0]=$obj->{$l[0]};

        //none of the blacklist
        if($l[2]=='IN')if(in_array($obj->{$l[0]},$l[1]))$aclfail[]=$aclid;
        if($l[2]=='NotIN')if(!in_array($obj->{$l[0]},$l[1]))$aclfail[]=$aclid;
      }
    }

    return $aclfail;
  }

  public function gate($module,$gate)
  {
    return in_array($gate,$this->gates[$module]??[])&&in_array($gate,Config::get("kstych.modules.$module.gates",[]));
  }

  public function configModules()
  {
    $configmodules=[];
    foreach(Config::get("kstych.modules",[]) as $mname=>$module)
    {
      if($this->moduleACL($mname,true,false,false))
      {
          $configmodules[$mname]=$module;
      }
    }
    return $configmodules;
  }
  public function dispname($maxlen=0)
  {
    $retval=explode(' ',$this->fullname)[0];
    if(empty($retval))$retval=explode('@',$this->username)[0];

    if($maxlen>0)return substr(trim($retval),0,$maxlen);
    else return trim($retval);
  }

  public function fetchphoto($id=null,$type='files')
  {
    if(empty($id)){$user=$this->user();}else $user=User::find($id);

    if(!$user)return '';

    return $user->getFileLink('photo',$type);
  }
  public function fetchphotothumb($id=0)
  {
    return $this->fetchphoto($id,'thumbs');
  }

  public function getObj($class)
  {
    if(!isset($this->objArr[$class]))
    {
      $this->objArr[$class]=new $class();
    }
    return $this->objArr[$class];
  }

  public function tzdate($datetime,$fmt='Y-m-d H:i:s')
  {
    $time = strtotime($datetime)-($this->timezone*60);
    if($fmt=='TS')return $time;
    else
    {
      if($fmt=='SOCIAL')
      {
        $fmt='M j, Y \\a\\t g:i A';
        if ($time >= strtotime('today 00:00'))$fmt='\\T\\o\\d\\a\\y \\a\\t g:i A';
        else if ($time >= strtotime('yesterday 00:00'))$fmt='\\Y\\e\\s\\t\\e\\r\\d\\a\\y \\a\\t g:i A';
        else if ($time >= strtotime('-6 day 00:00'))$fmt='l \\a\\t g:i A';
      }

      return date($fmt, $time);
    }
  }

  public function getSipid()
  {
    if($this->roleobj()->isEmpty())return 'No Roles for User. Login Failed. <a href="'.url('/').'/auth?action=logout">Logout</a>';

    //TODO later user may be bound to a specific server for sip

    $cliarr=[];$cliarr['did']=[];if($this->exten!='')$cliarr['did'][]=$this->exten;

//     TODO disabling for later dialer implementation
//     if(!empty($allclients))foreach($allclients as $tclnt)
//     {
//       if(!empty($mastersdata['DialerDID']))$cliarr['did'][]=$mastersdata['DialerDID'];
//     }

    if(Config::get('kstych.config.APP_Multiple_Logins')=='no'&&$this->id!=1)
    {
      if($this->presence>0)return 'Already Logged In.  <a href="'.url('/').'/auth?action=logout">Logout</a>';
    }
    $cliarr['keepconf']=Config::get('kstych.config.kDialer_keeplocalconf');
    //get a free sip id
    $sipid=Sipid::where('status','=','0')->where('updated_at','<=',date('Y-m-d H:i:s',time()-600))->orderBy('updated_at','asc')->where('server','!=','');
    //$server=explode(':',$this->exten);
    //if(isset($server[2]))$sipid=$sipid->where('server','=',$server[2]);
    $sipid=$sipid->first();
    if(!$sipid){if($this->id!=1)return 'Error : Cant Allocate Data Channel. Giving Up.  <a href="'.url('/').'/auth?action=logout">Logout</a>';else return '';}
    $sipid->setData('config',$cliarr,false);
    $sipid->user_id=$this->id;
    $sipid->save();

    //layout data
    $this->sipid=$sipid;
    return '';
  }

  public function action($action,$arr=[])
  {
    if($action=='resetloginattempt')
    {
      $this->setMeta('pwd_array',[]);
      $this->setMeta('otp_array',[]);
    }
    if($action=='newloginattempt')
    {
      $this->setMeta('pwd_array.'.date('Ymd'),$this->getMeta('pwd_array.'.date('Ymd'),0)+1);
      return $this->getMeta('pwd_array.'.date('Ymd'));
    }
  }

  public function getToken($options=[])
  {
    $data=[];
    $data['id']=$this->id;
    $data['ts']=time();$data['ts']+=$options['ts']??(24*60*60);
    if(isset($options['token']))$data['token']=$options['token'];
    $token=Crypt::encrypt(json_encode($data));
    return $token;
  }

  public function igroup()
  {
    return $this->belongsTo('App\Models\Group','igroup_id','id');
  }
  public function lang()
  {
    return $this->belongsTo('App\Models\Lang','lang_id','id');
  }
  public function roles()
  {
    $pivotmodel=(new Builder())->make('UserRole');
    $pivotschema=(new $pivotmodel)->getModelData()['kschema'];
    $filedsarr=array_keys($pivotschema);

    return $this->belongsToMany('App\Models\Role', 'userroles', 'user_id', 'role_id')->whereNull('userroles.deleted_at')->as('UserRole')->withTimestamps()->withPivot($filedsarr)->using('App\Models\UserRole');
  }
  public function manager()
  {
    return $this->belongsTo('App\Models\User','manager_id','id');
  }
  public function acls()
  {

  }
  public function profile()
  {
    $modelpath=(new Builder())->make('Profile');
    return $this->hasOne('App\Models\Profile', 'user_id', 'id');
  }
  public function subscriptions()
  {
    $pivotmodel=(new Builder())->make('UserSubscription');
    $pivotschema=(new $pivotmodel)->getModelData()['kschema'];
    $filedsarr=array_keys($pivotschema);

    return $this->belongsToMany('App\Models\Subscription', 'usersubscription', 'user_id', 'subscription_id')->whereNull('usersubscription.deleted_at')->as('UserSubscription')->withTimestamps()->withPivot($filedsarr)->using('App\Models\UserSubscription');
  }

  public function modelData()
  {
    $data=[];

    $data['config']=[];

    $data['schema']=
    [
      //[key,           type,       uitype,     display,          tab,      label,    frameset, pos,    ??,validation,  Forms,    ??,??,storage]
      ['photo',         'bigInteger','file-photo','Photo',        '',       '',       '',       '0|12|1','','',         '11|1|1|1','','','Column'],
      ['id',            'bigInteger','text',    'ID',             '',       '',       '',       '0|12|1','','',         '11|0|1|0','','','IColumn'],
      ['created_at',    'timestamp','datetime', 'Create Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['updated_at',    'timestamp','datetime', 'Update Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['deleted_at',    'timestamp','datetime', 'DeletedDate',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],

      ['username',      'string',   'text',     'User Name',      '',       '',       '',       '0|12|1','','required|unique:users,username','11|1|2|2','','unique','Column'],
      ['password',      'string',   'password', 'Password',       '',       '',       '',       '0|12|1','','min:8', '00|1|1|1','','','Column'],
      ['fullname',      'string',   'text',     'FullName',       '',       '',       '',       '0|12|1','','',         '11|1|1|1','','','Column'],
      ['email',         'string',   'email',    'Email',          '',       '',       '',       '0|12|1','','required|email','11|1|1|1','','','Column'],
      ['status',        'string',   'select',   'Status',         '',       '',       '',       '0|12|1','','',         '11|1|1|1','','index','Column'],
      ['presence',      'integer',  'text',     'Presence',       '',       '',       '',       '0|12|1','','',         '11|1|1|1','','','Column'],
      ['timezone',      'integer',  'text',     'Time Zone',      '',       '',       '',       '0|12|1','','',         '11|1|1|1','','','Column'],
      ['lang_id',       'bigInteger','rel-b1',  'Language',       '',       '',       '',       '0|12|1','','',         '11|1|1|1','','foreign','Column'],
      ['roles',         'string',   'rel-bn',   'Roles',          '',       '',       '',       '0|12|1','','',         '11|1|1|1','','','REL'],
      ['source',        'string',   'text',     'Source',         '',       '',       '',       '0|12|1','','',         '00|1|1|1','','','Column'],
      ['meta',          'longText', 'json',     'Meta',           '',       '',       '',       '0|12|1','','',         '00|1|1|1','','','Column'],
      ['remember_token','string',   'text',     'Remember Token', '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','Column'],
      ['manager_id',    'bigInteger','rel-b1',  'Manager',        '',       '',       '',       '0|12|1','','',         '00|1|1|1','','foreign','Column'],
      ['profile',       'string',   'rel-h1',   'Profile',        '',       '',       '',       '0|12|1','','',         '00|1|1|1','','','REL'],
      ['subscriptions', 'string',   'rel-bn',   'Subscriptions',  '',       '',       '',       '0|12|1','','',         '00|1|1|1','','','REL'],

      ['data',          'longText', 'json',     'Data',           '',       '',       '',       '0|12|1','','',         '00|0|0|0','','','IColumn'],
      ['igroup_id',     'bigInteger','rel-b1',  'Group',          '',       '',       '',       '0|12|1','','',         '11|0|1|0','','foreign','Column'],
    ];
    $data['relationships']=
    [
      'lang_id'=>       ['lang',        'Lang',     'lang_id,id',       '{{name}}',     []],
      'igroup_id'=>     ['igroup',      'Group',    'igroup_id,id',     '{{name}}',     []],
      'roles'=>         ['roles',       'Role',     'UserRole,userroles,user_id,role_id',  '{{name}}',     []],
      'manager_id'=>    ['manager',     'User',     'manager_id,id',    '{{username}}',     []],
      'profile'=>       ['profile',     'Profile',  'user_id,id',       '{{id}}',           []],
      'subscriptions'=> ['subscriptions','Subscription',    'UserSubscription,usersubscription,user_id,subscription_id',  '{{name}}',     []],
    ];
    $data['forms']=
    [
      'index'=>
      [
        0=> ['name'=>'Table', 'icon'=>'icon-table', 'photo'=>'photo','status'=>'status','nosearch'=>[]],
        1=> ['name'=>'Card',  'icon'=>'icon-grid',  'photo'=>'photo','status'=>'status','nosearch'=>[],'cardtitle'=>'{{name}}','cardsubtitle'=>'{{status}}'],
      ],
      'create'=>
      [
        0=>['name'=>'Create','type'=>'tab'],
      ],
      'show'=>
      [
        0=>['name'=>'Show','type'=>'tree'],
      ],
      'edit'=>
      [
        0=>['name'=>'Edit','type'=>'tab'],
      ],
    ];


    $data['values']=
    [
      'select'=>
      [
        'status'=>['Active','Unverified','Disabled','Blocked'],
      ],
    ];
    $data['actions']=[];
    $data['triggers']=[];
    $data['script']=[];


    return $data;
  }

}

```
### ACL

```
<?php namespace App\Models;

use App\Kstych\Models\KModel;

use Config;

class ACL extends KModel{

  protected $table = 'acls';


  public function igroup()
  {
    return $this->belongsTo('App\Models\Group','igroup_id','id');
  }
  public function user()
  {
    return $this->belongsTo('App\Models\User','user_id','id');
  }

  public function modelData()
  {
    $data=[];

    $data['config']=[];

    $data['schema']=
    [
      //[key,           type,       uitype,     display,          tab,      label,    frameset, pos,    ??,validation,  Forms,    ??,??,storage]
      ['id',            'bigInteger','text',    'ID',             '',       '',       '',       '0|12|1','','',         '11|0|1|0','','','IColumn'],
      ['created_at',    'timestamp','datetime', 'Create Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['updated_at',    'timestamp','datetime', 'Update Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['deleted_at',    'timestamp','datetime', 'DeletedDate',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['data',          'longText', 'json',     'Data',           '',       '',       '',       '0|12|1','','',         '00|0|0|0','','','IColumn'],
      ['igroup_id',     'bigInteger','rel-b1',  'Group',          '',       '',       '',       '0|12|1','','',         '11|0|1|0','','foreign','IColumn'],

      ['model', 'string','select','Model',  '','','','0|12|1','','','11|1|1|1','','index','Column'],
      ['status','string','select','Status', '','','','0|12|1','','','11|1|1|1','','','Column'],
      ['matrix','string','htable','Matrix', '','','','0|12|1','','','00|1|1|1','','','JSON'],

      //0=>Type (IN,EQ,NEQ,NIN) . (User,Profile,Subscription,Role,Module-Read,Module-SubModule,Module-Gate,) . option
      //1=>Value of Type CSV (admin , App , canCreate)
      //2=>Blacklist/Whitelist
      //3=>Actions
      //4=>ModelKey
      //5=>ModelValues CSV

      //IN.User.username	admin	blacklist	R	NotIN.id	231,230
      //IN.User.username	admin	blacklist	O		tab.Delete,index.Delete

      //['authkey','string','select','User Key','','','','0|12|1','','','11|1|1|1','','index','Column'],
      //['authvalue','string','text','User Value','','','','0|12|1','','','11|1|1|1','','','Column'],
      //['type','string','select','Type','','','','0|12|1','','','11|1|1|1','','','Column'],
      //['action','string','multiselect','Action','','','','0|12|1','','','11|1|1|1','','','Column'],
      //['modelkey','string','text','Key','','','','0|12|1','','','11|1|1|1','','','Column'],
      //['modelvalue','string','text','Value','','','','0|12|1','','','11|1|1|1','','','Column'],
      //['user_id','bigInteger','rel-b1','User Id','','','','0|12|1','','','11|1|1|1','','foreign','Column'],
    ];

    $data['relationships']=
    [
      'igroup_id'=> ['igroup','Group','igroup_id,id', '{{name}}',     []],
      'user_id'=>   ['user',  'User', 'user_id,id',   '{{username}}', []],
    ];

    $data['forms']=
    [
      'index'=>
      [
        0=> ['name'=>'Table', 'icon'=>'icon-table', 'photo'=>'photo','status'=>'status','nosearch'=>[]],
        1=> ['name'=>'Card',  'icon'=>'icon-grid',  'photo'=>'photo','status'=>'status','nosearch'=>[],'cardtitle'=>'{{name}}','cardsubtitle'=>'{{status}}'],
      ],
      'create'=>
      [
        0=>['name'=>'Create','type'=>'tab'],
      ],
      'show'=>
      [
        0=>['name'=>'Show','type'=>'tree'],
      ],
      'edit'=>
      [
        0=>['name'=>'Edit','type'=>'tab'],
      ],
    ];


    $data['values']=
    [
      'select'=>
      [
        //'type'=>[['whitelist','White List'],['blacklist','Black List']],
        'status'=>['Active','Disabled'],
        'model'=>'__SEL__APP_Models__',
        //'action'=>[['C','Create'],['R','Read'],['U','Update'],['D','Delete'],['O','Admin'],['F','Field'],['A','Action']],
      ],
    ];
    $data['actions']=[];
    $data['triggers']=[];
    $data['script']=[];


    return $data;
  }

}

```


### Role
```
<?php namespace App\Models;
use App\Kstych\Models\Builder;
use Illuminate\Database\Eloquent\Model;

use Auth;
use Config;
use App\Kstych\Models\KModel;

class Role extends KModel{

  protected $table = 'roles';

  private $roledataArr=[];

  protected function loadACLs()
  {
    if(empty($this->roledataArr))
    {
      $this->roledataArr['module']=[];
      $this->roledataArr['group']=[];

      $modulsarr=$this->modules;
      $groupsarr=$this->groups;
      foreach($modulsarr as $module)
      {
        $this->roledataArr['module'][strtolower($module->module)]=$module->acl;
      }
      foreach($groupsarr as $group)
      {
        $this->roledataArr['group'][$group->id]=$group->RoleGroup->acl;
      }
    }
  }
  public function getAcl($type)
  {
    $this->loadACLs();
    return $this->roledataArr[$type];
  }
  public function roleAccess($type,$acl,$str)
  {
    $this->loadACLs();

    $str=strtolower($str);
    if(!empty($this->roledataArr[$type][$str]))
    {
      if($acl=='Read' &&in_array($this->roledataArr[$type][$str],['Read','Write','Admin'] ))return true;
      if($acl=='Write'&&in_array($this->roledataArr[$type][$str],['Write','Admin']        ))return true;
      if($acl=='Admin'&&in_array($this->roledataArr[$type][$str],['Admin']                ))return true;
    }
    return false;
  }

  public function igroup()
  {
    return $this->belongsTo('App\Models\Group','igroup_id','id');
  }
  public function modules()
  {
    return $this->hasMany('App\Models\RoleModule', 'role_id', 'id');
  }
  public function groups()
  {
    $pivotmodel=(new Builder())->make('RoleGroup');
    $pivotschema=(new $pivotmodel)->getModelData()['kschema'];
    $filedsarr=array_keys($pivotschema);

    return $this->belongsToMany('App\Models\Group', 'rolegroups', 'role_id', 'group_id')->whereNull('rolegroups.deleted_at')->as('RoleGroup')->withTimestamps()->withPivot($filedsarr)->using('App\Models\RoleGroup');
  }
  public function users()
  {
    $pivotmodel=(new Builder())->make('UserRole');
    $pivotschema=(new $pivotmodel)->getModelData()['kschema'];
    $filedsarr=array_keys($pivotschema);

    return $this->belongsToMany('App\Models\User', 'userroles', 'role_id', 'user_id')->whereNull('userroles.deleted_at')->as('UserRole')->withTimestamps()->withPivot($filedsarr)->using('App\Models\UserRole');
  }
  public function acls()
  {
  }

  public function modelData()
  {
    $data=[];

    $data['config']=[];

    $data['schema']=
    [
      //[key,           type,       uitype,     display,          tab,      label,    frameset, pos,    ??,validation,  Forms,    ??,??,storage]
      ['id',            'bigInteger','text',    'ID',             '',       '',       '',       '0|12|1','','',         '11|0|1|0','','','IColumn'],
      ['created_at',    'timestamp','datetime', 'Create Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['updated_at',    'timestamp','datetime', 'Update Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['deleted_at',    'timestamp','datetime', 'DeletedDate',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['data',          'longText', 'json',     'Data',           '',       '',       '',       '0|12|1','','',         '00|0|0|0','','','IColumn'],
      ['igroup_id',     'bigInteger','rel-b1',  'Group',          '',       '',       '',       '0|12|1','','',         '11|0|1|0','','foreign','IColumn'],

      ['name',  'string',   'text',       'Role Name',  '','','',           '0|12|1',  '','',            '11|1|1|1','','','Column'],
      ['status',    'string',   'select',     'Status',     '','','',           '0|12|1',  '','',            '11|1|1|1','','','Column'],
      ['default',   'integer',  'select',     'Default',    '','','',           '0|12|1',  '','',            '11|1|1|1','','','Column'],
      ['showtopmenu','string',  'select',     'ShowMenu',   '','','',           '0|12|1',  '','',            '11|1|1|1','','','JSON'],
      ['uimode',    'string',   'select',     'UIMode',     '','','',           '0|12|1',  '','',            '11|1|1|1','','','JSON'],
      ['wallpaper', 'string',   'select',     'Wallpaper',  '','','',           '0|12|1',  '','',            '11|1|1|1','','','JSON'],
      ['homeroute', 'string',   'text',       'HomeRoute',  '','','',           '0|12|1',  '','',            '11|1|1|1','','','JSON'],
      ['modules',   'string',   'rel-hn',     'Modules',    '','','',           '0|12|1',  '','',            '11|1|1|1','','','REL'],
      ['groups',    'string',   'rel-bn',     'Groups',     '','','',           '0|12|1',  '','',            '11|1|1|1','','','REL'],
      ['users',     'text',     'rel-bn',     'Users',      '','','',           '0|12|1',  '','',            '11|0|1|0','','','REL'],
    ];

    $data['relationships']=[
      'igroup_id'=>       ['igroup',        'Group',      'igroup_id,id', '{{name}}',             []],
      'modules'=>         ['modules',       'RoleModule', 'role_id,id',   '{{module}}-{{acl}}',   []],
      'groups'=>          ['groups',       'Group',       'RoleGroup,rolegroups,role_id,group_id',  '{{name}}',     []],
      'users'=>           ['users',        'User',        'UserRole,userroles,role_id,user_id',     '{{username}}',     []],
    ];
    $data['forms']=[
      'index'=>[
        0=> ['name'=>'Table', 'icon'=>'icon-table', 'photo'=>'photo','status'=>'status','nosearch'=>[]],
        1=> ['name'=>'Card',  'icon'=>'icon-grid',  'photo'=>'photo','status'=>'status','nosearch'=>[],'cardtitle'=>'{{name}}','cardsubtitle'=>'{{status}}'],
      ],
      'create'=>[
        0=>['name'=>'Create','type'=>'tab'],
      ],
      'show'=>[
        0=>['name'=>'Show','type'=>'tree'],
      ],
      'edit'=>[
        0=>['name'=>'Edit','type'=>'tab'],
      ],
    ];
    $data['values']=
    [
      'select'=>
      [
        'status'=>['Active','Disabled'],
        'default'=>[[0,'No'],[1,'Yes']],
        'showtopmenu'=>['','Yes','No'],
        'uimode'=>['','windows','default'],
        'wallpaper'=>['','No','Role'],
      ],

    ];

    $data['actions']=[];
    $data['triggers']=[];
    $data['script']=[];

    return $data;
  }
}

```


### Sipid

```
<?php namespace App\Models;

use App\Kstych\Models\KModel;
use App\Models\Kqueue;
use App\Models\UserLog;
use App\Models\Record;

class Sipid extends KModel{

  protected $table = 'sipids';


  public function updateStatus($status,$nowts=0)
  {
    if($this->status!=$status)
    {
      if($nowts==0)$nowts=microtime(true)*1000;
      $this->status=$status;
      $data=$this->getData('config',[]);
      if($status==0)
      {
        if($this->confup==1)
        {
          if(isset($data['channel']))
          {
            $newqueue=new Kqueue();
            $newqueue->hangupChannelS($data['channel'],$this->server);
          }
        }
        $this->ready=0;$this->confup=0;$this->setData('config',null,false);
      }
      else
      {
        if(isset($data['keepconf'])&&$data['keepconf']==1)
        {
          $newqueue=new Kqueue();
          $newqueue->userToConf($this);
        }
      }
      $this->save();

      $user=$this->user;
      $userstatus=0;
      if($user)
      {
        $userstatus=0+intval(Sipid::where('user_id','=',$this->user_id)->max('status'));

        if($user->presence!=$userstatus)
        {
          if($userstatus==1)
          {
            $userlog=new UserLog();
            $userlog->startLog($user);
            $userlog->getLastTs($this->id,$nowts);
            $userlog->igroup_id=$user->igroup_id;
            $userlog->save();
          }
          else if($userstatus==0)
          {
            $userlog=UserLog::where('user_id','=',$user->id)->orderBy('id','DESC')->first();
            if($userlog)
            {
              $userlog->stopLog();
              $userlog->getLastTs($this->id,$nowts);
              $userlog->save();
            }
          }

          $user->presence=$userstatus;
          $user->save();

          //$frndlib=new KFriendLib();
          //$noty=new KPushNotify();
          //$noty->send($frndlib->myFrndList($user->id),'presence',$user->dispname(),$user->presence,$user->id);
        }


        //close any calls
        if($status==0)
        {
          $calls=CallLog::where('sipid_id','=',$this->id)->where('user_id','=',$this->user_id)->whereColumn('ts_Dispo','=','ts_Close')->where('userstatus','=','')->where('created_at','>=',date('Y-m-d 00:00:00'))->get();
          foreach($calls as $call)
          {
            $call->setTs('ts_Close',$nowts);

            $call->userstatus='FORCEDCLOSE';
            $call->usersubstatus='FORCEDCLOSE';
            if($call->crm_id>0)
            {
              $record=Record::find($call->crm_id);
              if($record)
              {
                $record->setData('dialer_status','FORCEDCLOSE');
                $record->setData('dialer_substatus','FORCEDCLOSE');
                $record->setData('dialer_callback','');
                $record->setData('dialer_remarks','');
                $record->save();
              }

            }

            $call->saveRecFileSize();
            $call->save();
          }
        }
      }
      return ['userstatus'=>$userstatus];
    }
    else
    {
      $this->touch();
    }

    return false;
  }

  public function igroup()
  {
    return $this->belongsTo('App\Models\Group','igroup_id','id');
  }
  public function user()
  {
    return $this->belongsTo('App\Models\User','user_id','id');
  }

  public function modelData()
  {
    $data=[];

    $data['config']=[];

    $data['schema']=
    [
      //[key,           type,       uitype,     display,          tab,      label,    frameset, pos,    ??,validation,  Forms,    ??,??,storage]
      ['id',            'bigInteger','text',    'ID',             '',       '',       '',       '0|12|1','','',         '11|0|1|0','','','IColumn'],
      ['created_at',    'timestamp','datetime', 'Create Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['updated_at',    'timestamp','datetime', 'Update Date',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['deleted_at',    'timestamp','datetime', 'DeletedDate',    '',       '',       '',       '0|12|1','','',         '00|0|1|0','','','IColumn'],
      ['data',          'longText', 'json',     'Data',           '',       '',       '',       '0|12|1','','',         '00|0|0|0','','','IColumn'],
      ['igroup_id',     'bigInteger','rel-b1',  'Group',          '',       '',       '',       '0|12|1','','',         '11|0|1|0','','foreign','IColumn'],

      ['user_id','bigInteger','rel-b1','User','','','','0|12|1','','','11|1|1|1','','foreign','Column'],
      ['server','string','select','Server','','','','0|12|1','','','11|1|1|1','','index','Column'],
      ['status','integer','select','Status','','','','0|12|1','','','11|1|1|1','','','Column'],
      ['ready','integer','text','Ready','','','','0|12|1','','','11|1|1|1','','','Column'],
      ['confup','integer','text','ConfUp','','','','0|12|1','','','11|1|1|1','','','Column'],
    ];
    $data['relationships']=
    [
      'igroup_id'=>       ['igroup',        'Group',      'igroup_id,id', '{{name}}',             []],
      'user_id'=>         ['user',          'User',       'user_id,id',   '{{username}}',         []],
    ];
    $data['forms']=
    [
      'index'=>
      [
        0=> ['name'=>'Table', 'icon'=>'icon-table', 'photo'=>'photo','status'=>'status','nosearch'=>[]],
        1=> ['name'=>'Card',  'icon'=>'icon-grid',  'photo'=>'photo','status'=>'status','nosearch'=>[],'cardtitle'=>'{{name}}','cardsubtitle'=>'{{status}}'],
      ],
      'create'=>
      [
        0=>['name'=>'Create','type'=>'tab'],
      ],
      'show'=>
      [
        0=>['name'=>'Show','type'=>'tree'],
      ],
      'edit'=>
      [
        0=>['name'=>'Edit','type'=>'tab'],
      ],
    ];
    $data['values']=
    [
      'select'=>
      [
        'server'=>[],
        'status'=>['Active','Disabled'],
      ],
    ];
    $data['actions']=[];
    $data['triggers']=[];
    $data['script']=[];

    return $data;
  }

}

```
