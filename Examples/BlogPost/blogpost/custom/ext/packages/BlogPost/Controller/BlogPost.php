<?php namespace App\Custom\BlogPost\Controller;

use DB;
use Auth;
use Request;
class BlogPost
{
    public function __construct(){}

    public function index()
    {
      $posts = kmodel('Post')::latest()->paginate(15);
      return view("custom.blogpost.index",['posts'=>$posts]);
    }
    public function show($id)
    {
      $post = kmodel('Post')::find($id);
      $latests = kmodel('Post')::latest()->where('id','!=',$id)->take(3)->get();

      return view("custom.blogpost.show",['post'=>$post,'latests'=>$latests]);
    }
    public function create()
    {
        $id = request('id');
        $post = kmodel('Post')::find($id);
        if(!$post) $post = kmodel('Post')::make();
        return view("custom.blogpost.create",['post'=>$post]);
    }
    public function store()
    {
      request()->validate([
    		'title'=>'required',
    		'intro'=>'required',
    		'body'=>'required',
    	]);

      $Post = kmodel('Post');

    	$post = new $Post();
    	$post->title=request('title');
    	$post->intro=request('intro');
    	$post->body=request('body');
    	$post->user_id=Auth::user()->id;
    	$post->save();

      return redirect('/blogpost');
    }
    public function edit($id)
    {

    }
    public function update($id)
    {
    	$Post = kmodel('Post')::find($id);
    	$Post->title = request('title');
    	$Post->intro = request('intro');
    	$Post->body  = request('body');
    	 $Post->save();

      return redirect('/blogpost/'.$id.'?'.request('title'));

    }

    public function destroy($id)
    {
      $obj = Kmodel('Post')::where('id',$id)->first();
      if($obj) $obj->delete();
   	  return redirect('/blogpost');

    }
}

