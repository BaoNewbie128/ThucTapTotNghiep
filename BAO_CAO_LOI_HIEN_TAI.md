# Bao cao loi hien tai cua du an JDM World

Ngay kiem tra: 25/04/2026  
Moi truong kiem tra: PHP 8.3.14, chay bang `php -S localhost:8000`

## Ket qua kiem tra nhanh

- Da chay lint PHP cho 51 file `.php` ngoai thu muc `vendor`: khong co loi cu phap.
- Trang chinh `/` va `/index.php` tra ve HTTP 200 khi chay local server.
- Co loi tai nguyen anh bi 404 khi render danh sach san pham.
- Co mot so rui ro bao mat/logic van can sua truoc khi dua len production.
- File `BAO_CAO_KIEM_TRA_DU_AN.md` cu co mot so muc da loi thoi vi code/schema hien tai da duoc cap nhat.

## Loi can sua uu tien cao

### 1. Anh san pham trong database khong khop file that

- File lien quan: `config/jdm.sql`, thu muc `images/`.
- Ket qua doi chieu: co 2 anh duoc tham chieu trong SQL nhung khong ton tai trong thu muc `images/`:
  - `toyota_gt86_white`
  - `toyota_supra_mk5_white.jpg`
- Dau hieu thuc te: server log bao `404` cho `/images/toyota_supra_mk5_white.jpg`.
- Hau qua: san pham hien anh bi vo/mat anh tren giao dien.
- De xuat:
  - Doi `toyota_gt86_white` thanh dung ten file co duoi, vi du `toyota_gt86_white.jpg` neu file ton tai.
  - Bo sung file `toyota_supra_mk5_white.jpg` vao `images/` hoac sua database sang ten anh dang co that.

### 2. `user/reviews.php` con noi chuoi SQL truc tiep

- File lien quan: `user/reviews.php`.
- Vi tri/logic lien quan:
  - Lay san pham bang `WHERE id = $product_id`.
  - Kiem tra/xoa/sua review bang query ghep `$review_id`, `$user_id`, `$new_comment`.
  - Tao review bang `INSERT INTO reviews ... VALUES ($user_id, $product_id, $rating, '$comment', NOW())`.
- Trang thai hien tai: da co `intval()`, `real_escape_string()` va CSRF, nhung van nen chuyen sang prepared statement de an toan va dong nhat.
- Hau qua: tang rui ro SQL injection neu co diem validate bi sot; code kho bao tri.
- De xuat: doi tat ca query co input nguoi dung sang `prepare()` + `bind_param()`.

### 3. OTP reset password van luu dang plain text va chua co rate limit

- File lien quan: `user/forgot_password.php`, `user/verify_otp.php`, `user/reset_password.php`.
- Trang thai hien tai:
  - SMTP credential da duoc dua sang bien moi truong `SMTP_USERNAME`, `SMTP_PASSWORD`.
  - Thong bao email khong ton tai da duoc lam chung chung hon.
  - OTP van luu truc tiep vao bang `password_resets`.
- Hau qua: neu database bi lo, OTP con hieu luc co the bi dung de reset mat khau; co nguy co brute force OTP neu khong gioi han so lan nhap/gui.
- De xuat:
  - Luu hash OTP thay vi OTP plain text.
  - Them so lan nhap sai, thoi gian cho gui lai OTP, va gioi han theo email/IP.
  - Xoa OTP het han bang job dinh ky hoac khi verify that bai qua nguong.

### 4. Cau hinh GHN con hard-code trong source

- File lien quan: `config/ghn.php`.
- Trang thai hien tai:
  - `GHN_TOKEN` dang la placeholder `your_ghn_token_here`.
  - `GHN_SHOP_ID` dang hard-code `6403411`.
- Hau qua: de sai cau hinh giua local/production; neu token that duoc dua vao source se co nguy co lo credential khi day len Git.
- De xuat:
  - Dung `getenv('GHN_TOKEN')` va `getenv('GHN_SHOP_ID')`.
  - Neu thieu cau hinh thi tra loi JSON loi ro rang thay vi goi API ngoai.
  - Validate `province_id`, `district_id` la so duong va xu ly loi cURL/HTTP/JSON.

## Loi/rui ro muc trung binh

### 5. Mot so trang van dung query ghep chuoi sau khi da escape

- File lien quan: `index.php`, `user/dashboard.php`, `admin/customer_management.php`, mot so trang admin/user khac.
- Trang thai hien tai: nhieu noi da dung `intval()` hoac `real_escape_string()`, nhung cach nay khong dong nhat bang prepared statement.
- Hau qua: kho review bao mat, de sot loi khi them dieu kien loc/tim kiem moi.
- De xuat: chuan hoa tat ca truy van co input tu `GET`, `POST`, `SESSION` sang prepared statement.

### 6. `user/reviews.php` cho phep mot user tao nhieu review cho cung mot san pham

- File lien quan: `user/reviews.php`, `config/jdm.sql`.
- Van de: khong thay rang buoc unique `(user_id, product_id)` va logic insert khong kiem tra da review chua.
- Hau qua: mot nguoi dung co the spam nhieu danh gia cho cung san pham, lam sai diem/danh sach review.
- De xuat:
  - Them unique key `(user_id, product_id)` neu nghiep vu chi cho phep moi user review 1 lan.
  - Hoac gioi han tan suat tao review neu cho phep review nhieu lan.

### 7. Noi dung bai viet can tiep tuc kiem soat XSS

- File lien quan: `admin/add_post.php`, `admin/edit_post.php`, `user/post_detail.php`, `includes/security.php`.
- Trang thai hien tai: da co `clean_html_content()` de loai bo mot so tag/attribute nguy hiem.
- Rui ro con lai: regex sanitize HTML khong manh bang HTML purifier/whitelist parser chuyen dung; neu co truong hop HTML phuc tap co the sot XSS.
- De xuat: dung thu vien sanitize HTML chuyen dung hoac chi cho phep plain text neu khong bat buoc can HTML.

### 8. Thu muc `vendor` co the bi expose tren web server

- File lien quan: `vendor/`, dac biet `vendor/phpmailer/phpmailer/get_oauth_token.php`.
- Van de: neu web server cho truy cap truc tiep `/vendor/...`, file demo/dev cua dependency co the bi public.
- Hau qua: tang be mat tan cong va gay rui ro cau hinh sai.
- De xuat: chan truy cap `vendor` bang `.htaccess`/web server config hoac khong deploy file demo/dev.

## Ghi chu ve cac muc da duoc cai thien so voi bao cao cu

- `config/jdm.sql` hien da co cac bang/cot quan trong: `posts`, `coupons`, `password_resets`, `orders.shipping_fee`, `orders.discount`.
- `includes/security.php` hien da co `require_admin()`, `csrf_field()`, `verify_csrf()`, `is_safe_local_url()`, `upload_image_file()` va `clean_html_content()`.
- `user/cart_item.php` hien da tinh tong gio hang truoc khi ap dung coupon, co CSRF cho cap nhat/xoa/ap coupon, va gioi han so luong theo ton kho khi cap nhat.
- `user/cart_add.php` hien da co transaction, lock san pham/gio hang va kiem tra ton kho khi them gio.
- `user/forgot_password.php` hien khong con hard-code SMTP user/password trong source.
- `user/verify_otp.php` hien da dung prepared statement khi xoa OTP.
- `user/reset_password.php` hien da dung `Validator::minLength()`.

## Lenh da chay

```bat
python -c "import pathlib, subprocess; files=[str(p) for p in pathlib.Path('.').rglob('*.php') if 'vendor' not in p.parts]; errs=[]; [errs.append((f,(r:=subprocess.run(['php','-l',f],capture_output=True,text=True)).stdout+r.stderr)) for f in files if (r:=subprocess.run(['php','-l',f],capture_output=True,text=True)).returncode!=0]; print(f'checked={len(files)} errors={len(errs)}'); [print('--- '+f+'\n'+out) for f,out in errs]"
```

Ket qua:

```text
checked=51 errors=0
```

## Thu tu sua de xuat

1. Sua/bosung 2 anh thieu trong `config/jdm.sql` hoac thu muc `images/`.
2. Chuyen CRUD trong `user/reviews.php` sang prepared statement va them rang buoc chong spam review.
3. Nang cap OTP reset password: hash OTP, rate limit, gioi han so lan nhap sai.
4. Dua cau hinh GHN sang bien moi truong va them xu ly loi API.
5. Chan public `vendor` tren web server.
6. Tiep tuc chuan hoa cac query ghep chuoi con lai sang prepared statement.
