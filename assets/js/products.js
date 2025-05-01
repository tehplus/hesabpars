/**
 * add-product.js
 * مدیریت عملیات‌های مربوط به صفحه افزودن محصول
 * 
 * @author TehPlus
 * @version 1.0.0
 */

// استفاده از Strict Mode برای جلوگیری از خطاهای رایج
'use strict';

// تعریف ماژول اصلی با استفاده از IIFE
const ProductManager = (function() {
    // متغیرهای خصوصی
    let selectedImages = [];
    let priceHistory = [];
    let specifications = [];
    let tags = new Set();
    
    // تنظیمات پیش‌فرض
    const DEFAULT_SETTINGS = {
        maxImages: 5,
        maxImageSize: 2 * 1024 * 1024, // 2MB
        allowedImageTypes: ['image/jpeg', 'image/png', 'image/webp'],
        defaultCurrency: 'IRR',
        defaultTaxRate: 9,
        autoGenerateCode: true
    };

    /**
     * راه‌اندازی اولیه
     */
    function init() {
        initializeSelect2();
        initializeImageUpload();
        initializeFormValidation();
        initializeEventListeners();
        initializePriceCalculator();
        initializeInventoryManager();
        initializeSpecifications();
        initializeTags();
    }

    /**
     * راه‌اندازی Select2
     */
    function initializeSelect2() {
        $('.select2-basic').select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            language: 'fa',
            width: '100%'
        });

        // تنظیمات Select2 برای دسته‌بندی‌ها
        $('#category_id').select2({
            theme: 'bootstrap-5',
            dir: 'rtl',
            language: 'fa',
            width: '100%',
            placeholder: 'انتخاب دسته‌بندی',
            allowClear: true,
            ajax: {
                url: `${BASE_URL}/api/categories/search`,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    return {
                        results: data.items,
                        pagination: {
                            more: data.pagination.more
                        }
                    };
                },
                cache: true
            },
            templateResult: formatCategory,
            templateSelection: formatCategorySelection
        });
    }

    /**
     * فرمت‌بندی نمایش دسته‌بندی در Select2
     */
    function formatCategory(category) {
        if (!category.id) return category.text;
        
        return $(`<span>
            <i class="${category.icon || 'fas fa-folder'}"></i>
            ${category.text}
            ${category.parent ? `<small class="text-muted">(${category.parent})</small>` : ''}
        </span>`);
    }

    /**
     * فرمت‌بندی نمایش دسته‌بندی انتخاب شده
     */
    function formatCategorySelection(category) {
        if (!category.id) return category.text;
        return $(`<span><i class="${category.icon || 'fas fa-folder'}"></i> ${category.text}</span>`);
    }

    /**
     * راه‌اندازی آپلود تصویر
     */
    function initializeImageUpload() {
        const dropZone = document.getElementById('dropZone');
        const imageInput = document.getElementById('productImages');

        // رویدادهای Drag & Drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // افزودن کلاس مناسب هنگام drag
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        // مدیریت رها کردن فایل
        dropZone.addEventListener('drop', handleDrop, false);
        
        // مدیریت انتخاب فایل
        imageInput.addEventListener('change', function(e) {
            handleFiles(e.target.files);
        });

        // کلیک روی ناحیه آپلود
        dropZone.addEventListener('click', () => imageInput.click());
    }

    /**
     * جلوگیری از رفتار پیش‌فرض مرورگر
     */
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * نمایش حالت فعال برای drop zone
     */
    function highlight(e) {
        document.getElementById('dropZone').classList.add('dragover');
    }

    /**
     * حذف حالت فعال از drop zone
     */
    function unhighlight(e) {
        document.getElementById('dropZone').classList.remove('dragover');
    }

    /**
     * مدیریت رها کردن فایل
     */
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }

    /**
     * مدیریت فایل‌های آپلود شده
     */
    function handleFiles(files) {
        if (selectedImages.length + files.length > DEFAULT_SETTINGS.maxImages) {
            showError(`حداکثر تعداد مجاز تصاویر ${DEFAULT_SETTINGS.maxImages} عدد می‌باشد.`);
            return;
        }

        Array.from(files).forEach(file => {
            if (!DEFAULT_SETTINGS.allowedImageTypes.includes(file.type)) {
                showError(`فرمت فایل ${file.name} پشتیبانی نمی‌شود.`);
                return;
            }

            if (file.size > DEFAULT_SETTINGS.maxImageSize) {
                showError(`حجم فایل ${file.name} بیشتر از حد مجاز است.`);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                addImagePreview(e.target.result, file);
            };
            reader.readAsDataURL(file);
        });
    }

    /**
     * افزودن پیش‌نمایش تصویر
     */
    function addImagePreview(src, file) {
        const preview = document.createElement('div');
        preview.className = 'image-preview';
        preview.innerHTML = `
            <img src="${src}" alt="Preview">
            <button type="button" class="remove-image">×</button>
            <input type="hidden" name="images[]" value="${src}">
        `;

        preview.querySelector('.remove-image').addEventListener('click', function() {
            preview.remove();
            selectedImages = selectedImages.filter(img => img !== file);
        });

        document.querySelector('.image-preview-container').appendChild(preview);
        selectedImages.push(file);
    }

    /**
     * راه‌اندازی اعتبارسنجی فرم
     */
    function initializeFormValidation() {
        const form = document.getElementById('productForm');
        
        form.addEventListener('submit', function(event) {
            if (!validateForm()) {
                event.preventDefault();
                event.stopPropagation();
                showError('لطفاً موارد الزامی را تکمیل کنید.');
            }
            form.classList.add('was-validated');
        });

        // اعتبارسنجی زنده فیلدها
        form.querySelectorAll('.form-control, .form-select').forEach(input => {
            input.addEventListener('change', function() {
                validateField(this);
            });
        });
    }

    /**
     * اعتبارسنجی کل فرم
     */
    function validateForm() {
        const form = document.getElementById('productForm');
        let isValid = true;

        // بررسی فیلدهای الزامی
        form.querySelectorAll('[required]').forEach(input => {
            if (!validateField(input)) {
                isValid = false;
            }
        });

        // بررسی قیمت
        const price = parseFloat(form.querySelector('#selling_price').value);
        if (isNaN(price) || price < 0) {
            showError('لطفاً قیمت معتبر وارد کنید.');
            isValid = false;
        }

        return isValid;
    }

    /**
     * اعتبارسنجی یک فیلد
     */
    function validateField(field) {
        let isValid = true;

        // بررسی پر بودن فیلد
        if (field.hasAttribute('required') && !field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        }

        // بررسی الگوی ورودی
        if (field.pattern && !new RegExp(field.pattern).test(field.value)) {
            field.classList.add('is-invalid');
            isValid = false;
        }

        // بررسی طول مجاز
        if (field.minLength && field.value.length < field.minLength) {
            field.classList.add('is-invalid');
            isValid = false;
        }

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }

        return isValid;
    }

    /**
     * راه‌اندازی محاسبه‌گر قیمت
     */
    function initializePriceCalculator() {
        const sellingPrice = document.getElementById('selling_price');
        const purchasePrice = document.getElementById('purchase_price');
        const profitMargin = document.getElementById('profit_margin');
        const salesTaxEnabled = document.getElementById('sales_tax_enabled');
        const salesTaxRate = document.getElementById('sales_tax_rate');

        // محاسبه حاشیه سود
        function calculateProfit() {
            const selling = parseFloat(sellingPrice.value) || 0;
            const purchase = parseFloat(purchasePrice.value) || 0;
            
            if (selling && purchase) {
                const profit = ((selling - purchase) / purchase) * 100;
                profitMargin.value = profit.toFixed(2);
            }
        }

        // محاسبه قیمت با مالیات
        function calculateTaxedPrice() {
            const price = parseFloat(sellingPrice.value) || 0;
            const taxRate = salesTaxEnabled.checked ? (parseFloat(salesTaxRate.value) || 0) : 0;
            
            const taxedPrice = price * (1 + (taxRate / 100));
            document.getElementById('final_price').value = taxedPrice.toFixed(2);
        }

        // رویدادهای محاسبه قیمت
        [sellingPrice, purchasePrice].forEach(input => {
            input.addEventListener('input', calculateProfit);
        });

        [sellingPrice, salesTaxEnabled, salesTaxRate].forEach(input => {
            input.addEventListener('input', calculateTaxedPrice);
        });
    }

    /**
     * راه‌اندازی مدیریت موجودی
     */
    function initializeInventoryManager() {
        const stockControl = document.getElementById('stock_control');
        const inventorySection = document.querySelector('.inventory-section');
        
        stockControl.addEventListener('change', function() {
            inventorySection.style.display = this.checked ? 'block' : 'none';
        });

        // محاسبه هشدار موجودی
        function checkStockAlert() {
            const currentStock = parseInt(document.getElementById('current_stock').value) || 0;
            const minStock = parseInt(document.getElementById('min_stock').value) || 0;
            
            if (currentStock <= minStock) {
                showWarning('موجودی محصول کم است.');
            }
        }

        document.getElementById('current_stock').addEventListener('change', checkStockAlert);
    }

    /**
     * راه‌اندازی مدیریت مشخصات فنی
     */
    function initializeSpecifications() {
        const specTable = document.querySelector('.specifications-table tbody');
        const addSpecBtn = document.getElementById('addSpecification');

        addSpecBtn.addEventListener('click', function() {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <input type="text" class="form-control" name="spec_keys[]" required>
                </td>
                <td>
                    <input type="text" class="form-control" name="spec_values[]" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-spec">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            `;

            specTable.appendChild(row);
        });

        // حذف مشخصات
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-spec')) {
                e.target.closest('tr').remove();
            }
        });
    }

    /**
     * راه‌اندازی مدیریت تگ‌ها
     */
    function initializeTags() {
        const tagInput = document.getElementById('tagInput');
        const tagContainer = document.querySelector('.tag-container');

        tagInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const tagText = this.value.trim();
                
                if (tagText && !tags.has(tagText)) {
                    addTag(tagText);
                    this.value = '';
                }
            }
        });

        function addTag(text) {
            const tag = document.createElement('span');
            tag.className = 'tag';
            tag.innerHTML = `
                ${text}
                <i class="fas fa-times remove-tag"></i>
                <input type="hidden" name="tags[]" value="${text}">
            `;

            tag.querySelector('.remove-tag').addEventListener('click', function() {
                tags.delete(text);
                tag.remove();
            });

            tagContainer.appendChild(tag);
            tags.add(text);
        }
    }

    /**
     * راه‌اندازی گوش‌دهنده‌های رویداد
     */
    function initializeEventListeners() {
        // تولید خودکار کد حسابداری
        const autoGenerate = document.getElementById('autoAccountingCode');
        const accountingCode = document.getElementById('accounting_code');
        
        autoGenerate.addEventListener('change', function() {
            accountingCode.disabled = this.checked;
            if (this.checked) {
                accountingCode.value = generateAccountingCode();
            }
        });

        // ذخیره خودکار پیش‌نویس
        let saveTimeout;
        document.getElementById('productForm').addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveDraft, 3000);
        });

        // پیش‌نمایش توضیحات
        const description = document.getElementById('description');
        const previewBtn = document.getElementById('previewDescription');
        
        if (previewBtn) {
            previewBtn.addEventListener('click', function() {
                showPreview(description.value);
            });
        }
    }

    /**
     * تولید کد حسابداری
     */
    function generateAccountingCode() {
        const timestamp = new Date().getTime();
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return `PRD-${timestamp}-${random}`;
    }

    /**
     * ذخیره پیش‌نویس
     */
    function saveDraft() {
        const formData = new FormData(document.getElementById('productForm'));
        
        fetch(`${BASE_URL}/api/products/draft`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('پیش‌نویس ذخیره شد.');
            }
        })
        .catch(error => {
            console.error('Error saving draft:', error);
        });
    }

    /**
     * نمایش پیش‌نمایش توضیحات
     */
    function showPreview(markdown) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">پیش‌نمایش توضیحات</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        ${marked(markdown)}
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();

        modal.addEventListener('hidden.bs.modal', function() {
            modal.remove();
        });
    }

    /**
     * نمایش پیام موفقیت
     */
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'موفق',
            text: message,
            confirmButtonText: 'تایید'
        });
    }

    /**
     * نمایش پیام خطا
     */
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'خطا',
            text: message,
            confirmButtonText: 'تایید'
        });
    }

    /**
     * نمایش پیام هشدار
     */
    function showWarning(message) {
        Swal.fire({
            icon: 'warning',
            title: 'هشدار',
            text: message,
            confirmButtonText: 'تایید'
        });
    }

    // بازگرداندن متدهای عمومی
    return {
        init: init,
        validateForm: validateForm,
        showSuccess: showSuccess,
        showError: showError,
        showWarning: showWarning
    };
})();

// راه‌اندازی ماژول هنگام لود صفحه
document.addEventListener('DOMContentLoaded', function() {
    ProductManager.init();
});