<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Contact::with('category', 'tags');

        $keyword = $request->input('keyword');

        if ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $gender = $request->input('gender');

        if ($gender) {
            $query->where('gender', $gender);
        }

        $categoryId = $request->input('category_id');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $date = $request->input('date');

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $contacts = $query->latest()->paginate(7);

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    public function show(Contact $contact)
    {
        $contact->load('category', 'tags');

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.index');
    }
}
