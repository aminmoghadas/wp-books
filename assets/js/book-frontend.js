(function (window, document, $) {
    'use strict';

    const ajaxUrl = (window.WPBooksData && window.WPBooksData.ajax_url) ? window.WPBooksData.ajax_url : '/wp-admin/admin-ajax.php';
    const nonce  = (window.WPBooksData && window.WPBooksData.nonce) ? window.WPBooksData.nonce : '';
    const perPage = (window.WPBooksData && window.WPBooksData.per_page) ? parseInt(window.WPBooksData.per_page, 10) : 10;

    function showMessage(msg, isError) {
        const $el = $('#wp-books-message');
        $el.text(msg).css('color', isError ? 'red' : 'green');
        setTimeout(() => $el.text(''), 5000);
    }

    function renderBooks(books) {
        const $tbody = $('#wp-books-table tbody');
        $tbody.empty();
        if (!books || books.length === 0) {
            $tbody.append('<tr><td colspan="3">No books found.</td></tr>');
            return;
        }
        books.forEach(book => {
            const row = `<tr>
                <td>${escapeHtml(book.title)}</td>
                <td>${escapeHtml(book.author)}</td>
                <td>${escapeHtml(String(book.published_year))}</td>
            </tr>`;
            $tbody.append(row);
        });
    }

    function escapeHtml(unsafe) {
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function fetchBooks(page) {
        page = page || 1;
        $.ajax({
            url: ajaxUrl,
            method: 'GET',
            data: {
                action: 'wpbooks_get_books',
                page: page,
                per_page: perPage
            },
            success: function (resp) {
                if (resp && resp.success) {
                    renderBooks(resp.data.books);
                    updatePagination(resp.data.meta);
                } else {
                    renderBooks([]);
                    updatePagination({ total:0, current_page:1, total_pages:1 });
                }
            },
            error: function () {
                renderBooks([]);
                updatePagination({ total:0, current_page:1, total_pages:1 });
            }
        });
    }

    function updatePagination(meta) {
        var current = meta.current_page || 1;
        var total = meta.total_pages || 1;
        $('#wp-books-pageinfo').text('Page ' + current + ' / ' + total);
        $('#wp-books-prev').prop('disabled', current <= 1);
        $('#wp-books-next').prop('disabled', current >= total);
        $('#wp-books-prev').data('page', current - 1);
        $('#wp-books-next').data('page', current + 1);
    }

    function submitForm(e) {
        e.preventDefault();
        const $form = $('#wp-books-form');
        const title = $('#wp-books-title').val() || '';
        const author = $('#wp-books-author').val() || '';
        const year = $('#wp-books-year').val() || '';

        if (!title.trim() || !author.trim() || !year || parseInt(year, 10) <= 0) {
            showMessage('Please complete all fields correctly.', true);
            return;
        }

        $.ajax({
            url: ajaxUrl,
            method: 'POST',
            data: {
                action: 'wpbooks_add_book',
                nonce: nonce,
                title: title,
                author: author,
                published_year: year
            },
            success: function (resp) {
                if (resp && resp.success) {
                    showMessage(resp.data.message, false);
                    fetchBooks(1); // go to first page to show recently added
                    $form[0].reset();
                } else if (resp && resp.data && resp.data.message) {
                    showMessage(resp.data.message, true);
                } else {
                    showMessage('Unexpected error.', true);
                }
            },
            error: function (xhr) {
                let msg = 'AJAX error';
                try {
                    msg = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : msg;
                } catch (e) {}
                showMessage(msg, true);
            }
        });
    }

    $(document).ready(function () {
        $(document).on('submit', '#wp-books-form', submitForm);
        $(document).on('click', '#wp-books-prev, #wp-books-next', function (e) {
            e.preventDefault();
            var page = $(this).data('page') || 1;
            fetchBooks(page);
        });

        // Initial fetch
        fetchBooks(1);
    });


})(window, document, jQuery);
