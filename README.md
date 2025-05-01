# hesabpars

کد ناقص اومد. ادامشو بنویس. تا اینجا نوشتی.

    /**
     * تجدید remember token
     * 
     * @param string $token توکن قبلی
     */
    private function refreshRememberToken($token) {
        $query = "SELECT user_id FROM remember_tokens WHERE token = ? LIMIT 1";
        $result = $this->db->getRow($query, [$token]);

        if ($result) {
            $this->clearRememberToken($token);
            $this->setRememberToken($result['user_id']);
        }
    }

    /**
     *


از همینجا به بعدشو بنویس. از اول ننویسی


مسیر فایل رو درست کنیم. با توجه به ساختار منو، باید فایل رو در این مسیر قرار بدیم:
 /pages/add-product.php (به جای /pages/products/add.php)








 <script src="https://cdn.tiny.cloud/1/0emz75b66b3lrmxv8eoj3jhz6gakhqli6gxtlkqrlh93r2sn/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>


