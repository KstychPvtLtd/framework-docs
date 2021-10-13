@extends('custom.blogpost.template')

@section('content')

<div id="content" style="max-width:80%;margin:10px auto;">
	  <div class="post-container container " style="padding-bottom:10px;">
	    <div class="post" style="padding:20px;">
	      <h3 class="post-title"><a href="/blogpost/{{$post->id}}?{{$post->title}}">{{$post->title}}</a></h3>
	      <div class="post-content">
	        <p>{{$post->body}}</p>
	      </div>
	      <div class="post-author">
	        <img src="{{$post->user->getFileLink('photo')}}" alt="user profile pic">
	        <span>{{$post->user->fullname}}</span>
	      </div>
	    </div>
	    <div class="container">
		    <div class="row">
	    	   <div class="col-sm-6" style="text-align:right">
	        	  <form method="Post" action="/blogpost/{{$post->id}}" >
	        	    @csrf
	        	    <input name="_method" type="hidden" value="DELETE">
	                <button  type="submit" class="btn btn-danger" value="delete">Delete</button></a>
	        	  </form>
		       </div>
		       <div class="col-sm-6">
			       <a href="/blogpost/create?id={{$post->id}}"><button  class="btn btn-primary">Edit</button></a>
	       	   </div>
			</div>
		</div>
	   </div>
</div>

<div style="margin:50px auto; width:50%">

	<h2 align="center">Latest Post</h2>
	 @forelse($latests as $latest)
        <div class="post-container">
        <div class="post" style="padding:20px 35px;">
          <h3 class="post-title"><a href="/blogpost/{{$latest->id}}?{{$latest->title}}">{{$latest->title}}</a></h3>
          <div class="post-content">
            <p>{{$latest->intro}}</p>
          </div>
          <div class="post-author">
            <img src="{{$latest->user->getFileLink('photo')}}" alt="user profile pic">
            <span>{{$latest->user->fullname}}</span>
          </div>
        </div>
        </div>

      @empty
	      <div class="post-container">
	        <div class="post" style="text-align: center;">
	          Nothing here yet
	        </div>
	      </div>
      @endforelse


</div>

@endsection


