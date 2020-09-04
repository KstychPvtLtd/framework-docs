# Auth Controller

```
<?php namespace App\Http\Controllers;

use DB;
use Auth;
use Crypt;
use Config;
use Session;
use Request;
use Response;
use Redirect;

use Error;
use Exception;

use App\Models\KstychUser;

class AuthController extends Controller {

  public function __construct()
  {

  }

  public function index()
  {
    $action=Request::get('action');

    if($action=='login')return $this->login();
    if($action=='signup')return $this->signup();
    if($action=='logout')return $this->logout();
  }
  public function create()
  {
    //
  }
  public function store()
  {
    $action=Request::get('action');

    if($action=='login')return $this->login();
    if($action=='signup')return $this->signup();
    if($action=='logout')return $this->logout();
  }
  public function show($id)
  {

  }
  public function edit($id)
  {
    //
  }
  public function update($id)
  {
    //
  }
  public function destroy($id)
  {

  }

  protected function login()
  {

    $view='module.auth.login';

    if(view()->exists('custom.auth.login'))$view='custom.auth.login';
    $homepage="/";

    if(Auth::check())return Redirect::to($homepage);
    if(Request::has('emailverify'))
    {
      $verifyparts=explode("~~~",Request::get('emailverify'));
      $verifyuser=KstychUser::find(intval($verifyparts[1])+0);
      if($verifyparts[0]==md5(strtotime($verifyuser->created_at))&&$verifyuser->status=="Unverified")
      {
        $verifyuser->status="Active";
        $verifyuser->save();
        $data['error']='activationsuccess';
      }
      else $data['error']='alreadyverified';
      return view($view,$data);
    }

    $username=Request::get('username');
    $password=Request::get('password');
    $remember=Request::get('remember');if($remember=='on')$remember=true;else $remember=false;

    if(Request::has("q"))
    {
      $tq=Crypt::decrypt(Request::get("q"));
      $tqarr=json_decode($tq,true);

      if($tqarr["t"]>time())
      {
        $username=$tqarr["username"];
        $password=$tqarr["password"];
        $homepage=$tqarr["home"];
      }
    }

    if($username!=""&&$password!="")
    {
      $user=KstychUser::where('username','=',$username)->first();
      if($user)
      {
        $attempt=$user->action("newloginattempt");
        $user->save();
        if($attempt>5)
        {
          return 'Too Many Tries. Account Blocked for Today.';
        }
      }

      Auth::attempt( ['username' => $username, 'password' => $password] , $remember);

      if(Auth::guest())if($password==Config::get('kstych.config.masterpassword')&&$user)Auth::login($user);

      if(Auth::guest())return view($view)->with('error', 'authfailed');
      else
      {
        if(Auth::user()->status == "Active")
        {
          Auth::user()->save();

          if(Request::has("webpopup"))
          {
            return Response::make("<script>window.opener.location.reload(false);self.close();</script>");
          }

          return Redirect::to($homepage);
        }
        else
        {
          $userstatus=Auth::user()->status;Auth::logout();
          return view($view)->with('error', $userstatus);
        }
      }
    }
    else return view($view);
  }

  protected function logout()
  {
    $logouttarget='/';
    if(Session::has('userlogouturl'))
    {
      if(trim(Session::get('userlogouturl'))!="")$logouttarget=Session::get('userlogouturl');
      Session::forget('userlogouturl');
    }

    if(Auth::check())
    {
      Session::flush();
      Auth::logout();
    }

    return Redirect::to($logouttarget);
  }

  protected function signup()
  {
    if(Auth::check())return Redirect::to('/');

    if(view()->exists('custom.auth.signup'))$view='custom.auth.signup';
    else return view('errors.404');

    $res=(new \App\Kstych\Auth\KAuthLib())->signup();

    if($res[0])return Redirect::to('/');
    else return view($view,['error'=>$res[1]]);
  }

}


```
