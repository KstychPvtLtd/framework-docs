@extends('custom.blogpost.template')

@section('content')

	<div class="container" style="margin-bottom:20px;">
		<br/>
			<h3>@if($post->id) Edit @else Create New @endif Post</h3>
		<br/>
		<form @if($post->id) action="/blogpost/{{$post->id}}" @else  action="/blogpost" @endif method="POST"  >
			@csrf
     @if($post->id) <input name="_method" type="hidden" value="put">@endif
		  <div class="form-group">
		    <label>Title</label>
		    <input type="text" class="form-control"  value="{{$post->title}}"  name="title" placeholder="Enter title" required>

		  </div>
		  <div class="form-group">
		    <label>Intro</label>
		    <textarea type="textarea" class="form-control" name="intro"   placeholder="Enter Intro" required>{{$post->intro}}</textarea>
		  </div>


		  <div class="form-group">
		    <label>Body</label>
		    <textarea type="textarea" class="form-control" name="body"    placeholder="Enter body" required>{{$post->body}}</textarea>
		  </div>

		  <center>
		     <button type="submit" class="btn btn-primary" >Submit</button>
		  </center>
		</form>
	</div>
@endsection

