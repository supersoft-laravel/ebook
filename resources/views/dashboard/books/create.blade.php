@extends('layouts.master')

@section('title', __('Create Book'))

@section('css')
    {{-- <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"> --}}
@endsection


@section('breadcrumb-items')
    <li class="breadcrumb-item"><a href="{{ route('dashboard.books.index') }}">{{ __('Books') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Create') }}</li>
@endsection
@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card mb-6">
            <!-- Account -->
            <div class="card-body pt-4">
                <form method="POST" action="{{ route('dashboard.books.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row p-5">
                        <h3>{{ __('Add New Book') }}</h3>
                        <div class="mb-4 col-md-6">
                            <label for="title" class="form-label">{{ __('Title') }}</label><span
                                class="text-danger">*</span>
                            <input class="form-control @error('title') is-invalid @enderror" type="text" id="title"
                                name="title" required placeholder="{{ __('Enter title') }}" autofocus
                                value="{{ old('title') }}" />
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label for="slug" class="form-label">{{ __('Slug') }}</label><span
                                class="text-danger">*</span>
                            <input class="form-control @error('slug') is-invalid @enderror" type="text" id="slug"
                                name="slug" required placeholder="{{ __('Enter slug') }}" />
                            @error('slug')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label class="form-label" for="book_type_id">{{ __('Book Type') }}</label>
                            <select id="book_type_id" name="book_type_id"
                                class="select2 form-select @error('book_type_id.*') is-invalid @enderror">

                                @if (isset($bookTypes) && count($bookTypes) > 0)
                                    @foreach ($bookTypes as $bookType)
                                        <option value="{{ $bookType->id }}"
                                            {{ collect(old('book_type_id', []))->contains($bookType->id) ? 'selected' : '' }}>
                                            {{ $bookType->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>

                            @error('book_type_id.*')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-4 col-md-6">
                            <label for="author" class="form-label">{{ __('Author') }}</label>
                            <input class="form-control @error('author') is-invalid @enderror" type="text" id="author"
                                name="author" placeholder="{{ __('Enter author') }}" />
                            @error('author')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label for="publication_year" class="form-label">{{ __('Publication Year') }}</label>
                            <input class="form-control @error('publication_year') is-invalid @enderror" type="date"
                                id="publication_year" name="publication_year"
                                placeholder="{{ __('Enter publication year') }}" />
                            @error('publication_year')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label for="isbn" class="form-label">{{ __('ISBN') }}</label>
                            <input class="form-control @error('isbn') is-invalid @enderror" type="text" id="isbn"
                                name="isbn" placeholder="{{ __('Enter isbn') }}" />
                            @error('isbn')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label for="price" class="form-label">{{ __('Price') }}</label><span
                                class="text-danger">*</span>
                            <input class="form-control @error('price') is-invalid @enderror" type="number" id="price"
                                name="price" step="any" required placeholder="{{ __('Enter price') }}" />
                            @error('price')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label for="free_laws" class="form-label">{{ __('No of Free Laws') }}</label>
                            <input class="form-control @error('free_laws') is-invalid @enderror" type="number"
                                step="any" id="free_laws" name="free_laws" value="{{ old('free_laws') ?? '0' }}"
                                required placeholder="{{ __('Enter Free Laws') }}" />
                            @error('free_laws')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label for="image" class="form-label">{{ __('Image') }}</label>
                            <input class="form-control @error('image') is-invalid @enderror" type="file" id="image"
                                name="image" accept="image/*" />
                            @error('image')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-6">
                            <label for="pdf_file" class="form-label">{{ __('PDF File') }}</label>
                            <input class="form-control @error('pdf_file') is-invalid @enderror" type="file"
                                id="pdf_file" name="pdf_file" />
                            @error('pdf_file')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-12">
                            <label for="amazon_link" class="form-label">{{ __('Amazon Link') }}</label>
                            <input class="form-control @error('amazon_link') is-invalid @enderror" type="url" id="amazon_link"
                                name="amazon_link" placeholder="{{ __('Enter amazon link') }}" />
                            @error('amazon_link')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 col-md-12">
                            <label for="description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                placeholder="{{ __('Enter description') }}" cols="30" rows="10">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-3">{{ __('Add Book') }}</button>
                    </div>
                </form>
            </div>
            <!-- /Account -->
        </div>
    </div>
@endsection

@section('script')
    <!-- Vendors JS -->
    {{-- <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script> --}}
    <script>
        $(document).ready(function() {
            // tinymce.init({
            //     selector: '#description',
            //     height: 500,
            //     plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
            //     toolbar: `undo redo | formatselect | fontselect fontsizeselect |
        //               bold italic underline strikethrough forecolor backcolor |
        //               alignleft aligncenter alignright alignjustify |
        //               bullist numlist outdent indent | link image media table |
        //               removeformat | code fullscreen`,
            //     menubar: 'file edit view insert format tools table help',
            //     branding: false,
            //     content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }"
            // });

            // Generate slug from name
            $('#title').on('keyup change', function() {
                let name = $(this).val();
                let slug = name.toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                $('#slug').val(slug);
            });

            // Handle form submission manually to validate TinyMCE
            $('form').on('submit', function(e) {
                tinymce.triggerSave(); // sync content to <textarea>
                const $details = $('#description');
                const detailsContent = $details.val().trim();

                // Remove previous validation state
                $details.removeClass('is-invalid');
                $details.next('.invalid-feedback').remove();

                if (!detailsContent) {
                    e.preventDefault();

                    // Add Bootstrap invalid class
                    $details.addClass('is-invalid');

                    // Append validation message
                    $details.after(`
                        <div class="invalid-feedback">
                            {{ __('The details field is required.') }}
                        </div>
                    `);

                    // Optional: focus editor
                    tinymce.get('description').focus();
                }
            });
        });
    </script>
@endsection
