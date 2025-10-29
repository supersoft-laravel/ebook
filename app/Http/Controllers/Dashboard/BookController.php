<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLaw;
use App\Models\BookType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view book');
        try {
            $books = Book::withCount('bookLaws','bookType')->get();
            return view('dashboard.books.index',compact('books'));
        } catch (\Throwable $th) {
            Log::error('Books Index Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create book');
        try {
            $bookTypes = BookType::where('is_active', 'active')->get();
            return view('dashboard.books.create', compact('bookTypes'));
        } catch (\Throwable $th) {
            Log::error('Books Create Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create book');
        $validator = Validator::make($request->all(), [
            'book_type_id' => 'required|exists:book_types,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:books,slug',
            'author' => 'nullable|string|max:255',
            'amazon_link' => 'nullable|url',
            'publication_year' => 'nullable|date',
            'isbn' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'free_laws' => 'required|integer|min:0',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max_size',
            'pdf_file' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('error', 'Validation Error!');
        }

        try {
            DB::beginTransaction();
            $book = new Book();
            $book->book_type_id = $request->book_type_id;
            $book->title = $request->title;
            $book->slug = $request->slug;
            $book->author = $request->author;
            $book->amazon_link = $request->amazon_link;
            $book->publication_year = $request->publication_year;
            $book->isbn = $request->isbn;
            $book->price = $request->price;
            $book->free_laws = $request->free_laws;
            $book->description = $request->description;

            if ($request->hasFile('image')) {
                $bookImage = $request->file('image');
                $bookImage_ext = $bookImage->getClientOriginalExtension();
                $bookImage_name = time() . '_bookImage.' . $bookImage_ext;
                $bookImage_path = 'uploads/book-images';
                $bookImage->move(public_path($bookImage_path), $bookImage_name);
                $book->image = $bookImage_path . "/" . $bookImage_name;
            }
            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                $pdfFile_ext = $pdfFile->getClientOriginalExtension();
                $pdfFile_name = time() . '_pdfFile.' . $pdfFile_ext;
                $pdfFile_path = 'uploads/pdf-files';
                $pdfFile->move(public_path($pdfFile_path), $pdfFile_name);
                $book->pdf_file = $pdfFile_path . "/" . $pdfFile_name;
            }

            $book->save();

            DB::commit();
            return redirect()->route('dashboard.books.index')->with('success', 'Book Created Successfully');
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            Log::error('Book Created Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view book law');
        try {
            $book = Book::findOrFail($id);
            $bookLaws = BookLaw::where('book_id', $id)->get();
            return view('dashboard.books.laws.index', compact('book','bookLaws'));
        } catch (\Throwable $th) {
            Log::error('Books Edit Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('update book');
        try {
            $book = Book::findOrFail($id);
            $bookTypes = BookType::where('is_active', 'active')->get();
            return view('dashboard.books.edit', compact('book','bookTypes'));
        } catch (\Throwable $th) {
            Log::error('Books Edit Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('update book');
        $validator = Validator::make($request->all(), [
            'book_type_id' => 'required|exists:book_types,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:books,slug,'.$id,
            'author' => 'nullable|string|max:255',
            'amazon_link' => 'nullable|url',
            'publication_year' => 'nullable|date',
            'isbn' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'free_laws' => 'required|integer|min:0',
            'description' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max_size',
            'pdf_file' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('error', 'Validation Error!');
        }

        try {
            DB::beginTransaction();
            $book = Book::findOrFail($id);
            $book->book_type_id = $request->book_type_id;
            $book->title = $request->title;
            $book->slug = $request->slug;
            $book->author = $request->author;
            $book->amazon_link = $request->amazon_link;
            $book->publication_year = $request->publication_year;
            $book->isbn = $request->isbn;
            $book->price = $request->price;
            $book->free_laws = $request->free_laws;
            $book->description = $request->description;

            if ($request->hasFile('image')) {
                if (isset($book->image) && File::exists(public_path($book->image))) {
                    File::delete(public_path($book->image));
                }

                $bookImage = $request->file('image');
                $bookImage_ext = $bookImage->getClientOriginalExtension();
                $bookImage_name = time() . '_bookImage.' . $bookImage_ext;
                $bookImage_path = 'uploads/book-images';
                $bookImage->move(public_path($bookImage_path), $bookImage_name);
                $book->image = $bookImage_path . "/" . $bookImage_name;
            }

            if ($request->hasFile('pdf_file')) {
                if (isset($book->pdf_file) && File::exists(public_path($book->pdf_file))) {
                    File::delete(public_path($book->pdf_file));
                }

                $pdfFile = $request->file('pdf_file');
                $pdfFile_ext = $pdfFile->getClientOriginalExtension();
                $pdfFile_name = time() . '_pdfFile.' . $pdfFile_ext;
                $pdfFile_path = 'uploads/pdf-files';
                $pdfFile->move(public_path($pdfFile_path), $pdfFile_name);
                $book->pdf_file = $pdfFile_path . "/" . $pdfFile_name;
            }

            $book->save();

            DB::commit();
            return redirect()->route('dashboard.books.index')->with('success', 'Book Updated Successfully');
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            Log::error('Book Created Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete book');
        try {
            $book = Book::findOrFail($id);
            if (isset($book->image) && File::exists(public_path($book->image))) {
                File::delete(public_path($book->image));
            }
            $book->delete();
            return redirect()->back()->with('success', 'Book Deleted Successfully!');
        } catch (\Throwable $th) {
            Log::error('Books Delete Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    // Book Laws Methods

    public function bookLawIndex(string $id)
    {
        $this->authorize('view book law');
        try {
            $book = Book::findOrFail($id);
            $bookLaws = BookLaw::where('book_id', $id)->get();
            return view('dashboard.books.laws.index', compact('book','bookLaws'));
        } catch (\Throwable $th) {
            Log::error('Book Laws Index Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }

    public function bookLawCreate(string $id)
    {
        $this->authorize('create book law');
        try {
            $book = Book::findOrFail($id);
            return view('dashboard.books.laws.create', compact('book'));
        } catch (\Throwable $th) {
            Log::error('Book Laws Create Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }
    public function bookLawStore(Request $request, string $id)
    {
        $this->authorize('create book law');
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:book_laws,slug',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('error', 'Validation Error!');
        }

        try {
            DB::beginTransaction();
            $bookLaw = new BookLaw();
            $bookLaw->title = $request->title;
            $bookLaw->slug = $request->slug;
            $bookLaw->content = $request->content;
            $bookLaw->book_id = $id;
            $bookLaw->save();

            DB::commit();
            return redirect()->route('dashboard.book-laws.index', $id)->with('success', 'Book Law Created Successfully');
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            Log::error('Book Law Created Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
        }
    }
    public function bookLawEdit(string $id)
    {
        $this->authorize('update book law');
        try {
            $bookLaw = BookLaw::findOrFail($id);
            $book = Book::findOrFail($bookLaw->book_id);
            return view('dashboard.books.laws.edit', compact('bookLaw', 'book'));
        } catch (\Throwable $th) {
            Log::error('Book Laws Edit Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }
    public function bookLawUpdate(Request $request, string $id)
    {
        $this->authorize('update book law');
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'required|unique:book_laws,slug,'.$id,
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput($request->all())->with('error', 'Validation Error!');
        }

        try {
            DB::beginTransaction();
            $bookLaw = BookLaw::findOrFail($id);
            $bookLaw->title = $request->title;
            $bookLaw->slug = $request->slug;
            $bookLaw->content = $request->content;
            $bookLaw->save();

            DB::commit();
            return redirect()->route('dashboard.book-laws.index', $bookLaw->book_id)->with('success', 'Book Law Updated Successfully');
        } catch (\Throwable $th) {
            throw $th;
            DB::rollBack();
            Log::error('Book Law Created Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
        }
    }
    public function bookLawDestroy(string $id)
    {
        $this->authorize('delete book law');
        try {
            $bookLaw = BookLaw::findOrFail($id);
            $bookLaw->delete();
            return redirect()->back()->with('success', 'Book Law Deleted Successfully!');
        } catch (\Throwable $th) {
            Log::error('Book Laws Delete Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
            throw $th;
        }
    }
}
