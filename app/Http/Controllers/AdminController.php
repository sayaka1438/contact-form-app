<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;

class AdminController extends Controller
{
    public function index(IndexContactRequest $request)
    {
        $validated = $request->validated();

        $query = Contact::with('category', 'tags');

        $keyword = $validated['keyword'] ?? null;

        if ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $gender = $validated['gender'] ?? null;

        if ($gender) {
            $query->where('gender', $gender);
        }

        $categoryId = $validated['category_id'] ?? null;

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $date = $validated['date'] ?? null;

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

        return redirect()->route('admin.contacts.index');
    }
}
