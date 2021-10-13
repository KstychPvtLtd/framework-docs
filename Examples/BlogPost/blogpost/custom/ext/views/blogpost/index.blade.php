@extends('custom.blogpost.template')

@section('content')
     <div id="content" style="max-width:1000px;margin:10px auto">

      @forelse($posts as $post)

        <div class="post-container">
        <div class="post" style="padding:20px 30px;">
          <h3 class="post-title"><a href="/blogpost/{{$post->id}}?{{$post->title}}">{{$post->title}}</a></h3>
          <div class="post-content">
            <p>{{$post->intro}}</p>
          </div>
          <div class="post-author">
            <img src="{{$post->user->getFileLink('photo')}}" alt="user profile pic">
            <span>{{$post->user->fullname??''}}</span>

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


