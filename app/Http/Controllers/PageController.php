<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Events\PostViewEvent;
use DB;
use App\Http\Requests;
use App\Lesson;
use App\Course;
use App\User;
use App\Post;
use App\Tag;
use App;
use Log;
use URL;
use Auth;
use Event;
use App\Events\ErrorEvent;

class PageController extends Controller
{
    
    /*
     * Home page that displays all lessons
     */
    public function Home()
    {
        $lessons = DB::table('lessons')->orderBy('created_at', 'desc')->paginate(9);
        $courses = Course::all();
        return view('index', compact('lessons', 'courses'));
    }
    /*
     * Show the an article based of slug passed in.
     * 
     * @return view
     */
    public function show($slug)
    {  
        $lesson = Lesson::whereSlug($slug)->get()->first();
        
        $course = Course::where('id', '=', $lesson->course_id)->get()->first();
        
        if(count($lesson) < 1){
            Log::info("Lesson could not be found");
            $error = [
                'type' => '404: Lesson Not Found for Slug: ',
                'body' => $slug,
            ];
            Event::fire(new ErrorEvent($error));
            return view('errors.404');
        }
        // log::info($lesson->tags->get(0));
        $tag = $lesson->tags->get(0);
        // fire an event every time a lesson is vieweds
        Event::fire(new PostViewEvent($lesson));   
        
        if(count($lesson->tags) > 0){
            $articles = Lesson::whereHas('tags', function($q) use ($tag)
                        {
                            // log::info($tag->id);
                            $q->where('tag_id', $tag->id);
                        })
                        ->where('id', '!=', $lesson->id)
                        ->take(2)
                        ->get();
        } else {
            Log::info("Returning 2 Random Recommended Articles");
            $articles = DB::table('lessons')->take(2)->get();
        }
        
        // Log::info("Lesson Requested: ", $lesson->title);
        
        return view('frontend.single', compact('lesson', 'articles', 'course'));     
        
    }
    
    /*
     * returns paginated results of all tutorials 
     */
    public function allTutorials()
    {
        $lessons = Lesson::paginate(9);
        
        return view('frontend.all', compact('lessons'));
    }
    
    /*
     * returns the search view 
     */
     public function search()
     {
         $results = Lesson::get()->all();
         
         return view('frontend.search', compact('results'));
     }
     
     /*
      * Returns the course page for any courses
      */
      public function course($slug)
      {
          $course = Course::whereSlug($slug)->get()->first();
          return view('frontend.course.single', compact('course'));
         
      }
      
      /*
      * Returns the course page for any courses
      */
      public function courses()
      {
          $courses = Course::paginate(15);
          
          return view('frontend.course.all', compact('courses'));
      }
      
      /*
       *
       */
       public function contact()
       {
           return view('frontend.static.contact');
       }
       
       /*
       *
       */
       public function advertise()
       {
           return view('frontend.static.advertise');
       }
       
       /*
       *
       */
       public function blog()
       {
           $posts = Post::orderBy('id', 'DESC')->paginate(15);
           return view('frontend.static.blog', compact('posts'));
       }
       
       /*
       *
       */
       public function blogSingle($slug)
       {
           $post = Post::whereSlug($slug)->get()->first();
           return view('frontend.static.blog-single', compact('post'));
       }
       
       /*
       *
       */
       public function about()
       {
           return view('frontend.static.about');
       }
    
       /*
       *
       */
       public function forum()
       {
           return view('frontend.forum.index');
       }
       
       public function tag($slug)
       {
           $tag = Tag::where('name', '=', $slug)->get()->first();
           
           $articles = $tag->articles;
          
           return view('frontend.tag', compact('tag', 'articles')); 
       }
       
       public function register() 
       {
            return view('frontend.register');   
       }
       
       public function profile()
       {
           if(Auth::user())
           {
               $user = Auth::user();
                
               return view('frontend.profile.index', compact('user'));
           } 
           else
           {
               return view('frontend.profile.register');
           } 
       }
       
       
       public function sitemap()
       {
           $sitemap = App::make("sitemap");
           
        //    $sitemap->setCache('laravel.sitemap', 60);
           Log::info("Sitemap Route Hit");
        //    if($sitemap->isCached())
        //    {
               Log::info("Sitemap isn't cached, creating new sitemap");
               $sitemap->add(URL::to('/'), '2015-01-01T12:00:00+02:00'  , '1.0', 'daily');
               
               $lessons = Lesson::orderBy('created_at', 'desc')->get()->all();
               
               foreach($lessons as $lesson)
               {
                   Log::info($lesson);
                   $location = URL::to('/') . "/" . $lesson->slug;
                   $sitemap->add($location , $lesson->created_at, '1.0', 'monthly');
               }
        //    }
           
           Log::info($sitemap->render('xml'));
           return $sitemap->render('xml');
       }
       
       public function error404()
       {
           return view('errors.404');
       }
       
       public function error500()
       {
           return view('errors.500');
       }
       
       public function error()
       {
           return view('errors.index');
       }
    
    
}
