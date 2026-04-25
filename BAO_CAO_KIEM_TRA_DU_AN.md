# Bao cao kiem tra du an JDM World

## Cap nhat sau khi doi chieu lai code hien tai

- Da sua mot phan trong `user/order_items.php`: chuyen huy don/xac nhan da chuyen khoan tu GET sang POST co CSRF, rang buoc don hang theo `user_id`, dung prepared statement, transaction va hien thi QR theo tong sau ship/giam gia.
- `includes/security.php` da co `require_admin()`, `csrf_field()`, `verify_csrf()`, `is_safe_local_url()` va `upload_image_file()`; vi vay mot so muc trong bao cao cu can hieu la "chua ap dung dong bo", khong phai hoan toan chua co helper.
- `includes/admin_auth_check.php` hien da dung `require_admin()`. `admin/admin_dashboard.php`, `admin/add_product.php`, `admin/edit_product.php`, `admin/upload_image.php` da co guard admin; can tiep tuc chuan hoa cac file admin con lai neu co the bi truy cap truc tiep.
- `user/cart_add.php` hien da kiem tra ton kho bang transaction va `SELECT ... FOR UPDATE`; muc cu noi file nay khong kiem tra ton kho khong con dung voi code hien tai.
- `user/wishlist_add.php` hien da dung `is_safe_local_url()` cho redirect; rui ro open redirect o file nay da duoc giam, nhung luong pending sau dang nhap van can test lai.
- Van de lon nhat con lai: `config/jdm.sql` chua dong bo voi code, vi code dung `posts`, `coupons`, `password_resets`, `orders.shipping_fee`, `orders.discount` nhung schema doc duoc chua co cac bang/cot nay.

Ngay kiem tra: 25/04/2026

Pham vi: kiem tra cau truc du an PHP, schema SQL, luong dang nhap/dang ky, gio hang, dat hang, admin, upload, danh gia, quen mat khau va chay kiem tra cu phap PHP.

## Ket qua tong quan

- Khong phat hien loi cu phap PHP khi chay `php -l` tren cac file `.php` trong du an.
- Co nhieu loi logic va rui ro bao mat can xu ly truoc khi dua len moi truong that.
- Muc do uu tien cao nhat: phan quyen admin, CSRF, logic ton kho/don hang, SQL injection, upload file va thong tin bi mat hard-code.

## Loi nghiem trong

### 1. Phan quyen admin chua chat

- File lien quan: `includes/auth_check.php`, `admin/admin_dashboard.php`, nhieu file trong `admin/`.
- Van de: `includes/auth_check.php` chi kiem tra da dang nhap, khong kiem tra `$_SESSION["role"] === "admin"`.
- Hau qua: tai khoan customer co the truy cap truc tiep mot so trang admin neu biet URL, vi nhieu file admin chi include `auth_check.php` hoac khong tu kiem tra role.
- Vi du: `admin/admin_dashboard.php` include `auth_check.php` o dau file nhung khong chan role customer.
- De xuat: tao middleware rieng `admin_auth_check.php` kiem tra dang nhap va role admin, include vao tat ca file trong `admin/`.

### 2. Thao tac thay doi du lieu bang GET va thieu CSRF token

- File lien quan: `admin/delete_product.php`, `admin/delete_post.php`, `user/cart_item.php`, `user/order_items.php`, `user/reviews.php`, `admin/reviews_management.php`.
- Van de: xoa/huy/cap nhat trang thai co the thuc hien qua link GET nhu `?action=delete`, `?delete_review=...`, `?view=delete&id=...`.
- Hau qua: de bi CSRF; nguoi dung/admin chi can bam vao link doc hai la co the xoa san pham, huy don, xoa review.
- De xuat: chuyen cac thao tac ghi/xoa sang POST, them CSRF token vao form, kiem tra token truoc khi xu ly.

### 3. Logic ton kho cua don hang bi sai, co the tru/cang ton kho nhieu lan

- File lien quan: `user/orders.php`, `admin/edit_order_status.php`, `admin/update_order_status.php`, `user/order_items.php`.
- Van de 1: `user/orders.php` tru ton kho ngay khi checkout, trong khi trang thai don moi la `pending`.
- Van de 2: `admin/edit_order_status.php` tiep tuc tru ton kho lan nua khi admin chuyen status sang `paid`.
- Van de 3: `user/order_items.php` cong lai ton kho khi huy don ma khong kiem tra don da tung tru kho hay da huy truoc do hay chua.
- Hau qua: ton kho co the am, sai so lieu kho, nguoi dung co the huy lap lai/doi trang thai gay sai kho.
- De xuat: chon mot thoi diem duy nhat de tru kho, tot nhat la khi tao don thanh cong trong transaction; them cot/flag `stock_deducted` hoac rang buoc trang thai de chi cong/tru mot lan.

### 4. Nguoi dung co the cap nhat/huy don khong thuoc ve minh

- File lien quan: `user/order_items.php`.
- Trang thai: da sua trong `user/order_items.php` bang POST + CSRF + `WHERE id = ? AND user_id = ?` va transaction.
- Can tiep tuc: ra soat cac file user/admin khac co thao tac tren `order_id` de dam bao cung rang buoc chu so huu hoac role admin.

### 5. Thong tin SMTP Gmail hard-code trong source

- File lien quan: `user/forgot_password.php`.
- Van de: email va app password Gmail duoc ghi truc tiep trong code.
- Hau qua: lo credential neu day len GitHub, tai khoan email co the bi chiem dung/gui spam.
- De xuat: thu hoi app password hien tai, tao app password moi, dua vao bien moi truong `.env` hoac file config khong commit.

## Loi bao mat muc cao

### 6. Nhieu cau SQL con noi chuoi truc tiep

- File lien quan: `admin/add_product.php`, `admin/edit_product.php`, `admin/add_post.php`, `admin/edit_post.php`, `admin/reviews_management.php`, `user/cart_item.php`, `user/reviews.php`, `index.php`, `user/dashboard.php`.
- Van de: nhieu query van ghep bien vao SQL bang chuoi. Mot so noi da dung `intval()`/`real_escape_string()`, nhung cach nay khong dong nhat va de sot loi.
- Hau qua: co nguy co SQL injection, kho bao tri, de loi khi du lieu co ky tu dac biet.
- De xuat: chuyen tat ca truy van co input nguoi dung sang prepared statement (`prepare`, `bind_param`).

### 7. Upload file chua kiem tra MIME, size va quyen truy cap

- File lien quan: `admin/add_product.php`, `admin/edit_product.php`, `admin/add_post.php`, `admin/edit_post.php`, `admin/upload_image.php`.
- Van de: upload chu yeu dua vao ten file/extension; mot so noi khong check extension, MIME, size, loi upload; file duoc luu vao thu muc public `images/`.
- Hau qua: co the upload file doc hai/gia mao anh, ghi file dung ten la, lam day dung luong server.
- De xuat: kiem tra `$_FILES['...']['error']`, gioi han size, dung `finfo_file()` de check MIME, doi ten file an toan, chi cho phep image hop le.

### 8. Thieu CSRF trong form admin va user

- File lien quan: hau het form POST nhu `admin/add_product.php`, `admin/edit_product.php`, `admin/edit_user.php`, `user/edit_profile.php`, `user/reviews.php`, `user/cart_item.php`.
- Van de: form POST khong co CSRF token.
- Hau qua: ke tan cong co the tao form tren website khac de nguoi dung/admin vo tinh gui request thay doi du lieu.
- De xuat: sinh token trong session, chen hidden input vao form, verify token khi POST.

### 9. Open redirect sau dang nhap

- File lien quan: `login.php`, `user/cart_add.php`.
- Van de: `$_SESSION['redirect_after_login']` co the lay tu `HTTP_REFERER`, sau do `login.php` redirect den gia tri nay.
- Hau qua: co nguy co open redirect/phishing neu referer bi dieu khien.
- De xuat: chi cho phep redirect noi bo bat dau bang `/` va khong co schema/domain; hoac dung whitelist route.

## Loi logic va bug chuc nang

### 10. Ap dung coupon tinh sai vi dung `$total` truoc khi tinh tong

- File lien quan: `user/cart_item.php`.
- Van de: khi apply coupon phan tram, code dung `$total` o dau file truoc khi `$total` duoc tinh tu danh sach gio hang.
- Hau qua: discount phan tram co the bang 0, sai tien giam gia.
- De xuat: load gio hang va tinh tong truoc, sau do moi xu ly coupon; hoac tinh coupon tai checkout dua tren tong da xac dinh.

### 11. Tong thanh toan QR khong bao gom ship/giam gia

- File lien quan: `user/order_items.php`.
- Trang thai: da sua trong `user/order_items.php`; QR hien `$grand_total` gom tong san pham, phi ship va giam gia.

### 12. `edit_order_status.php` form POST sang file khac, logic trong file bi lech

- File lien quan: `admin/edit_order_status.php`, `admin/update_order_status.php`.
- Van de: `edit_order_status.php` co logic POST tru ton kho, nhung form lai submit sang `update_order_status.php`; file update chi update status, khong xu ly logic kho.
- Hau qua: hanh vi thuc te khac voi y dinh code, kho de debug, ton kho khi doi trang thai co the luc tru luc khong.
- De xuat: gom logic cap nhat status vao mot file duy nhat, co transaction va kiem tra chuyen trang thai hop le.

### 13. Database schema thieu cot duoc code su dung

- File lien quan: `config/jdm.sql`, `user/orders.php`, `user/order_items.php`.
- Van de: code insert/select cac cot `shipping_fee`, `discount` trong bang `orders`, nhung phan `CREATE TABLE orders` trong `config/jdm.sql` chi co `id`, `user_id`, `total`, `status`, `created_at`.
- Hau qua: import database moi tu `config/jdm.sql` se lam checkout loi `Unknown column 'shipping_fee'` hoac `Unknown column 'discount'`.
- De xuat: cap nhat schema SQL them `shipping_fee DECIMAL(10,2) DEFAULT 0`, `discount DECIMAL(10,2) DEFAULT 0`.

### 14. Mot so du lieu anh trong SQL khong khop file that

- File lien quan: `config/jdm.sql`, thu muc `images/`.
- Vi du: `toyota_gt86_white` thieu duoi file o `config/jdm.sql`; nhieu san pham dau co ten anh nhu `toyota_supra_mk5.jpg`, `toyota_supra_mk4.jpeg` nhung khong thay trong danh sach `images/` hien tai.
- Hau qua: anh san pham bi loi hien thi.
- De xuat: viet script doi chieu `products.image` voi thu muc `images/`, sua ten file hoac bo sung anh thieu.

### 15. Pagination cua quan ly review khong ap dung LIMIT/OFFSET

- File lien quan: `admin/reviews_management.php`.
- Van de: co tinh `$limit`, `$offset`, `$total_pages` nhung query lay review khong co `LIMIT $limit OFFSET $offset`.
- Hau qua: trang review van load tat ca danh gia, pagination chi hien thi hinh thuc.
- De xuat: them `LIMIT ? OFFSET ?` vao query.

### 16. Reset password/OTP can cai thien

- File lien quan: `user/forgot_password.php`, `user/verify_otp.php`, `user/reset_password.php`.
- Van de: OTP luu dang plain text, khong thay co rate limit, thong bao email khong ton tai co the lam lo thong tin tai khoan.
- Hau qua: de bi brute force OTP hoac enumerate email.
- De xuat: hash OTP, gioi han so lan gui/nhap, thong bao chung chung "neu email ton tai chung toi se gui ma".

## Loi chat luong code / bao tri

### 17. Dong HTML lap lai va cau truc file chua tach ro

- File lien quan: `index.php`, `user/dashboard.php`, cac file admin/user.
- Van de: nhieu trang tron xu ly POST/SQL/HTML trong cung file, lap lai header, style inline va query.
- Hau qua: kho bao tri, sua mot logic phai sua nhieu noi, de phat sinh bug khong dong nhat.
- De xuat: tach helper database, auth, csrf, upload, order service; dung include layout thong nhat.

### 18. Khong co rang buoc khoa ngoai trong SQL

- File lien quan: `config/jdm.sql`.
- Van de: cac bang co index `user_id`, `product_id`, `order_id` nhung khong thay khai bao `FOREIGN KEY`.
- Hau qua: de ton tai du lieu mo coi, xoa user/product co the lam loi order/cart/review.
- De xuat: them foreign key va chinh sach `ON DELETE` phu hop.

### 19. Chua co validation thong nhat cho tien, so luong, trang thai

- File lien quan: `admin/add_product.php`, `admin/edit_product.php`, `admin/update_order_status.php`, `user/cart_item.php`.
- Van de: `price`, `stock`, `quantity`, `status` chua duoc validate chat; status trong admin co the nhan gia tri POST bat ky neu request tu tao.
- Hau qua: du lieu am, status sai enum, loi database hoac sai nghiep vu.
- De xuat: validate `price >= 0`, `stock >= 0`, `quantity >= 1`, status thuoc whitelist.

## Loi bo sung sau khi ra soat lan 2

### 20. `user/wishlist_add.php` bi lech phuong thuc POST/GET sau khi dang nhap

- File lien quan: `user/wishlist_add.php`, `user/dashboard.php`.
- Van de: khi chua dang nhap, file luu `wishlist_pending` roi redirect ve `/user/wishlist_add.php?product_id=...`; nhung sau do file lai chi doc `$_POST['product_id']`, khong doc GET.
- Hau qua: sau khi dang nhap, them wishlist co the bao `San pham khong hop le0` va khong them duoc san pham.
- De xuat: sau dang nhap xu ly pending bang POST noi bo hoac cho phep doc `$_GET['product_id']` co kiem tra hop le; dong thoi chuan hoa tat ca nut wishlist ve POST.

### 21. `wishlist_add.php` co open redirect qua truong `redirect`

- File lien quan: `user/wishlist_add.php`, `index.php`.
- Van de: sau khi them wishlist thanh cong, code `header("Location: " . $redirect)` voi `$redirect = $_POST['redirect'] ?? '../index.php'`.
- Hau qua: neu request bi tao gia mao, co the redirect nguoi dung sang domain doc hai.
- De xuat: chi chap nhan redirect noi bo, vi du path bat dau bang `/` va khong chua `//`, hoac luu route hop le trong whitelist.

### 22. `cart_add.php` khong kiem tra ton kho khi them vao gio

- File lien quan: `user/cart_add.php`.
- Van de: file chi kiem tra san pham ton tai, khong kiem tra `stock` va khong gioi han tong so luong trong gio <= ton kho.
- Hau qua: user co the them so luong lon hon ton kho, den checkout moi loi hoac gay trai nghiem xau.
- De xuat: truy van `stock`, tinh so luong da co trong gio, chi cho them neu `current_quantity + quantity <= stock`.

### 23. `post_detail.php` co the loi khi bai viet khong ton tai

- File lien quan: `user/post_detail.php`.
- Van de: code goi `$post = $result->fetch_assoc()` roi dung `$post['title']`, `$post['thumbnail']`, `$post['content']` ma khong kiem tra `$post` co null hay khong.
- Hau qua: truy cap `?view=post&id=ID_KHONG_TON_TAI` co the phat sinh warning/fatal tuy cau hinh PHP.
- De xuat: neu khong tim thay bai viet thi hien thong bao 404/"Bai viet khong ton tai" va dung render.

### 24. `post_detail.php` render HTML content truc tiep, co nguy co stored XSS

- File lien quan: `user/post_detail.php`, `admin/add_post.php`, `admin/edit_post.php`.
- Van de: bai viet duoc in bang `<?= $post['content'] ?>` khong sanitize. Neu admin/editor nhap script hoac iframe doc hai, script se chay voi nguoi xem.
- Hau qua: stored XSS, danh cap session/cookie neu cookie khong HttpOnly, chen noi dung lua dao.
- De xuat: neu can cho phep HTML thi dung HTML purifier/whitelist tag; neu khong can HTML thi dung `htmlspecialchars()`.

### 25. `verify_otp.php` van co SQL noi chuoi khi xoa OTP

- File lien quan: `user/verify_otp.php`.
- Van de: dong `DELETE FROM password_resets WHERE email = '$email'` noi chuoi truc tiep tu session.
- Hau qua: rui ro SQL injection neu session bi thao tung hoac du lieu email bat thuong; dong thoi khong dong nhat voi prepared statement o cac dong tren.
- De xuat: doi sang prepared statement `DELETE FROM password_resets WHERE email = ?`.

### 26. Validate mat khau reset dung sai ham validator

- File lien quan: `user/reset_password.php`.
- Van de: code dung `Validator::min($password, 6)` thay vi `Validator::minLength($password, 6)`.
- Hau qua: so sanh chuoi voi so co the cho ket qua khong dung y nghia; mat khau ngan co the qua validation hoac validation khong on dinh.
- De xuat: doi thanh `Validator::minLength($password, 6)` va them confirm password neu can.

### 27. API GHN public endpoint chua validate input va chua xu ly loi cURL/JSON

- File lien quan: `config/ghn.php`.
- Van de: endpoint doc truc tiep `action`, `province_id`, `district_id` tu GET; khong cast int ro rang, khong kiem tra curl error/http status/json decode error.
- Hau qua: khi GHN loi hoac token sai, frontend nhan response rong/khong ro loi; input bat thuong co the tao request khong hop le den API ngoai.
- De xuat: validate ID la so duong, tra HTTP status phu hop, log curl error va chuan hoa JSON response.

### 28. Co file dev/demo cua PHPMailer nam trong `vendor` co the bi public neu server expose vendor

- File lien quan: `vendor/phpmailer/phpmailer/get_oauth_token.php`.
- Van de: file demo OAuth cua PHPMailer co form nhap client secret va co flow OAuth; neu web server cho truy cap `/vendor/...`, day la endpoint khong can thiet tren production.
- Hau qua: tang be mat tan cong, co nguy co lo thong tin neu nguoi dung/admin thao tac nham.
- De xuat: chan truy cap thu muc `vendor` bang web server/.htaccess hoac khong deploy file demo/dev.

## Kiem tra da chay

Lenh da chay:

```bat
for /R %f in (*.php) do @php -l "%f"
```

Ket qua: tat ca file PHP trong du an va vendor khong co loi cu phap.

## Thu tu uu tien sua de xuat

1. Them `admin_auth_check.php` va ap dung cho toan bo thu muc `admin/`.
2. Chuyen cac thao tac xoa/huy/cap nhat sang POST + CSRF token.
3. Sua logic don hang/ton kho bang transaction va chi cong/tru kho mot lan.
4. Sua schema `orders` them `shipping_fee`, `discount` va dong bo lai `config/jdm.sql`.
5. Doi toan bo SQL co input nguoi dung sang prepared statement.
6. Dua SMTP/GHN token vao bien moi truong, thu hoi credential da lo.
7. Gia co upload file bang MIME/size/error check.
8. Sua coupon, QR amount, pagination review va doi chieu anh san pham.
9. Sua cac loi bo sung: wishlist pending, open redirect, validate ton kho khi them gio, post 404/XSS, reset password validator va API GHN.
