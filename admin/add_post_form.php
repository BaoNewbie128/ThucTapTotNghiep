<h3>Thêm bài viết</h3>

<form method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="text" name="title" class="form-control mb-2" placeholder="Tiêu đề" required>

    <textarea name="content" id="editor" class="form-control mb-2"></textarea>

    <input type="file" name="thumbnail" class="form-control mb-2">

    <select name="status" class="form-control mb-2">
        <option value="published">Hiển thị</option>
        <option value="draft">Ẩn</option>
    </select>

    <button class="btn btn-success">Thêm</button>
</form>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
ClassicEditor.create(document.querySelector('#editor'), {
    ckfinder: {
        uploadUrl: 'upload_image.php'
    }

}).catch(error => {
    console.error(error);
});
</script>