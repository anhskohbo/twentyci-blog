<div class="post__form">
    <div class="row">
        <div class="col-lg-8">
            <label class="d-block mb-4">
                <input
                    type="text"
                    class="form-control form-control-lg d-block"
                    placeholder="{{ __('Enter Title') }}"
                    name="title"
                    value="{{ $post->title }}"
                >
            </label>

            <div id="editor"></div>
            <textarea type="hidden" name="content" style="display: none;">{{ $post->content }}</textarea>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <p class="post-status">
                        <label for="status">Post status</label>

                        <select class="form-control" id="status" name="status">
                            <option value="pending">Pending Review</option>
                            <option value="published">Published</option>
                        </select>
                    </p>

                    @if($post->id)
                        <button class="btn btn-primary btn-block">{{ __('Delete') }}</button>
                    @endif

                    <button class="btn btn-primary btn-block">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>