<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;

class LessonController extends Controller
{
    
    public function index()
    {
        $lessons = Lesson::all();
        return view('admin.article.all', compact('lessons'));
    }
    
    public function create()
    {
        return view('admin.article.new');
    }
    
    public function edit($slug)
    {
        $lesson = Lesson::whereSlug($slug)->get()->first();
        return view('admin.article.edit', compact('lesson'));
    }
    
}
